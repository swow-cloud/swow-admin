<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Http2\HPack;

use CloudAdmin\Http2\Exception\HPackException;
use function array_column;
use function array_pop;
use function array_unshift;
use function bindec;
use function chr;
use function decbin;
use function filter_var;
use function floor;
use function ini_get;
use function max;
use function min;
use function ord;
use function str_contains;
use function str_pad;
use function str_repeat;
use function strlen;
use function substr;
use const FILTER_VALIDATE_BOOLEAN;
use const PHP_SAPI;

final class HPack
{
    public const LAST_INDEX = 61;

    public const TABLE = [ // starts at 1
        [':authority', ''],
        [':method', 'GET'],
        [':method', 'POST'],
        [':path', '/'],
        [':path', '/index.html'],
        [':scheme', 'http'],
        [':scheme', 'https'],
        [':status', '200'],
        [':status', '204'],
        [':status', '206'],
        [':status', '304'],
        [':status', '400'],
        [':status', '404'],
        [':status', '500'],
        ['accept-charset', ''],
        ['accept-encoding', 'gzip, deflate'],
        ['accept-language', ''],
        ['accept-ranges', ''],
        ['accept', ''],
        ['access-control-allow-origin', ''],
        ['age', ''],
        ['allow', ''],
        ['authorization', ''],
        ['cache-control', ''],
        ['content-disposition', ''],
        ['content-encoding', ''],
        ['content-language', ''],
        ['content-length', ''],
        ['content-location', ''],
        ['content-range', ''],
        ['content-type', ''],
        ['cookie', ''],
        ['date', ''],
        ['etag', ''],
        ['expect', ''],
        ['expires', ''],
        ['from', ''],
        ['host', ''],
        ['if-match', ''],
        ['if-modified-since', ''],
        ['if-none-match', ''],
        ['if-range', ''],
        ['if-unmodified-since', ''],
        ['last-modified', ''],
        ['link', ''],
        ['location', ''],
        ['max-forwards', ''],
        ['proxy-authentication', ''],
        ['proxy-authorization', ''],
        ['range', ''],
        ['referer', ''],
        ['refresh', ''],
        ['retry-after', ''],
        ['server', ''],
        ['set-cookie', ''],
        ['strict-transport-security', ''],
        ['transfer-encoding', ''],
        ['user-agent', ''],
        ['vary', ''],
        ['via', ''],
        ['www-authenticate', ''],
    ];

    private const HUFFMAN_CODE = [
        /* 0x00 */
        0x1FF8, 0x7FFFD8, 0xFFFFFE2, 0xFFFFFE3, 0xFFFFFE4, 0xFFFFFE5, 0xFFFFFE6, 0xFFFFFE7,
        /* 0x08 */
        0xFFFFFE8, 0xFFFFEA, 0x3FFFFFFC, 0xFFFFFE9, 0xFFFFFEA, 0x3FFFFFFD, 0xFFFFFEB, 0xFFFFFEC,
        /* 0x10 */
        0xFFFFFED, 0xFFFFFEE, 0xFFFFFEF, 0xFFFFFF0, 0xFFFFFF1, 0xFFFFFF2, 0x3FFFFFFE, 0xFFFFFF3,
        /* 0x18 */
        0xFFFFFF4, 0xFFFFFF5, 0xFFFFFF6, 0xFFFFFF7, 0xFFFFFF8, 0xFFFFFF9, 0xFFFFFFA, 0xFFFFFFB,
        /* 0x20 */
        0x14, 0x3F8, 0x3F9, 0xFFA, 0x1FF9, 0x15, 0xF8, 0x7FA,
        /* 0x28 */
        0x3FA, 0x3FB, 0xF9, 0x7FB, 0xFA, 0x16, 0x17, 0x18,
        /* 0x30 */
        0x0, 0x1, 0x2, 0x19, 0x1A, 0x1B, 0x1C, 0x1D,
        /* 0x38 */
        0x1E, 0x1F, 0x5C, 0xFB, 0x7FFC, 0x20, 0xFFB, 0x3FC,
        /* 0x40 */
        0x1FFA, 0x21, 0x5D, 0x5E, 0x5F, 0x60, 0x61, 0x62,
        /* 0x48 */
        0x63, 0x64, 0x65, 0x66, 0x67, 0x68, 0x69, 0x6A,
        /* 0x50 */
        0x6B, 0x6C, 0x6D, 0x6E, 0x6F, 0x70, 0x71, 0x72,
        /* 0x58 */
        0xFC, 0x73, 0xFD, 0x1FFB, 0x7FFF0, 0x1FFC, 0x3FFC, 0x22,
        /* 0x60 */
        0x7FFD, 0x3, 0x23, 0x4, 0x24, 0x5, 0x25, 0x26,
        /* 0x68 */
        0x27, 0x6, 0x74, 0x75, 0x28, 0x29, 0x2A, 0x7,
        /* 0x70 */
        0x2B, 0x76, 0x2C, 0x8, 0x9, 0x2D, 0x77, 0x78,
        /* 0x78 */
        0x79, 0x7A, 0x7B, 0x7FFE, 0x7FC, 0x3FFD, 0x1FFD, 0xFFFFFFC,
        /* 0x80 */
        0xFFFE6, 0x3FFFD2, 0xFFFE7, 0xFFFE8, 0x3FFFD3, 0x3FFFD4, 0x3FFFD5, 0x7FFFD9,
        /* 0x88 */
        0x3FFFD6, 0x7FFFDA, 0x7FFFDB, 0x7FFFDC, 0x7FFFDD, 0x7FFFDE, 0xFFFFEB, 0x7FFFDF,
        /* 0x90 */
        0xFFFFEC, 0xFFFFED, 0x3FFFD7, 0x7FFFE0, 0xFFFFEE, 0x7FFFE1, 0x7FFFE2, 0x7FFFE3,
        /* 0x98 */
        0x7FFFE4, 0x1FFFDC, 0x3FFFD8, 0x7FFFE5, 0x3FFFD9, 0x7FFFE6, 0x7FFFE7, 0xFFFFEF,
        /* 0xA0 */
        0x3FFFDA, 0x1FFFDD, 0xFFFE9, 0x3FFFDB, 0x3FFFDC, 0x7FFFE8, 0x7FFFE9, 0x1FFFDE,
        /* 0xA8 */
        0x7FFFEA, 0x3FFFDD, 0x3FFFDE, 0xFFFFF0, 0x1FFFDF, 0x3FFFDF, 0x7FFFEB, 0x7FFFEC,
        /* 0xB0 */
        0x1FFFE0, 0x1FFFE1, 0x3FFFE0, 0x1FFFE2, 0x7FFFED, 0x3FFFE1, 0x7FFFEE, 0x7FFFEF,
        /* 0xB8 */
        0xFFFEA, 0x3FFFE2, 0x3FFFE3, 0x3FFFE4, 0x7FFFF0, 0x3FFFE5, 0x3FFFE6, 0x7FFFF1,
        /* 0xC0 */
        0x3FFFFE0, 0x3FFFFE1, 0xFFFEB, 0x7FFF1, 0x3FFFE7, 0x7FFFF2, 0x3FFFE8, 0x1FFFFEC,
        /* 0xC8 */
        0x3FFFFE2, 0x3FFFFE3, 0x3FFFFE4, 0x7FFFFDE, 0x7FFFFDF, 0x3FFFFE5, 0xFFFFF1, 0x1FFFFED,
        /* 0xD0 */
        0x7FFF2, 0x1FFFE3, 0x3FFFFE6, 0x7FFFFE0, 0x7FFFFE1, 0x3FFFFE7, 0x7FFFFE2, 0xFFFFF2,
        /* 0xD8 */
        0x1FFFE4, 0x1FFFE5, 0x3FFFFE8, 0x3FFFFE9, 0xFFFFFFD, 0x7FFFFE3, 0x7FFFFE4, 0x7FFFFE5,
        /* 0xE0 */
        0xFFFEC, 0xFFFFF3, 0xFFFED, 0x1FFFE6, 0x3FFFE9, 0x1FFFE7, 0x1FFFE8, 0x7FFFF3,
        /* 0xE8 */
        0x3FFFEA, 0x3FFFEB, 0x1FFFFEE, 0x1FFFFEF, 0xFFFFF4, 0xFFFFF5, 0x3FFFFEA, 0x7FFFF4,
        /* 0xF0 */
        0x3FFFFEB, 0x7FFFFE6, 0x3FFFFEC, 0x3FFFFED, 0x7FFFFE7, 0x7FFFFE8, 0x7FFFFE9, 0x7FFFFEA,
        /* 0xF8 */
        0x7FFFFEB, 0xFFFFFFE, 0x7FFFFEC, 0x7FFFFED, 0x7FFFFEE, 0x7FFFFEF, 0x7FFFFF0, 0x3FFFFEE,
        /* end! */
        0x3FFFFFFF,
    ];

    private const HUFFMAN_CODE_LENGTHS = [
        /* 0x00 */
        13, 23, 28, 28, 28, 28, 28, 28,
        /* 0x08 */
        28, 24, 30, 28, 28, 30, 28, 28,
        /* 0x10 */
        28, 28, 28, 28, 28, 28, 30, 28,
        /* 0x18 */
        28, 28, 28, 28, 28, 28, 28, 28,
        /* 0x20 */
        6, 10, 10, 12, 13, 6, 8, 11,
        /* 0x28 */
        10, 10, 8, 11, 8, 6, 6, 6,
        /* 0x30 */
        5, 5, 5, 6, 6, 6, 6, 6,
        /* 0x38 */
        6, 6, 7, 8, 15, 6, 12, 10,
        /* 0x40 */
        13, 6, 7, 7, 7, 7, 7, 7,
        /* 0x48 */
        7, 7, 7, 7, 7, 7, 7, 7,
        /* 0x50 */
        7, 7, 7, 7, 7, 7, 7, 7,
        /* 0x58 */
        8, 7, 8, 13, 19, 13, 14, 6,
        /* 0x60 */
        15, 5, 6, 5, 6, 5, 6, 6,
        /* 0x68 */
        6, 5, 7, 7, 6, 6, 6, 5,
        /* 0x70 */
        6, 7, 6, 5, 5, 6, 7, 7,
        /* 0x78 */
        7, 7, 7, 15, 11, 14, 13, 28,
        /* 0x80 */
        20, 22, 20, 20, 22, 22, 22, 23,
        /* 0x88 */
        22, 23, 23, 23, 23, 23, 24, 23,
        /* 0x90 */
        24, 24, 22, 23, 24, 23, 23, 23,
        /* 0x98 */
        23, 21, 22, 23, 22, 23, 23, 24,
        /* 0xA0 */
        22, 21, 20, 22, 22, 23, 23, 21,
        /* 0xA8 */
        23, 22, 22, 24, 21, 22, 23, 23,
        /* 0xB0 */
        21, 21, 22, 21, 23, 22, 23, 23,
        /* 0xB8 */
        20, 22, 22, 22, 23, 22, 22, 23,
        /* 0xC0 */
        26, 26, 20, 19, 22, 23, 22, 25,
        /* 0xC8 */
        26, 26, 26, 27, 27, 26, 24, 25,
        /* 0xD0 */
        19, 21, 26, 27, 27, 26, 27, 24,
        /* 0xD8 */
        21, 21, 26, 26, 28, 27, 27, 27,
        /* 0xE0 */
        20, 24, 20, 21, 22, 21, 21, 23,
        /* 0xE8 */
        22, 22, 25, 25, 24, 24, 26, 23,
        /* 0xF0 */
        26, 27, 26, 26, 27, 27, 27, 27,
        /* 0xF8 */
        27, 28, 27, 27, 27, 27, 27, 26,
        /* end! */
        30,
    ];

    private const DEFAULT_COMPRESSION_THRESHOLD = 1024;

    private const DEFAULT_MAX_SIZE = 4096;

    private static $huffmanLookup;

    private static $huffmanCodes;

    private static $huffmanLengths;

    private static array $indexMap = [];

    /** @var string[][] */
    private array $headers = [];

    private int $hardMaxSize = self::DEFAULT_MAX_SIZE;

    /** @var int Max table size. */
    private int $currentMaxSize = self::DEFAULT_MAX_SIZE;

    /** @var int Current table size. */
    private int $size = 0;

    /**
     * @param int $maxSize upper limit on table size
     */
    public function __construct(int $maxSize = self::DEFAULT_MAX_SIZE)
    {
        $this->hardMaxSize = $maxSize;
    }

    /**
     * @return null|string returns null if decoding fails
     */
    public static function huffmanDecode(string $input): ?string /* : ?string */
    {
        $huffmanLookup = self::$huffmanLookup;
        $lookup = 0;
        $lengths = self::$huffmanLengths;
        $length = strlen($input);
        $out = str_repeat("\0", (int)floor($length / 5 * 8 + 1)); // max length

        // Fail if EOS symbol is found.
        if (str_contains($input, "\x3f\xff\xff\xff")) {
            return null;
        }

        for ($bitCount = $off = $i = 0; $i < $length; ++$i) {
            [$lookup, $chr] = $huffmanLookup[$lookup][$input[$i]];

            if ($chr === null) {
                continue;
            }

            if ($chr === '') {
                return null;
            }

            $out[$off++] = $chr[0];
            $bitCount += $lengths[$chr[0]];

            if (isset($chr[1])) {
                $out[$off++] = $chr[1];
                $bitCount += $lengths[$chr[1]];
            }
        }

        // Padding longer than 7-bits
        if ($i && $chr === null) {
            return null;
        }

        // Check for 0's in padding
        if ($bitCount & 7) {
            $mask = 0xFF >> ($bitCount & 7);
            if ((ord($input[$i - 1]) & $mask) !== $mask) {
                return null;
            }
        }

        return substr($out, 0, $off);
    }

    public static function huffmanEncode(string $input): string
    {
        $codes = self::$huffmanCodes;
        $lengths = self::$huffmanLengths;

        $length = strlen($input);
        $out = str_repeat("\0", $length * 5 + 1); // max length

        for ($bitCount = $i = 0; $i < $length; ++$i) {
            $chr = $input[$i];
            $byte = $bitCount >> 3;

            foreach ($codes[$bitCount & 7][$chr] as $bits) {
                // Note: |= can't be used with strings in PHP
                $out[$byte] = $out[$byte] | $bits;
                ++$byte;
            }

            $bitCount += $lengths[$chr];
        }

        if ($bitCount & 7) {
            // Note: |= can't be used with strings in PHP
            $out[$byte - 1] = $out[$byte - 1] | \chr(0xFF >> ($bitCount & 7));
        }

        return $i ? substr($out, 0, $byte) : '';
    }

    /**
     * Sets the upper limit on table size. Dynamic table updates requesting a size above this size will result in a
     * decoding error (i.e., returning null from decode()).
     */
    public function setTableSizeLimit(int $maxSize): void /* : void */
    {
        $this->hardMaxSize = $maxSize;
    }

    /**
     * Resizes the table to the given size, removing old entries as per section 4.4 if necessary.
     */
    public function resizeTable(int $size = null): void /* : void */
    {
        if ($size !== null) {
            $this->currentMaxSize = max(0, min($size, $this->hardMaxSize));
        }

        while ($this->size > $this->currentMaxSize) {
            [$name, $value] = array_pop($this->headers);
            $this->size -= 32 + strlen($name) + strlen($value);
        }
    }

    /**
     * @param string $input encoded headers
     * @param int $maxSize maximum length of the decoded header string
     * @return null|string[][] returns null if decoding fails or if $maxSize is exceeded
     */
    public function decode(string $input, int $maxSize): ?array /* : ?array */
    {
        $headers = [];
        $off = 0;
        $inputLength = strlen($input);
        $size = 0;

        try {
            // dynamic $table as per 2.3.2
            while ($off < $inputLength) {
                $index = ord($input[$off++]);

                if ($index & 0x80) {
                    // range check
                    if ($index <= self::LAST_INDEX + 0x80) {
                        if ($index === 0x80) {
                            return null;
                        }

                        [$name, $value] = $headers[] = self::TABLE[$index - 0x81];
                    } else {
                        if ($index == 0xFF) {
                            $index = self::decodeDynamicInteger($input, $off) + 0xFF;
                        }

                        $index -= 0x81 + self::LAST_INDEX;
                        if (!isset($this->headers[$index])) {
                            return null;
                        }

                        [$name, $value] = $headers[] = $this->headers[$index];
                    }
                } elseif (($index & 0x60) !== 0x20) { // (($index & 0x40) || !($index & 0x20)): bit 4: never index is ignored
                    $dynamic = (bool)($index & 0x40);

                    if ($index & ($dynamic ? 0x3F : 0x0F)) { // separate length
                        if ($dynamic) {
                            if ($index === 0x7F) {
                                $index = self::decodeDynamicInteger($input, $off) + 0x3F;
                            } else {
                                $index &= 0x3F;
                            }
                        } else {
                            $index &= 0x0F;
                            if ($index === 0x0F) {
                                $index = self::decodeDynamicInteger($input, $off) + 0x0F;
                            }
                        }

                        if ($index < 0) {
                            return null;
                        }

                        if ($index <= self::LAST_INDEX) {
                            $header = self::TABLE[$index - 1];
                        } elseif (!isset($this->headers[$index - 1 - self::LAST_INDEX])) {
                            return null;
                        } else {
                            $header = $this->headers[$index - 1 - self::LAST_INDEX];
                        }
                    } else {
                        if ($off >= $inputLength) {
                            return null;
                        }

                        $length = ord($input[$off++]);
                        $huffman = $length & 0x80;
                        $length &= 0x7F;

                        if ($length === 0x7F) {
                            $length = self::decodeDynamicInteger($input, $off) + 0x7F;
                        }

                        if ($inputLength - $off < $length || $length <= 0) {
                            return null;
                        }

                        if ($huffman) {
                            $header = [self::huffmanDecode(substr($input, $off, $length))];
                            if ($header[0] === null) {
                                return null;
                            }
                        } else {
                            $header = [substr($input, $off, $length)];
                        }

                        $off += $length;
                    }

                    if ($off >= $inputLength) {
                        return null;
                    }

                    $length = ord($input[$off++]);
                    $huffman = $length & 0x80;
                    $length &= 0x7F;

                    if ($length === 0x7F) {
                        $length = self::decodeDynamicInteger($input, $off) + 0x7F;
                    }

                    if ($inputLength - $off < $length || $length < 0) {
                        return null;
                    }

                    if ($huffman) {
                        $header[1] = self::huffmanDecode(substr($input, $off, $length));
                        if ($header[1] === null) {
                            return null;
                        }
                    } else {
                        $header[1] = substr($input, $off, $length);
                    }

                    $off += $length;

                    if ($dynamic) {
                        array_unshift($this->headers, $header);
                        $this->size += 32 + strlen($header[0]) + strlen($header[1]);
                        if ($this->currentMaxSize < $this->size) {
                            $this->resizeTable();
                        }
                    }

                    [$name, $value] = $headers[] = $header;
                } else { // if ($index & 0x20) {
                    if ($off >= $inputLength) {
                        return null; // Dynamic table size update must not be the last entry in header block.
                    }

                    $index &= 0x1F;
                    if ($index === 0x1F) {
                        $index = self::decodeDynamicInteger($input, $off) + 0x1F;
                    }

                    if ($index > $this->hardMaxSize) {
                        return null;
                    }

                    $this->resizeTable($index);

                    continue;
                }

                $size += strlen($name) + strlen($value);

                if ($size > $maxSize) {
                    return null;
                }
            }
        } catch (HPackException $e) {
            return null;
        }

        return $headers;
    }

    /**
     * @param string[][] $headers
     * @param int $compressionThreshold compress strings whose length is at least the number of bytes given
     */
    public function encode(array $headers, int $compressionThreshold = self::DEFAULT_COMPRESSION_THRESHOLD): string
    {
        // @TODO implementation is deliberately primitive... [doesn't use any dynamic table...]
        $output = '';

        foreach ($headers as [$name, $value]) {
            if (isset(self::$indexMap[$name])) {
                $index = self::$indexMap[$name];
                if ($index < 0x10) {
                    $output .= chr($index);
                } else {
                    $output .= "\x0f" . chr($index - 0x0F);
                }
            } else {
                $output .= "\0" . $this->encodeString($name, $compressionThreshold);
            }

            $output .= $this->encodeString($value, $compressionThreshold);
        }

        return $output;
    }

    /** Called via bindTo(), see end of file */
    public static function init(): void /* : void */
    {
        self::$huffmanLookup = self::huffmanLookupInit();
        self::$huffmanCodes = self::huffmanCodesInit();
        self::$huffmanLengths = self::huffmanLengthsInit();

        foreach (array_column(self::TABLE, 0) as $index => $name) {
            if (isset(self::$indexMap[$name])) {
                continue;
            }

            self::$indexMap[$name] = $index + 1;
        }
    }

    // (micro-)optimized decode
    private static function huffmanLookupInit(): array
    {
        if (('cli' !== PHP_SAPI && 'phpdbg' !== PHP_SAPI) || filter_var(ini_get('opcache.enable_cli'), FILTER_VALIDATE_BOOLEAN)) {
            return require __DIR__ . '/huffman-lookup.php';
        }

        $encodingAccess = [];
        $terminals = [];
        $index = 7;

        foreach (self::HUFFMAN_CODE as $chr => $bits) {
            $len = self::HUFFMAN_CODE_LENGTHS[$chr];

            for ($bit = 0; $bit < 8; ++$bit) {
                $offlen = $len + $bit;
                $next = $bit;

                for ($byte = ($offlen - 1) >> 3; $byte > 0; --$byte) {
                    $cur = str_pad(decbin(($bits >> ($byte * 8 - ((0x30 - $offlen) & 7))) & 0xFF), 8, '0', STR_PAD_LEFT);
                    if (($encodingAccess[$next][$cur][0] ?? 0) !== 0) {
                        $next = $encodingAccess[$next][$cur][0];
                    } else {
                        $encodingAccess[$next][$cur] = [++$index, null];
                        $next = $index;
                    }
                }

                $key = str_pad(
                    decbin($bits & ((1 << ((($offlen - 1) & 7) + 1)) - 1)),
                    (($offlen - 1) & 7) + 1,
                    '0',
                    STR_PAD_LEFT
                );
                $encodingAccess[$next][$key] = [null, $chr > 0xFF ? '' : chr($chr)];

                if ($offlen & 7) {
                    $terminals[$offlen & 7][] = [$key, $next];
                } else {
                    $encodingAccess[$next][$key][0] = 0;
                }
            }
        }

        $memoize = [];
        for ($off = 7; $off > 0; --$off) {
            foreach ($terminals[$off] as [$key, $next]) {
                if ($encodingAccess[$next][$key][0] === null) {
                    foreach ($encodingAccess[$off] as $chr => $cur) {
                        $encodingAccess[$next][($memoize[$key] ?? $memoize[$key] = str_pad($key, 8, '0', STR_PAD_RIGHT)) | $chr] =
                            [$cur[0], $encodingAccess[$next][$key][1] != '' ? $encodingAccess[$next][$key][1] . $cur[1] : ''];
                    }

                    unset($encodingAccess[$next][$key]);
                }
            }
        }

        $memoize = [];
        for ($off = 7; $off > 0; --$off) {
            foreach ($terminals[$off] as [$key, $next]) {
                foreach ($encodingAccess[$next] as $k => $v) {
                    if (strlen((string)$k) !== 1) {
                        //todo TypeError: bindec(): Argument #1 ($binary_string) must be of type string, int given in
                        $encodingAccess[$next][$memoize[$k] ?? $memoize[$k] = chr(bindec($k))] = $v;
                        unset($encodingAccess[$next][$k]);
                    }
                }
            }

            unset($encodingAccess[$off]);
        }

        return $encodingAccess;
    }

    private static function huffmanCodesInit(): array
    {
        if (('cli' !== PHP_SAPI && 'phpdbg' !== PHP_SAPI) || filter_var(ini_get('opcache.enable_cli'), FILTER_VALIDATE_BOOLEAN)) {
            return require __DIR__ . '/huffman-codes.php';
        }

        $lookup = [];

        for ($chr = 0; $chr <= 0xFF; ++$chr) {
            $bits = self::HUFFMAN_CODE[$chr];
            $length = self::HUFFMAN_CODE_LENGTHS[$chr];

            for ($bit = 0; $bit < 8; ++$bit) {
                $bytes = ($length + $bit - 1) >> 3;
                $codes = [];

                for ($byte = $bytes; $byte >= 0; --$byte) {
                    $codes[] = chr(
                        $byte
                            ? $bits >> ($length - ($bytes - $byte + 1) * 8 + $bit)
                            : ($bits << ((0x30 - $length - $bit) & 7))
                    );
                }

                $lookup[$bit][chr($chr)] = $codes;
            }
        }

        return $lookup;
    }

    private static function huffmanLengthsInit(): array
    {
        $lengths = [];

        for ($chr = 0; $chr <= 0xFF; ++$chr) {
            $lengths[chr($chr)] = self::HUFFMAN_CODE_LENGTHS[$chr];
        }

        return $lengths;
    }

    private static function decodeDynamicInteger(string $input, int &$off): int
    {
        if (!isset($input[$off])) {
            throw new HPackException('Invalid input data, too short for dynamic integer');
        }

        $c = ord($input[$off++]);
        $int = $c & 0x7F;
        $i = 0;

        while ($c & 0x80) {
            if (!isset($input[$off])) {
                return -0x80;
            }

            $c = ord($input[$off++]);
            $int += ($c & 0x7F) << (++$i * 7);
        }

        return $int;
    }

    private static function encodeDynamicInteger(int $int): string
    {
        $out = '';
        for ($i = 0; ($int >> $i) > 0x80; $i += 7) {
            $out .= chr(0x80 | (($int >> $i) & 0x7F));
        }
        return $out . chr($int >> $i);
    }

    private function encodeString(string $value, int $compressionThreshold): string
    {
        $prefix = "\0";
        if (strlen($value) >= $compressionThreshold) {
            $value = self::huffmanEncode($value);
            $prefix = "\x80";
        }

        if (strlen($value) < 0x7F) {
            return ($prefix | chr(strlen($value))) . $value;
        }

        return ($prefix | "\x7f") . self::encodeDynamicInteger(strlen($value) - 0x7F) . $value;
    }
}

(function () {
    static::init();
})->bindTo(null, HPack::class)();
