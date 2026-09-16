<?php

namespace Tests\Feature;

use App\Support\SystemBackup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

class SystemBackupTest extends TestCase
{
    public function test_backup_can_be_verified_and_restored_to_an_isolated_directory(): void
    {
        $root = storage_path('framework/testing/backup-test-'.bin2hex(random_bytes(6)));
        $source = $root.'/source';
        $destination = $root.'/backups';
        $restore = $root.'/restored';

        File::ensureDirectoryExists($source.'/private');
        File::put($source.'/private/example.txt', 'restored attachment');
        config()->set('backup.path', $destination);
        config()->set('backup.source_path', $source);
        $this->configureIsolatedDatabase($root);
        DB::statement('CREATE TABLE backup_example (id INTEGER PRIMARY KEY, content TEXT NOT NULL)');
        DB::table('backup_example')->insert(['id' => 1, 'content' => 'restored row']);

        try {
            $backup = app(SystemBackup::class);
            $archive = $backup->create();
            $manifest = $backup->verify($archive);

            $this->assertSame('sqlite', $manifest['database_driver']);
            $this->assertArrayHasKey('database.sqlite', $manifest['files']);
            $this->assertArrayHasKey('storage/app/private/example.txt', $manifest['files']);
            $this->assertGreaterThanOrEqual(1, $backup->testRestore($archive)['tables']);

            File::ensureDirectoryExists($restore);
            $zip = new ZipArchive;
            $this->assertTrue($zip->open($archive) === true);
            $this->assertTrue($zip->extractTo($restore));
            $zip->close();

            $restoredDatabase = new \PDO('sqlite:'.$restore.'/database.sqlite');
            $this->assertSame('restored row', $restoredDatabase->query('SELECT content FROM backup_example WHERE id = 1')->fetchColumn());
            $this->assertSame('restored attachment', File::get($restore.'/storage/app/private/example.txt'));
        } finally {
            $this->removeIsolatedTestDirectory($root);
        }
    }

    public function test_verification_detects_changed_file_contents(): void
    {
        $root = storage_path('framework/testing/backup-test-'.bin2hex(random_bytes(6)));
        $source = $root.'/source';

        File::ensureDirectoryExists($source);
        File::put($source.'/example.txt', 'original');
        config()->set('backup.path', $root.'/backups');
        config()->set('backup.source_path', $source);
        $this->configureIsolatedDatabase($root);

        try {
            $backup = app(SystemBackup::class);
            $archive = $backup->create();
            $zip = new ZipArchive;
            $this->assertTrue($zip->open($archive) === true);
            $this->assertTrue($zip->addFromString('storage/app/example.txt', 'changed'));
            $zip->close();

            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Damaged backup entry');
            $backup->verify($archive);
        } finally {
            $this->removeIsolatedTestDirectory($root);
        }
    }

    private function removeIsolatedTestDirectory(string $path): void
    {
        DB::disconnect('backup_test');

        $resolved = realpath($path);
        $testingRoot = realpath(storage_path('framework/testing'));

        if ($resolved && $testingRoot && str_starts_with($resolved, $testingRoot.DIRECTORY_SEPARATOR.'backup-test-')) {
            File::deleteDirectory($resolved);
        }
    }

    private function configureIsolatedDatabase(string $root): void
    {
        File::ensureDirectoryExists($root);
        File::put($root.'/source.sqlite', '');
        config()->set('database.connections.backup_test', [
            'driver' => 'sqlite',
            'database' => $root.'/source.sqlite',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        config()->set('database.default', 'backup_test');
        DB::purge('backup_test');
    }
}
