<?php
namespace metapilot\traits;

use Craft;

trait LoggingTrait
{
    private static ?string $logFile = null;

    private static function writeLog(string $level, string $message, ?string $category = null): void
    {
        try {
            if (!self::$logFile) {
                $date = date('Y-m-d');
                self::$logFile = Craft::$app->path->getStoragePath() . "/logs/metapilot-{$date}.log";
            }

            $timestamp = date('Y-m-d H:i:s');
            $cat = $category ? "[{$category}] " : '';
            $logLine = "[{$timestamp}] [metapilot.{$level}] {$cat}{$message}" . PHP_EOL;
            
            file_put_contents(self::$logFile, $logLine, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            Craft::error("Metapilot logging failed: {$e->getMessage()}", __METHOD__);
        }
    }

    protected function logError(string $message, ?string $category = null): void
    {
        self::writeLog('ERROR', $message, $category);
    }

    protected function logInfo(string $message, ?string $category = null): void
    {
        self::writeLog('INFO', $message, $category);
    }
}