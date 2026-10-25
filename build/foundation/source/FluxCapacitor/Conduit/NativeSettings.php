<?php

namespace Low\Random\FluxCapacitor\Conduit;

/**
 * Return local callbacks without adding methods to extensible legacy classes.
 *
 * @return array<string, \Closure>
 */
return (static function () {
    /**
     * Rewrite escaped tokens and leave unrecognized remainders for native callback cleanup.
     *
     * @param string $value Serialized native settings.
     * @return string Normalized bytes with any unrecognized remainder unchanged.
     */
    $normalizeEscapedStrings = static function ($value)
    {
        $length = \strlen($value);
        $position = 0;
        $result = '';
        $depth = 0;

        while ($position < $length) {
            $start = $position;
            $type = $value[$position];
            if ($type === '}') {
                $result .= '}';
                $position++;
                $depth--;
                if ($depth <= 0) {
                    return $result . \substr($value, $position);
                }
                continue;
            }
            if (\preg_match('/\G(?:N;|[bidrR]:[^;]*;|a:[0-9]+:\{)/', $value, $match, 0, $position)) {
                $result .= $match[0];
                $position += \strlen($match[0]);
                if (\substr($match[0], -1) === '{') {
                    $depth++;
                }
                if ($depth === 0) {
                    return $result . \substr($value, $position);
                }
                continue;
            }
            if (!\preg_match('/\G([sSEOC]):([0-9]+):"/', $value, $match, 0, $position)) {
                return $result . \substr($value, $start);
            }

            $position += \strlen($match[0]);
            if ($match[2] > $length - $position) {
                return $result . \substr($value, $start);
            }
            $size = (int) $match[2];
            $contents = '';
            if ($type === 'S') {
                for ($i = 0; $i < $size; $i++) {
                    if ($position >= $length) {
                        return $result . \substr($value, $start);
                    }
                    $byte = $value[$position++];
                    if ($byte === '\\') {
                        $hex = \substr($value, $position, 2);
                        if (\strlen($hex) !== 2 || \strspn($hex, '0123456789abcdefABCDEF') !== 2) {
                            return $result . \substr($value, $start);
                        }
                        $byte = \chr(\hexdec($hex));
                        $position += 2;
                    }
                    $contents .= $byte;
                }
            } else {
                if ($size > $length - $position) {
                    return $result . \substr($value, $start);
                }
                $position += $size;
            }

            if ($type === 'O' || $type === 'C') {
                if (!\preg_match('/\G":([+-]?[0-9]+):\{/', $value, $tail, 0, $position)) {
                    return $result . \substr($value, $start);
                }
                $position += \strlen($tail[0]);
                if ($type === 'C') {
                    if ($tail[1] < 0 || $tail[1] >= $length - $position) {
                        return $result . \substr($value, $start);
                    }
                    $payload_length = (int) $tail[1];
                    if ($value[$position + $payload_length] !== '}') {
                        return $result . \substr($value, $start);
                    }
                    $position += $payload_length + 1;
                }
                $result .= \substr($value, $start, $position - $start);
                if ($type === 'O') {
                    $depth++;
                }
                if ($depth === 0) {
                    return $result . \substr($value, $position);
                }
                continue;
            }
            if (\substr($value, $position, 2) !== '";') {
                return $result . \substr($value, $start);
            }
            $position += 2;
            $result .= $type === 'S' ? 's:' . \strlen($contents) . ':"' . $contents . '";' : \substr($value, $start, $position - $start);
            if ($depth === 0) {
                return $result . \substr($value, $position);
            }
        }

        return $result;
    };

    /**
     * Decode native stored values and distinguish malformed serialization.
     *
     * @param mixed $serialized Native serialized carrier after caller hook dispatch.
     * @param bool|null $valid Whether native parsing completed without an error.
     * @return mixed The native value, or null when parsing fails.
     * @throws \Throwable When object hydration or the prior diagnostic handler fails.
     */
    $decodeSettings = static function ($serialized, &$valid = null) use ($normalizeEscapedStrings)
    {
        $valid = false;
        if (!\is_string($serialized)) {
            $valid = true;
            $arguments = array($serialized === null ? '' : $serialized);
            unset($serialized);
            return \unserialize(\array_pop($arguments));
        }
        if ($serialized === '') {
            $valid = true;
            return false;
        }
        if (\strpos($serialized, 'S:') !== false) {
            $serialized = $normalizeEscapedStrings($serialized);
        }
        $valid = true;
        $parse_line = 0;
        $previous = null;
        $previous = \set_error_handler(static function ($severity, $message, $file, $line) use (&$previous, &$parse_line, &$valid) {
            if ($file === __FILE__ && $line === $parse_line && \in_array($severity, array(\E_NOTICE, \E_WARNING), true)
                && \preg_match('/^(?:unserialize\(\): (?:Error at offset [0-9]+ of [0-9]+ bytes|Unexpected end of serialized data|Maximum depth of [0-9]+ exceeded.*)|Insufficient data for unserializing - -?[0-9]+ required, [0-9]+ present)$/D', $message)) {
                // Throwing here would bypass deferred object callbacks during native failure cleanup.
                $valid = false;
                return true;
            }
            if ($file === __FILE__ && $line === $parse_line && \in_array($severity, array(\E_NOTICE, \E_WARNING), true)
                && \preg_match('/^unserialize\(\): (?:Extra data starting at offset [0-9]+ of [0-9]+ bytes|Numerical result out of range)$/D', $message)) {
                // Preserve native accepted prefixes and clamped integers from historical stored values.
                return true;
            }

            return $previous === null ? false : $previous(...\func_get_args());
        });

        try {
            $parse_line = __LINE__ + 1;
            $settings = \unserialize($serialized);

            return $valid ? $settings : null;
        } finally {
            \restore_error_handler();
        }
    };


    return array('decode' => $decodeSettings, 'normalize' => $normalizeEscapedStrings);
})();
