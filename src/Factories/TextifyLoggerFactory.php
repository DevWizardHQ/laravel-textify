<?php

declare(strict_types=1);

namespace DevWizard\Textify\Factories;

use DevWizard\Textify\Contracts\TextifyLoggerInterface;
use DevWizard\Textify\Loggers\DatabaseLogger;
use DevWizard\Textify\Loggers\FileLogger;
use DevWizard\Textify\Loggers\NullLogger;
use InvalidArgumentException;

class TextifyLoggerFactory
{
    public static function create(string $driver): TextifyLoggerInterface
    {
        return match ($driver) {
            'database' => new DatabaseLogger,
            'file' => new FileLogger,
            'null' => new NullLogger,
            default => throw new InvalidArgumentException("Unsupported Textify logger driver: {$driver}"),
        };
    }

    public static function createFromConfig(): TextifyLoggerInterface
    {
        $config = config('textify.logging', []);

        if (! ($config['enabled'] ?? true)) {
            return new NullLogger;
        }

        $driver = $config['driver'] ?? 'file';

        return self::create($driver);
    }
}
