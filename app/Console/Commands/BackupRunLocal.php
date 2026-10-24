<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupRunLocal extends Command
{
    protected $signature = 'backup:run-local';
    protected $description = 'Run local backup (Windows)';

    public function handle()
    {
        $date = now()->format('Y-m-d');
        //$backupPath = storage_path('app/backup');
        $backupPath = base_path('backup');

        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0777, true);
        }


        // =========================
        // DATABASE BACKUP
        // =========================
        $dbFile = $backupPath . "/db_$date.sql";

        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbName = config('database.connections.mysql.database');

        $mysqldump = '"C:\\xampp\\mysql\\bin\\mysqldump.exe"';

        if (empty($dbPass)) {
            $command = "$mysqldump --user=$dbUser $dbName > \"$dbFile\"";
        } else {
            $command = "$mysqldump --user=$dbUser --password=$dbPass $dbName > \"$dbFile\"";
        }

        exec($command, $output, $result);

        if ($result !== 0) {
            $this->error('Database backup failed');
            dd($command, $output); // TEMP debug
        }

        exec($command, $output, $result);

        if ($result !== 0) {
            $this->error('Database backup failed');
            return 1;
        }

        // =========================
        // FILE BACKUP (ZIP)
        // =========================

        $zipFile = $backupPath . DIRECTORY_SEPARATOR . "files_$date.zip";

        if (file_exists($zipFile)) {
            unlink($zipFile);
        }

        $source = base_path();

        $zip = new \ZipArchive();

        $result = $zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        if ($result !== TRUE) {
            dd("ZIP OPEN FAILED", $zipFile, $result);
        }

        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {

                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($source) + 1);

                    if (
                        str_contains($relativePath, 'node_modules') ||
                        str_contains($relativePath, 'vendor') ||
                        str_contains($relativePath, 'storage\\logs') ||
                        str_contains($relativePath, 'storage\\tempdir') ||
                        str_contains($relativePath, '.git')
                    ) {
                        continue;
                    }

                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();
        }

        $this->info('Backup completed successfully');

        return 0;
    }
}
