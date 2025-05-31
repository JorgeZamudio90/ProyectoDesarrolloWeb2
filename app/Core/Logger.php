<?php
namespace Alexc\ProyectoAgustin\Core;

class Logger
{
    public static function info($message) {
        self::writeLog("INFO", $message);
    }

    public static function error($message) {
        self::writeLog("ERROR", $message);
    }

    private static function writeLog($level, $message) {
        $line = "[" . date('Y-m-d H:i:s') . "] [$level] $message\n";
        file_put_contents(__DIR__ . '/../../logs/app.log', $line, FILE_APPEND);
    }
}