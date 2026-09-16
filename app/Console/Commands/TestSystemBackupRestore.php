<?php

namespace App\Console\Commands;

use App\Support\SystemBackup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class TestSystemBackupRestore extends Command
{
    protected $signature = 'backup:restore-test {archive : Absolute path of the backup ZIP}';

    protected $description = 'Restore a backup into an isolated temporary database and check its tables';

    public function handle(SystemBackup $backup): int
    {
        try {
            $result = $backup->testRestore((string) $this->argument('archive'));
            $this->info('Isolated database restore succeeded: '.$result['tables'].' tables.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::error('Backup restore test failed', ['exception' => $exception]);
            $this->error('Backup restore test failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
