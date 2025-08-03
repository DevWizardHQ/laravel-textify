<?php

declare(strict_types=1);

namespace DevWizard\Textify\Factories;

use DevWizard\Textify\ActivityTrackers\DatabaseActivityTracker;
use DevWizard\Textify\ActivityTrackers\FileActivityTracker;
use DevWizard\Textify\ActivityTrackers\NullActivityTracker;
use DevWizard\Textify\Contracts\ActivityTrackerInterface;
use InvalidArgumentException;

class ActivityTrackerFactory
{
    public static function create(string $driver): ActivityTrackerInterface
    {
        return match ($driver) {
            'database' => new DatabaseActivityTracker,
            'file' => new FileActivityTracker,
            'null' => new NullActivityTracker,
            default => throw new InvalidArgumentException("Unsupported activity tracker driver: {$driver}")
        };
    }
}
