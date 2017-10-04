<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class CannotMakeIncrementalFilesBackup extends Exception
{
    public static function noLastBackupInfoPresented(): CannotMakeIncrementalFilesBackup
    {
        return new static("Cannot make incremental backup, no last backup info found");
    }
}