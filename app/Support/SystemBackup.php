<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use PDO;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

class SystemBackup
{
    public function create(): string
    {
        if (! extension_loaded('zip')) {
            throw new RuntimeException('The PHP zip extension is required for backups.');
        }

        $directory = $this->backupDirectory();
        $lock = fopen($directory.DIRECTORY_SEPARATOR.'.backup.lock', 'c+');

        if (! $lock) {
            throw new RuntimeException('Cannot create the backup lock file.');
        }

        if (! flock($lock, LOCK_EX | LOCK_NB)) {
            fclose($lock);
            throw new RuntimeException('Another backup is already running.');
        }

        $base = 'holol-'.now('UTC')->format('Ymd-His').'-'.bin2hex(random_bytes(4));
        $partial = $directory.DIRECTORY_SEPARATOR.$base.'.partial';
        $final = $directory.DIRECTORY_SEPARATOR.$base.'.zip';
        $dump = $directory.DIRECTORY_SEPARATOR.$base.'.database.tmp';

        try {
            [$databaseEntry, $driver] = $this->dumpDatabase($dump);
            $zip = new ZipArchive;

            if ($zip->open($partial, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Cannot create the backup archive.');
            }

            $files = [];

            try {
                $this->addFile($zip, $dump, $databaseEntry, $files);
                $this->addStorageFiles($zip, $files);

                $manifest = json_encode([
                    'format' => 1,
                    'created_at' => now('UTC')->toIso8601String(),
                    'database_driver' => $driver,
                    'files' => $files,
                ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

                if (! $zip->addFromString('manifest.json', $manifest)) {
                    throw new RuntimeException('Cannot write the backup manifest.');
                }
            } finally {
                if (! $zip->close()) {
                    throw new RuntimeException('Cannot finish the backup archive.');
                }
            }

            $this->verify($partial);

            if (! rename($partial, $final)) {
                throw new RuntimeException('Cannot publish the verified backup.');
            }

            @chmod($final, 0600);

            return $final;
        } finally {
            if (is_file($dump)) {
                unlink($dump);
            }

            if (is_file($partial)) {
                unlink($partial);
            }

            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public function verify(string $archive): array
    {
        $zip = new ZipArchive;

        if ($zip->open($archive, ZipArchive::CHECKCONS) !== true) {
            throw new RuntimeException('The backup archive is missing or corrupt.');
        }

        try {
            $rawManifest = $zip->getFromName('manifest.json');
            $manifest = is_string($rawManifest)
                ? json_decode($rawManifest, true, 512, JSON_THROW_ON_ERROR)
                : null;

            if (! is_array($manifest) || ($manifest['format'] ?? null) !== 1
                || ! in_array($manifest['database_driver'] ?? null, ['mysql', 'mariadb', 'sqlite'], true)
                || ! is_array($manifest['files'] ?? null)) {
                throw new RuntimeException('The backup manifest is invalid.');
            }

            $databaseEntry = $manifest['database_driver'] === 'sqlite' ? 'database.sqlite' : 'database.sql';

            if (! isset($manifest['files'][$databaseEntry]) || $zip->numFiles !== count($manifest['files']) + 1) {
                throw new RuntimeException('The backup archive is incomplete.');
            }

            foreach ($manifest['files'] as $name => $expected) {
                if (! is_string($name) || ! is_array($expected)
                    || ! is_int($expected['size'] ?? null)
                    || ! is_string($expected['sha256'] ?? null)) {
                    throw new RuntimeException('The backup manifest contains an invalid file entry.');
                }

                $stream = $zip->getStream($name);

                if (! $stream) {
                    throw new RuntimeException("Missing backup entry: {$name}");
                }

                $hash = hash_init('sha256');
                $bytes = hash_update_stream($hash, $stream);
                fclose($stream);

                if ($bytes !== $expected['size'] || ! hash_equals($expected['sha256'], hash_final($hash))) {
                    throw new RuntimeException("Damaged backup entry: {$name}");
                }
            }

            return $manifest;
        } finally {
            $zip->close();
        }
    }

    public function testRestore(string $archive): array
    {
        $manifest = $this->verify($archive);
        $entry = $manifest['database_driver'] === 'sqlite' ? 'database.sqlite' : 'database.sql';
        $temporary = $this->backupDirectory().DIRECTORY_SEPARATOR.'.restore-'.bin2hex(random_bytes(8)).'.tmp';
        try {
            $zip = new ZipArchive;

            if ($zip->open($archive) !== true) {
                throw new RuntimeException('Cannot open the verified backup for restore testing.');
            }

            try {
                $source = $zip->getStream($entry);

                if (! $source) {
                    throw new RuntimeException('Cannot read the database backup for restore testing.');
                }

                try {
                    $destination = fopen($temporary, 'x+b');

                    if (! $destination) {
                        throw new RuntimeException('Cannot create the isolated restore file.');
                    }

                    try {
                        if (stream_copy_to_stream($source, $destination) !== $manifest['files'][$entry]['size']) {
                            throw new RuntimeException('Cannot extract the database backup.');
                        }
                    } finally {
                        fclose($destination);
                    }
                } finally {
                    fclose($source);
                }
            } finally {
                $zip->close();
            }

            return $manifest['database_driver'] === 'sqlite'
                ? $this->testSqliteRestore($temporary)
                : $this->testMysqlRestore($temporary);
        } finally {
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
    }

    private function backupDirectory(): string
    {
        $path = (string) config('backup.path');

        if ($path === '' || (! is_dir($path) && ! mkdir($path, 0700, true))) {
            throw new RuntimeException('Cannot create the backup directory.');
        }

        $directory = realpath($path);
        $public = realpath(public_path());
        $storageApp = realpath(storage_path('app'));

        if (! $directory || ! $public || ! $storageApp) {
            throw new RuntimeException('Cannot resolve the backup directory.');
        }

        $normalized = strtolower(str_replace('\\', '/', $directory));

        foreach ([$public, $storageApp] as $forbidden) {
            $forbidden = strtolower(str_replace('\\', '/', $forbidden));

            if ($normalized === $forbidden || str_starts_with($normalized, $forbidden.'/')) {
                throw new RuntimeException('Backups cannot be stored under public/ or storage/app/.');
            }
        }

        @chmod($directory, 0700);

        return $directory;
    }

    private function dumpDatabase(string $path): array
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $quoted = $connection->getPdo()->quote($path);
            $connection->getPdo()->exec('VACUUM INTO '.$quoted);

            return ['database.sqlite', $driver];
        }

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException("Backups do not support the {$driver} database driver.");
        }

        $config = $connection->getConfig();
        $database = $config['database'] ?? null;

        if (! is_string($database) || $database === '') {
            throw new RuntimeException('The database name is missing.');
        }

        $arguments = [
            (string) config('backup.mysqldump_binary'),
            '--single-transaction',
            '--quick',
            '--hex-blob',
            '--default-character-set=utf8mb4',
            '--result-file='.$path,
            '--user='.(string) ($config['username'] ?? ''),
        ];

        if (! empty($config['unix_socket'])) {
            $arguments[] = '--socket='.$config['unix_socket'];
        } else {
            $arguments[] = '--host='.(string) ($config['host'] ?? '127.0.0.1');
            $arguments[] = '--port='.(string) ($config['port'] ?? 3306);
        }

        $arguments[] = $database;

        $process = new Process($arguments, null, [
            'MYSQL_PWD' => (string) ($config['password'] ?? ''),
        ], null, 3600);
        $process->run();

        if (! $process->isSuccessful() || ! is_file($path) || filesize($path) === 0) {
            throw new RuntimeException('Database dump failed: '.trim($process->getErrorOutput()));
        }

        return ['database.sql', $driver];
    }

    private function testSqliteRestore(string $path): array
    {
        $database = new PDO('sqlite:'.$path);
        $integrity = $database->query('PRAGMA integrity_check')->fetchColumn();
        $tables = (int) $database->query("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table'")->fetchColumn();

        if ($integrity !== 'ok' || $tables < 1) {
            throw new RuntimeException('The isolated SQLite restore failed integrity checks.');
        }

        return ['tables' => $tables];
    }

    private function testMysqlRestore(string $path): array
    {
        $connection = DB::connection();

        if (! in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
            throw new RuntimeException('A MySQL/MariaDB connection is required for this restore test.');
        }

        $config = $connection->getConfig();
        $schema = 'holol_restore_test_'.bin2hex(random_bytes(8));
        $created = false;

        try {
            $connection->statement("CREATE DATABASE `{$schema}` CHARACTER SET utf8mb4");
            $created = true;

            $arguments = [
                (string) config('backup.mysql_binary'),
                '--binary-mode',
                '--default-character-set=utf8mb4',
                '--user='.(string) ($config['username'] ?? ''),
                '--database='.$schema,
            ];

            if (! empty($config['unix_socket'])) {
                $arguments[] = '--socket='.$config['unix_socket'];
            } else {
                $arguments[] = '--host='.(string) ($config['host'] ?? '127.0.0.1');
                $arguments[] = '--port='.(string) ($config['port'] ?? 3306);
            }

            $input = fopen($path, 'rb');

            if (! $input) {
                throw new RuntimeException('Cannot read the SQL dump for restore testing.');
            }

            try {
                $process = new Process($arguments, null, [
                    'MYSQL_PWD' => (string) ($config['password'] ?? ''),
                ], $input, 3600);
                $process->run();
            } finally {
                fclose($input);
            }

            if (! $process->isSuccessful()) {
                throw new RuntimeException('The isolated MySQL restore failed: '.trim($process->getErrorOutput()));
            }

            $tableCount = (int) $connection->selectOne(
                'SELECT COUNT(*) AS total FROM information_schema.tables WHERE table_schema = ?',
                [$schema]
            )->total;

            if ($tableCount < 1) {
                throw new RuntimeException('The isolated MySQL restore contains no tables.');
            }

            $migrationCount = (int) $connection->selectOne("SELECT COUNT(*) AS total FROM `{$schema}`.`migrations`")->total;

            if ($migrationCount < 1) {
                throw new RuntimeException('The isolated MySQL restore contains no migration records.');
            }

            return ['tables' => $tableCount, 'migration_rows' => $migrationCount];
        } finally {
            if ($created) {
                $connection->statement("DROP DATABASE `{$schema}`");
            }
        }
    }

    private function addStorageFiles(ZipArchive $zip, array &$files): void
    {
        $root = realpath((string) config('backup.source_path'));

        if (! $root || ! is_dir($root)) {
            throw new RuntimeException('The storage/app source directory is missing.');
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isLink()) {
                throw new RuntimeException('A symlink inside storage/app cannot be backed up safely.');
            }

            if (! $file->isFile()) {
                continue;
            }

            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            $this->addFile($zip, $file->getPathname(), 'storage/app/'.$relative, $files);
        }
    }

    private function addFile(ZipArchive $zip, string $path, string $entry, array &$files): void
    {
        if (! is_readable($path) || ! $zip->addFile($path, $entry)) {
            throw new RuntimeException("Cannot add {$entry} to the backup.");
        }

        $hash = hash_file('sha256', $path);
        $size = filesize($path);

        if ($hash === false || $size === false) {
            throw new RuntimeException("Cannot inspect {$entry} for the backup.");
        }

        $files[$entry] = ['size' => $size, 'sha256' => $hash];
    }
}
