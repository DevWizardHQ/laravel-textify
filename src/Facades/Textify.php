<?php

declare(strict_types=1);

namespace DevWizard\Textify\Facades;

use DevWizard\Textify\Contracts\TextifyManagerInterface;
use DevWizard\Textify\Contracts\TextifyProviderInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed send(string|array|null $to = null, ?string $message = null, ?string $from = null)
 * @method static TextifyManagerInterface to(string|array $contacts)
 * @method static TextifyManagerInterface message(string $message)
 * @method static TextifyManagerInterface from(string $from)
 * @method static TextifyManagerInterface via(string $driver)
 * @method static TextifyManagerInterface driver(string $driver)
 * @method static mixed queue(?string $queueName = null)
 * @method static TextifyManagerInterface fallback(string $driver)
 * @method static array getProviders()
 * @method static bool hasProvider(string $name)
 * @method static TextifyProviderInterface getProvider(string $name)
 * @method static TextifyProviderInterface getDriver(?string $name = null)
 * @method static float getBalance(?string $provider = null)
 * @method static TextifyManagerInterface reset()
 *
 * @see \DevWizard\Textify\Textify
 */
class Textify extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'textify';
    }
}
