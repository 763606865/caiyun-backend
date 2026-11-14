<?php

namespace App\Admin\Commands\Concerns;

use InvalidArgumentException;

/** Console input is untyped; reject malformed values before changing integration state. */
trait ReadsIntegrationOptions
{
    private function stringArgument(string $name): string
    {
        $value = $this->argument($name);
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException("Argument {$name} must be a non-empty string.");
        }

        return $value;
    }

    private function stringOption(string $name): ?string
    {
        $value = $this->option($name);
        if ($value === null) {
            return null;
        }
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException("Option {$name} must be a non-empty string.");
        }

        return $value;
    }

    /** @return list<string> */
    private function stringListOption(string $name): array
    {
        $value = $this->option($name);
        if (! is_array($value)) {
            throw new InvalidArgumentException("Option {$name} must be a list of strings.");
        }
        foreach ($value as $item) {
            if (! is_string($item) || trim($item) === '') {
                throw new InvalidArgumentException("Option {$name} must contain non-empty strings.");
            }
        }

        return array_values($value);
    }
}
