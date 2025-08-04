<?php

declare(strict_types=1);

namespace DevWizard\Textify;

use DevWizard\Textify\Channels\TextifyChannel;
use DevWizard\Textify\Commands\PublishTableCommand;
use DevWizard\Textify\Commands\TextifyCommand;
use DevWizard\Textify\Contracts\TextifyManagerInterface;
use DevWizard\Textify\Contracts\TextifyProviderInterface;
use DevWizard\Textify\Providers\ArrayProvider;
use DevWizard\Textify\Providers\Bangladeshi\AlphaSmsProvider;
use DevWizard\Textify\Providers\Bangladeshi\BulkSmsBdProvider;
use DevWizard\Textify\Providers\Bangladeshi\DhorolaSmsProvider;
use DevWizard\Textify\Providers\Bangladeshi\EsmsProvider;
use DevWizard\Textify\Providers\Bangladeshi\MimSmsProvider;
use DevWizard\Textify\Providers\Bangladeshi\ReveSmsProvider;
use DevWizard\Textify\Providers\Global\NexmoPlaceholderProvider;
use DevWizard\Textify\Providers\Global\NexmoProvider;
use DevWizard\Textify\Providers\Global\TwilioPlaceholderProvider;
use DevWizard\Textify\Providers\Global\TwilioProvider;
use DevWizard\Textify\Providers\LogProvider;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TextifyServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('textify')
            ->hasConfigFile('textify')
            ->hasMigration('create_textify_table')
            ->hasCommands([
                TextifyCommand::class,
                PublishTableCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('textify.manager', function ($app) {
            $config = $app['config']->get('textify', []);

            return new TextifyManager($config);
        });

        $this->app->singleton('textify', function ($app) {
            return new Textify($app['textify.manager']);
        });

        $this->app->alias('textify', Textify::class);
        $this->app->alias('textify.manager', TextifyManagerInterface::class);
    }

    public function packageBooted(): void
    {
        $this->registerProviderFactories();
        $this->registerNotificationChannel();
    }

    protected function registerProviderFactories(): void
    {
        $manager = $this->app['textify.manager'];
        $config = $this->app['config']->get('textify.providers', []);

        // Register all providers without validation - validation happens when provider is used
        foreach ($config as $name => $providerConfig) {
            $manager->extend($name, function () use ($providerConfig, $name) {
                try {
                    return $this->createProvider($providerConfig);
                } catch (\Throwable $e) {
                    throw new \RuntimeException(
                        "Failed to create SMS provider '{$name}': {$e->getMessage()}. Please check your configuration.",
                        0,
                        $e
                    );
                }
            });
        }
    }

    /**
     * Register the Textify notification channel
     */
    protected function registerNotificationChannel(): void
    {
        if (class_exists(ChannelManager::class)) {
            Notification::extend('textify', function ($app) {
                return new TextifyChannel($app[TextifyManagerInterface::class]);
            });
        }
    }

    protected function createProvider(array $config): mixed
    {
        $driver = $config['driver'] ?? null;

        return match ($driver) {
            'dhorola' => $config['class'] ?? new DhorolaSmsProvider($config),
            'bulksmsbd' => $config['class'] ?? new BulkSmsBdProvider($config),
            'mimsms' => $config['class'] ?? new MimSmsProvider($config),
            'esms' => $config['class'] ?? new EsmsProvider($config),
            'revesms' => $config['class'] ?? new ReveSmsProvider($config),
            'alphasms' => $config['class'] ?? new AlphaSmsProvider($config),
            'twilio' => $this->createTwilioProvider($config),
            'nexmo' => $this->createNexmoProvider($config),
            'log' => $config['class'] ?? new LogProvider($config),
            'array' => $config['class'] ?? new ArrayProvider($config),
            default => $this->createCustomProvider($config),
        };
    }

    /**
     * Create a custom provider instance
     */
    protected function createCustomProvider(array $config): mixed
    {
        if (! isset($config['class'])) {
            throw new \InvalidArgumentException('Custom provider requires a "class" configuration key');
        }

        $class = $config['class'];

        // If it's already an instance, validate and return it
        if (is_object($class)) {
            if (! $class instanceof TextifyProviderInterface) {
                throw new \InvalidArgumentException(
                    'Custom provider must implement TextifyProviderInterface'
                );
            }

            return $class;
        }

        // If it's a class name string, instantiate and validate it
        if (is_string($class) && class_exists($class)) {
            $instance = new $class($config);

            if (! $instance instanceof TextifyProviderInterface) {
                throw new \InvalidArgumentException(
                    "Custom provider '{$class}' must implement TextifyProviderInterface"
                );
            }

            return $instance;
        }

        throw new \InvalidArgumentException("Custom provider class '{$class}' does not exist or is not valid");
    }

    /**
     * Create Nexmo provider with dependency check
     */
    private function createNexmoProvider(array $config): NexmoProvider|NexmoPlaceholderProvider
    {
        if (isset($config['class'])) {
            return $config['class'];
        }

        if (! class_exists('Vonage\Client')) {
            // Return placeholder provider that shows helpful error message
            return new NexmoPlaceholderProvider($config);
        }

        return new NexmoProvider($config);
    }

    /**
     * Create Twilio provider with dependency check
     */
    private function createTwilioProvider(array $config): TwilioProvider|TwilioPlaceholderProvider
    {
        if (isset($config['class'])) {
            return $config['class'];
        }

        if (! class_exists('Twilio\Rest\Client')) {
            // Return placeholder provider that shows helpful error message
            return new TwilioPlaceholderProvider($config);
        }

        return new TwilioProvider($config);
    }
}
