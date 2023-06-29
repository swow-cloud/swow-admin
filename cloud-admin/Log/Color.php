<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace CloudAdmin\Log;

use Exception;
use RuntimeException;

class Color
{
    public array $colors = [
        'default' => '39',
        'black' => '30',
        'red' => '31',
        'green' => '32',
        'yellow' => '33',
        'blue' => '34',
        'magenta' => '35',
        'cyan' => '36',
        'light_grey' => '37',
        'dark_grey' => '90',
        'light_red' => '91',
        'light_green' => '92',
        'light_yellow' => '93',
        'light_blue' => '94',
        'light_magenta' => '95',
        'light_cyan' => '96',
        'white' => '97',
    ];

    public array $background = [
        'default' => '49',
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'light_grey' => '47',
        'dark_grey' => '101',
        'light_red' => '101',
        'light_green' => '102',
        'light_yellow' => '103',
        'light_blue' => '104',
        'light_magenta' => '105',
        'light_cyan' => '106',
        'white' => '107',
    ];

    public array $style = [
        'bold' => '1',
        'dim' => '2',
        'underline' => '4',
        'blink' => '5',
        'reverse' => '7',
        'hidden' => '8',
    ];

    public array $attributes = [
        'color',
        'background',
        'style',
    ];

    private bool $isSupported;

    /**
     * Initialize isSupported variable.
     */
    public function __construct()
    {
        $this->isSupported = $this->isSupported();
    }

    /**
     * Print out string with a new line at the end.
     */
    public function out(string $text, array|string $attributes = 'default'): string
    {
        if (! $this->isSupported) {
            return $text . "\n";
        }

        return "{$this->setStyle($attributes)}{$text}{$this->clearStyles()}\n";
    }

    /**
     * Print out string without a new line.
     */
    public function inline(string $text, string $attributes = 'default'): string
    {
        if (! $this->isSupported) {
            return $text;
        }

        return "{$this->setStyle($attributes)}{$text}{$this->clearStyles()}";
    }

    /**
     * Print a new line.
     */
    public function newLine(): string
    {
        return "\n";
    }

    /**
     * Check if script running from CLI.
     */
    public function isSupported(): bool
    {
        return PHP_SAPI === 'cli';
    }

    /**
     * Sets the style for the string.
     */
    private function setStyle(array|string $attributes = []): string
    {
        // If string set only color
        if (is_string($attributes)) {
            // Check if color is in list of colors
            try {
                foreach ($this->colors as $color => $code) {
                    if ($attributes === $color) {
                        return "\e[{$code}m";
                    }
                }

                throw new RuntimeException("Color '{$attributes}' does not exist.");
            } catch (Exception $exception) {
            }
        }

        // Set style if array passed
        $style = '';

        // Check if property exists
        try {
            foreach (array_keys($attributes) as $attribute) {
                if (! property_exists(get_class($this), $attribute)) {
                    throw new RuntimeException("Unknown attribute '{$attribute}'.");
                }
            }
        } catch (Exception $exception) {
        }

        // Check if value exists in that property
        try {
            foreach ($attributes as $attribute => $value) {
                if (! array_key_exists($value, $this->{$attribute})) {
                    throw new RuntimeException("Unknown value '{$value}' of an attribute {$attribute}.");
                }
                // Set style
                $style .= ';' . $this->{$attribute}[$value];
            }
        } catch (Exception $exception) {
        }

        // Return style if all tests passed
        return "\e[{$style}m";
    }

    /**
     * Clear the default styles.
     */
    private function clearStyles(): string
    {
        return "\e[0m";
    }
}
