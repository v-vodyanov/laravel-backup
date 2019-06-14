<?php

namespace Spatie\Backup\Tasks\Backup;


use DateTime;
use Illuminate\Filesystem\Filesystem;
use SplFileObject;

class LastBackupInfo
{
    private $path;

    private $fs;

    public function __construct($path)
    {
        $this->path = $path;
        $this->fs = app(Filesystem::class);
    }

    public static function load() {
        $path = static::getFilePath();
        if (app(Filesystem::class)->exists($path)) {
            return new static($path);
        } else {
            return null;
        }
    }

    public static function createForManifest(Manifest $manifest) {
        $info = new static(static::getFilePath());

        $info->clear();

        $info->addFiles($manifest->files());
    }

    public static function getFilePath() {
        return config('backup.backup.incremental.last_backup_info_file_path', storage_path('app/last_backup_info.txt'));
    }

    public function getUpdateTime(): DateTime {
        return new DateTime('@' . $this->fs->lastModified($this->path));
    }

    public function updateDeletedFilesList($deletedFilesListPath) {
        fclose(fopen($deletedFilesListPath,'w'));

        foreach ($this->files() as $file) {
            if (!$this->fs->exists($file)) {
                file_put_contents($deletedFilesListPath, $file.PHP_EOL, FILE_APPEND);
            }
        }
    }

    public function files() {
        $file = new SplFileObject($this->path);

        while (! $file->eof()) {
            $filePath = $file->fgets();

            if (! empty($filePath)) {
                yield trim($filePath);
            }
        }
    }

    public function clear() {
        fclose(fopen($this->path,'w'));
    }

    public function addFiles($filePaths): LastBackupInfo
    {
        if (is_string($filePaths)) {
            $filePaths = [$filePaths];
        }

        foreach ($filePaths as $filePath) {
            if (! empty($filePath)) {
                file_put_contents($this->path, $filePath.PHP_EOL, FILE_APPEND);
            }
        }

        return $this;
    }
}
