<?php

/**
 * Create a stateless byte normalizer while leaving hydration to the caller.
 *
 * @return object Native-wire normalizer with no application state.
 */
return new class {
    /**
     * Rewrite recognized legacy strings and retain only a complete first value.
     *
     * @param mixed $wire Original native unserialize argument.
     * @param bool $settings Whether the pinned settings-only invalid-prefix fallback applies.
     * @return mixed Equivalent native wire or the untouched unsupported argument.
     */
    public function normalize($wire, $settings = false)
    {
        if (!\is_string($wire)) {
            return $wire;
        }
        // An unknown first opcode cannot construct an object or invoke native hydration.
        if ($settings && $wire !== '' && \strpos('NbidrRsSaOCE', $wire[0]) === false) {
            return 'b:0;';
        }
        $length = \strlen($wire);
        $offset = 0;
        $output = '';
        $frames = array(array(1, false));
        while ($frames) {
            $frame = \count($frames) - 1;
            if ($frames[$frame][0] === 0) {
                if ($frames[$frame][1]) {
                    if ($offset >= $length || $wire[$offset] !== '}') {
                        return $wire;
                    }
                    $output .= '}';
                    $offset++;
                }
                \array_pop($frames);
                continue;
            }
            $frames[$frame][0]--;
            if ($offset >= $length) {
                return $wire;
            }
            if (\preg_match('/\G(?:N;|b:[01];|i:[+-]?[0-9]+;|[rR]:[0-9]+;|d:(?:NAN|-?INF|[+-]?(?:[0-9]+(?:\.[0-9]*)?|\.[0-9]+)(?:[eE][+-]?[0-9]+)?);)/', $wire, $match, 0, $offset)) {
                $output .= $match[0];
                $offset += \strlen($match[0]);
                continue;
            }
            $start = $offset;
            $tag = $wire[$offset];
            if (($tag === 's' || $tag === 'S' || $tag === 'E') && \preg_match('/\G[sSE]:([0-9]+):"/', $wire, $match, 0, $offset)) {
                $offset += \strlen($match[0]);
                $bytes = self::bounded($match[1], $length - $offset);
                if ($bytes === null) {
                    return $wire;
                }
                if ($tag !== 'S') {
                    $offset += $bytes;
                    if (\substr($wire, $offset, 2) !== '";') {
                        return $wire;
                    }
                    $offset += 2;
                    $output .= \substr($wire, $start, $offset - $start);
                    continue;
                }
                $body = '';
                for ($byte = 0; $byte < $bytes; $byte++) {
                    if ($offset >= $length) {
                        return $wire;
                    }
                    $character = $wire[$offset++];
                    if ($character === '\\') {
                        $hex = \substr($wire, $offset, 2);
                        if (\strlen($hex) !== 2 || \strspn($hex, '0123456789abcdefABCDEF') !== 2) {
                            return $wire;
                        }
                        $character = \chr(\hexdec($hex));
                        $offset += 2;
                    }
                    $body .= $character;
                }
                if (\substr($wire, $offset, 2) !== '";') {
                    return $wire;
                }
                $offset += 2;
                $output .= 's:' . $bytes . ':"' . $body . '";';
                continue;
            }
            if ($tag === 'a' && \preg_match('/\Ga:([0-9]+):\{/', $wire, $match, 0, $offset)) {
                $offset += \strlen($match[0]);
                $count = self::bounded($match[1], \intdiv($length - $offset, 2));
                if ($count === null) {
                    return $wire;
                }
                $output .= $match[0];
                $frames[] = array($count * 2, true);
                continue;
            }
            if (($tag === 'O' || $tag === 'C') && \preg_match('/\G[OC]:([0-9]+):"/', $wire, $match, 0, $offset)) {
                $offset += \strlen($match[0]);
                $classBytes = self::bounded($match[1], $length - $offset);
                if ($classBytes === null) {
                    return $wire;
                }
                $offset += $classBytes;
                if (!\preg_match('/\G":([+-]?[0-9]+):\{/', $wire, $match, 0, $offset)) {
                    return $wire;
                }
                $offset += \strlen($match[0]);
                $count = self::bounded($match[1], $tag === 'C' ? $length - $offset : \intdiv($length - $offset, 2));
                if ($count === null) {
                    return $wire;
                }
                if ($tag === 'C') {
                    $offset += $count;
                    if ($offset >= $length || $wire[$offset] !== '}') {
                        return $wire;
                    }
                    $offset++;
                    $output .= \substr($wire, $start, $offset - $start);
                    continue;
                }
                $output .= \substr($wire, $start, $offset - $start);
                $frames[] = array($count * 2, true);
                continue;
            }
            return $wire;
        }
        return $output;
    }

    /**
     * Bound a header against available bytes before any integer conversion.
     *
     * @param string $number Decimal header text, with signed zero allowed.
     * @param int $limit Available bytes or tokens.
     * @return int|null Safe nonnegative header value or unsupported input.
     */
    private static function bounded($number, $limit)
    {
        $negative = $number[0] === '-';
        $digits = \ltrim($number, '+-0');
        if ($negative && $digits !== '') {
            return null;
        }
        $maximum = (string) $limit;
        if (\strlen($digits) > \strlen($maximum) || (\strlen($digits) === \strlen($maximum) && \strcmp($digits, $maximum) > 0)) {
            return null;
        }
        return $digits === '' ? 0 : (int) $digits;
    }
};
