<?php

return [
    // Keep backups outside public/ and storage/app/ (which is itself backed up).
    'path' => env('BACKUP_PATH', storage_path('backups')),
    'mysqldump_binary' => env('BACKUP_MYSQLDUMP_BINARY', 'mysqldump'),
    'mysql_binary' => env('BACKUP_MYSQL_BINARY', 'mysql'),
    'source_path' => storage_path('app'),
];
