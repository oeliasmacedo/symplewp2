<?php

namespace WPManager\Services;

use DateTime;

class BackupService
{
    private $backupDir;

    public function __construct()
    {
        $this->backupDir = getcwd() . '/backups';
        if (!file_exists($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    public function backupFiles()
    {
        $date = new DateTime();
        $backupName = 'wp-files-' . $date->format('Y-m-d-H-i-s') . '.zip';
        $backupPath = $this->backupDir . '/' . $backupName;

        // Criar arquivo ZIP com os arquivos do WordPress
        $zip = new \ZipArchive();
        if ($zip->open($backupPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            $this->addFilesToZip($zip, ABSPATH);
            $zip->close();
            return true;
        }
        return false;
    }

    public function backupDatabase()
    {
        $date = new DateTime();
        $backupName = 'wp-db-' . $date->format('Y-m-d-H-i-s') . '.sql';
        $backupPath = $this->backupDir . '/' . $backupName;

        // Obter configurações do banco de dados do WordPress
        $dbHost = DB_HOST;
        $dbName = DB_NAME;
        $dbUser = DB_USER;
        $dbPassword = DB_PASSWORD;

        // Criar comando mysqldump
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s %s > %s',
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            escapeshellarg($dbPassword),
            escapeshellarg($dbName),
            escapeshellarg($backupPath)
        );

        exec($command, $output, $returnVar);
        return $returnVar === 0;
    }

    private function addFilesToZip($zip, $path)
    {
        $excludeFiles = ['.', '..', 'backups'];
        $files = scandir($path);

        foreach ($files as $file) {
            if (in_array($file, $excludeFiles)) continue;

            $filePath = $path . '/' . $file;
            if (is_dir($filePath)) {
                $zip->addEmptyDir($file);
                $this->addFilesToZip($zip, $filePath);
            } else {
                $zip->addFile($filePath);
            }
        }
    }
} 