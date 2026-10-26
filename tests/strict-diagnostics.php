<?php

if (\class_exists(LowRandomDiagnostics::class, false)
    && (new \ReflectionClass(LowRandomDiagnostics::class))->getFileName() !== __FILE__) {
    exit(70);
}

if (!\class_exists(LowRandomDiagnostics::class, false)) {
    /**
     * Retain unexpected diagnostics across catches and native process boundaries.
     */
    final class LowRandomDiagnostics
    {
        private static $events = array();

        /**
         * Journal a diagnostic before it can be caught by source or PHPUnit.
         *
         * @param int $severity PHP diagnostic level.
         * @param string $message Diagnostic text.
         * @param string $file Source filename.
         * @param int $line Source line.
         * @return void
         * @throws ErrorException
         * @throws RuntimeException
         */
        public static function handle($severity, $message, $file, $line): void
        {
            $event = array('pid' => getmypid(), 'severity' => $severity, 'message' => $message, 'file' => $file, 'line' => $line);
            self::$events[] = $event;
            $journal = getenv('LOW_RANDOM_STRICT_JOURNAL');

            if ($journal && file_put_contents($journal, json_encode($event) . "\n", FILE_APPEND | LOCK_EX) === false) {
                throw new RuntimeException('Cannot retain the strict diagnostic journal.');
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        }

        /**
         * Reject even a green or explicitly successful process with caught errors.
         *
         * @return void
         */
        public static function finish(): void
        {
            if (!self::$events) {
                return;
            }

            foreach (self::$events as $event) {
                file_put_contents('php://stderr', 'LOW_RANDOM_DIAGNOSTIC ' . json_encode($event) . "\n");
            }

            exit(70);
        }
    }

    error_reporting(-1);
    set_error_handler(array(LowRandomDiagnostics::class, 'handle'), E_ALL);
    register_shutdown_function(array(LowRandomDiagnostics::class, 'finish'));

    if (\defined('LOW_RANDOM_NATIVE_CAPTURE_AUTHORITY')) {
        throw new \RuntimeException('Native capture authority was defined before strict startup.');
    }

    // Bind before discovery/providers can alter the environment selection.
    \define('LOW_RANDOM_NATIVE_CAPTURE_AUTHORITY', (static function (): ?array {
        $directory = \getenv('LOW_RANDOM_STRICT_CAPTURES');
        if ($directory === false || $directory === '') {
            return null;
        }

        $identity = \lstat($directory);
        if ($identity === false || ($identity['mode'] & 0170000) !== 0040000) {
            throw new \RuntimeException('The checked native capture directory is not an existing directory.');
        }

        return array('path' => $directory, 'dev' => $identity['dev'], 'ino' => $identity['ino']);
    })());

    $lowRandomJournal = getenv('LOW_RANDOM_STRICT_JOURNAL');

    if ($lowRandomJournal) {
        file_put_contents($lowRandomJournal . '.runtime', json_encode(array(
            'pid' => getmypid(),
            'version' => PHP_VERSION,
            'binary' => PHP_BINARY,
            'reporting' => error_reporting(),
            'display_errors' => ini_get('display_errors'),
            'display_startup_errors' => ini_get('display_startup_errors'),
            'entry' => $_SERVER['SCRIPT_FILENAME'] ?? null,
            'loaded_ini' => php_ini_loaded_file(),
            'scanned_ini' => php_ini_scanned_files(),
        )) . "\n", FILE_APPEND | LOCK_EX);
    }
}
