<?php

namespace App\Console\Commands;

use App\Support\SystemBackup;
use Illuminate\Console\Command;
use Throwable;

class VerifySystemBackup extends Command
{
    protected $signature = 'backup:verify {archive : Absolute path of the backup ZIP}';

    protected $description = 'Check every database and file checksum in a system backup';

    public function handle(SystemBackup $backup): int
    {
        try {
            $manifest = $backup->verify((string) $this->argument('archive'));
            $this->info('Backup verified: '.count($manifest['files']).' files, '.$manifest['database_driver'].' database.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Backup verification failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
