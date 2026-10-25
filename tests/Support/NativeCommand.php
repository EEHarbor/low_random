<?php

declare(strict_types=1);

namespace Tests\Support;

final class NativeCommand
{
    /**
     * Guard inline CLI modes while leaving file arguments and option values intact.
     *
     * @param array $arguments Native PHP options, source and script arguments.
     * @param string $strict Process-local strict diagnostic bootstrap.
     * @return array Native CLI plan, original configuration selectors and owned option indices.
     */
    public static function guardArguments(array $arguments, string $strict): array
    {
        $inline = ['--run', '--process-begin', '--process-code', '--process-end'];
        $values = ['--php-ini', '--define', '--zend-extension', '--docroot', '--server',
            '--repeat', '--rf', '--rc', '--re', '--rz', '--ri'];
        $needsBegin = false;
        $hasBegin = false;
        $inlineProgram = false;
        $configuration = [];
        $boundary = count($arguments);
        for ($index = 0; isset($arguments[$index]); $index++) {
            $argument = $arguments[$index];
            if ($argument === '--' || substr($argument, 0, 1) !== '-') {
                $boundary = $index;
                break;
            }
            if (substr($argument, 0, 2) === '--') {
                $parts = explode('=', $argument, 2);
                if (in_array($parts[0], ['--php-ini', '--define', '--zend-extension'], true)) {
                    $configuration[] = $argument;
                    if (!isset($parts[1]) && isset($arguments[$index + 1])) {
                        $configuration[] = $arguments[$index + 1];
                    }
                }
                if ($parts[0] === '--no-php-ini') {
                    $configuration[] = $argument;
                }
                if (in_array($parts[0], $inline, true)) {
                    $inlineProgram = true;
                    $isBody = in_array($parts[0], ['--process-code', '--process-end'], true);
                    $needsBegin = $needsBegin || $isBody;
                    $hasBegin = $hasBegin || $parts[0] === '--process-begin';
                    if (isset($parts[1])) {
                        if (!$isBody) {
                            $arguments[$index] = $parts[0] . '=' . self::guardInline($parts[1], $strict);
                        }
                        continue;
                    }
                    if (isset($arguments[$index + 1])) {
                        $index++;
                        if (!$isBody) {
                            $arguments[$index] = self::guardInline($arguments[$index], $strict);
                        }
                    }
                    continue;
                }
                if (in_array($parts[0], $values, true) && !isset($parts[1])) {
                    $index++;
                    continue;
                }
                if ($parts[0] === '--file' || $parts[0] === '--process-file') {
                    $needsBegin = $needsBegin || $parts[0] === '--process-file';
                    $inlineProgram = $inlineProgram || $parts[0] === '--process-file';
                    if (!isset($parts[1])) {
                        $index++;
                    }
                    continue;
                }
                continue;
            }
            for ($position = 1; $position < strlen($argument); $position++) {
                $option = $argument[$position];
                if ($option === 'n') {
                    $configuration[] = '-n';
                }
                if (strpos('fF', $option) !== false) {
                    $needsBegin = $needsBegin || $option === 'F';
                    $inlineProgram = $inlineProgram || $option === 'F';
                    if ($position + 1 === strlen($argument)) {
                        $index++;
                    }
                    break;
                }
                if (strpos('rBER', $option) !== false) {
                    $inlineProgram = true;
                    $isBody = $option === 'R' || $option === 'E';
                    $needsBegin = $needsBegin || $isBody;
                    $hasBegin = $hasBegin || $option === 'B';
                    $code = substr($argument, $position + 1);
                    if ($code !== '' && !$isBody) {
                        $arguments[$index] = substr($argument, 0, $position + 1) . self::guardInline($code, $strict);
                    }
                    if ($code === '' && isset($arguments[$index + 1])) {
                        $index++;
                        if (!$isBody) {
                            $arguments[$index] = self::guardInline($arguments[$index], $strict);
                        }
                    }
                    break;
                }
                if (strpos('dcztS', $option) !== false) {
                    if (strpos('dcz', $option) !== false) {
                        $configuration[] = '-' . $option;
                        $value = substr($argument, $position + 1);
                        if ($value !== '' || isset($arguments[$index + 1])) {
                            $configuration[] = $value !== '' ? $value : $arguments[$index + 1];
                        }
                    }
                    if ($position + 1 === strlen($argument)) {
                        $index++;
                    }
                    break;
                }
            }
        }

        // Per-line and end programs share the begin bootstrap without extra tick events.
        if ($needsBegin && !$hasBegin) {
            array_splice($arguments, $boundary, 0, ['-B', self::guardInline('', $strict)]);
        }

        // PHP compiles inline programs before their bootstrap can restore diagnostics.
        $required = [
            '-d', 'error_reporting=-1', '-d', 'display_errors=stderr',
            '-d', 'display_startup_errors=1', '-d', 'log_errors=1',
            '-d', 'error_log=__LOW_RANDOM_OWNED_NATIVE_ERROR_LOG__',
        ];
        $nativeLogIndex = $boundary + count($required) - 1;
        $prependIndex = null;
        if (!$inlineProgram) {
            $required[] = '-d';
            $required[] = 'auto_prepend_file=__LOW_RANDOM_OWNED_NATIVE_PREPEND__';
            $prependIndex = $boundary + count($required) - 1;
        }
        array_splice($arguments, $boundary, 0, $required);

        return ['arguments' => $arguments, 'prepend_index' => $prependIndex, 'native_log_index' => $nativeLogIndex,
            'configuration_arguments' => $inlineProgram ? null : $configuration];
    }

    /**
     * Install the inline guard before live code and after required declarations.
     *
     * @param string $code Original command-line PHP code.
     * @param string $strict Process-local diagnostic bootstrap path.
     * @return string Inline code with original bytes and line numbers preserved.
     */
    private static function guardInline(string $code, string $strict): string
    {
        $opening = '<?php ';
        $tokens = token_get_all($opening . $code);
        $position = -strlen($opening);
        $ends = [];
        foreach ($tokens as $index => $token) {
            $position += strlen(is_array($token) ? $token[1] : $token);
            $ends[$index] = $position;
        }

        $prefix = 0;
        $directiveSeen = false;
        for ($index = 0; isset($tokens[$index]); $index++) {
            $token = $tokens[$index];
            if (self::isTrivia($token)) {
                continue;
            }
            if ($token === ';') {
                if (!$directiveSeen) {
                    $prefix = $ends[$index];
                }
                continue;
            }
            if (is_array($token) && $token[0] === T_DECLARE) {
                $end = self::emptyStatementEnd($tokens, $index);
                if ($end === null) {
                    break;
                }
                $header = $index + 1;
                while (isset($tokens[$header]) && (self::isTrivia($tokens[$header]) || $tokens[$header] === '(')) {
                    $header++;
                }
                if (!$directiveSeen && isset($tokens[$header]) && is_array($tokens[$header]) && $tokens[$header][1] === 'strict_types') {
                    $prefix = $ends[$end];
                }
                if (!isset($tokens[$header]) || !is_array($tokens[$header]) || $tokens[$header][1] !== 'strict_types') {
                    $directiveSeen = true;
                }
                $index = $end;
                continue;
            }
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                $next = $index + 1;
                while (isset($tokens[$next]) && self::isTrivia($tokens[$next])) {
                    $next++;
                }
                // PHP 7 represents namespace-relative calls with a separate slash.
                if (isset($tokens[$next]) && (is_array($tokens[$next]) ? $tokens[$next][1] : $tokens[$next]) === '\\') {
                    break;
                }
                for (; isset($tokens[$next]); $next++) {
                    if ($tokens[$next] === ';' || $tokens[$next] === '{') {
                        $prefix = $ends[$next];
                        break;
                    }
                }
            }
            break;
        }

        $guard = 'declare(ticks=0){require_once ' . var_export($strict, true) . ';}';

        return substr_replace($code, $guard, $prefix, 0);
    }

    /**
     * Recognize only trivia that cannot execute before the diagnostic bootstrap.
     *
     * @param array|string $token Native PHP token.
     * @return bool Whether the token has no runtime effect.
     */
    private static function isTrivia($token): bool
    {
        return is_array($token) && in_array($token[0], [T_OPEN_TAG, T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true);
    }

    /**
     * Find an empty declaration without stepping past its first live statement.
     *
     * @param array $tokens Native PHP tokens for the original inline source.
     * @param int $index First token of a statement or declaration body.
     * @return int|null Last token of an empty statement, or null for live code.
     */
    private static function emptyStatementEnd(array $tokens, int $index): ?int
    {
        while (isset($tokens[$index]) && self::isTrivia($tokens[$index])) {
            $index++;
        }
        if (!isset($tokens[$index])) {
            return null;
        }
        if (is_array($tokens[$index]) && $tokens[$index][0] === T_DECLARE) {
            $index++;
            while (isset($tokens[$index]) && self::isTrivia($tokens[$index])) {
                $index++;
            }
            if (!isset($tokens[$index]) || $tokens[$index] !== '(') {
                return null;
            }
            $depth = 0;
            for (; isset($tokens[$index]); $index++) {
                if ($tokens[$index] === '(') {
                    $depth++;
                }
                if ($tokens[$index] === ')' && --$depth === 0) {
                    return self::emptyStatementEnd($tokens, $index + 1);
                }
            }
            return null;
        }
        if ($tokens[$index] === ';') {
            return $index;
        }
        $delimiter = $tokens[$index];
        if ($delimiter !== '{' && $delimiter !== ':') {
            return null;
        }
        for ($index++; isset($tokens[$index]); $index++) {
            if (self::isTrivia($tokens[$index])) {
                continue;
            }
            if ($delimiter === '{' && $tokens[$index] === '}') {
                return $index;
            }
            if ($delimiter === ':' && is_array($tokens[$index]) && $tokens[$index][0] === T_ENDDECLARE) {
                $index++;
                while (isset($tokens[$index]) && self::isTrivia($tokens[$index])) {
                    $index++;
                }
                return isset($tokens[$index]) && $tokens[$index] === ';' ? $index : null;
            }
            $end = self::emptyStatementEnd($tokens, $index);
            if ($end === null) {
                return null;
            }
            $index = $end;
        }

        return null;
    }

}
