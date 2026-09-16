<?php

namespace App\Console\Commands;

use App\Support\SystemBackup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateSystemBackup extends Command
{
    protected $signature = 'backup:create';

    protected $description = 'Back up the database and storage/app files to a verified private archive';

    public function handle(SystemBackup $backup): int
    {
        try {
            $archive = $backup->create();
            $this->info("Verified backup created: {$archive}");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::error('System backup failed', ['exception' => $exception]);
            $this->error('System backup failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
