<?php

declare(strict_types=1);

namespace DevWizard\Textify;

use Closure;
use DevWizard\Textify\Contracts\TextifyManagerInterface;
use DevWizard\Textify\Contracts\TextifyProviderInterface;
use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Exceptions\TextifyException;
use DevWizard\Textify\Jobs\SendTextifyJob;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * TextifyManager - Core SMS management class
 *
 * This class provides the main SMS sending functionality with support for multiple providers,
 * fallback mechanisms, fluent interface, and queue integration. It serves as the central
 * orchestrator for all SMS operations in the Laravel Textify package.
 *
 * Features:
 * - Multi-provider SMS sending
 * - Automatic fallback system
 * - Fluent interface for clean code
 * - Queue integration for background processing
 * - Bulk SMS support
 * - Provider-specific configurations
 */
class TextifyManager implements TextifyManagerInterface
{
    /**
     * Registered SMS providers
     *
     * @var array<string, TextifyProviderInterface|Closure>
     */
    protected array $providers = [];

    /**
     * Default SMS provider name
     */
    protected ?string $defaultProvider = null;

    /**
     * Fallback SMS provider name
     */
    protected ?string $fallbackProvider = null;

    /**
     * Configuration array
     */
    protected array $config;

    /**
     * Prepared contact data for fluent interface
     */
    protected string|array|null $preparedContacts = null;

    /**
     * Prepared message text for fluent interface
     */
    protected ?string $preparedMessage = null;

    /**
     * Prepared sender ID for fluent interface
     */
    protected ?string $preparedFrom = null;

    /**
     * Create a new TextifyManager instance
     *
     * @param  array  $config  Configuration array containing provider settings
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->defaultProvider = $config['default'] ?? null;
    }

    /**
     * Register an SMS provider or provider factory
     *
     * This method allows registering either a provider instance or a closure that
     * returns a provider instance (factory pattern). Factories are useful for
     * lazy loading providers and dependency injection.
     *
     * @param  string  $name  Provider name identifier
     * @param  TextifyProviderInterface|Closure  $provider  Provider instance or factory
     */
    public function extend(string $name, TextifyProviderInterface|Closure $provider): self
    {
        $this->providers[$name] = $provider;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function send(string|array|null $to = null, ?string $message = null, ?string $from = null): mixed
    {
        // Use provided parameters or fall back to prepared data
        $contacts = $to ?? $this->preparedContacts;
        $messageText = $message ?? $this->preparedMessage;
        $sender = $from ?? $this->preparedFrom;

        if ($contacts === null) {
            throw new TextifyException('No contacts specified. Use to() method or provide contacts in send().');
        }

        if ($messageText === null && ! $this->isStructuredArray($contacts)) {
            throw new TextifyException('No message specified. Use message() method or provide message in send().');
        }

        // Reset prepared data after use
        $this->reset();

        return $this->processSend($contacts, $messageText, $sender);
    }

    /**
     * {@inheritdoc}
     */
    public function to(string|array $contacts): self
    {
        $this->preparedContacts = $contacts;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function message(string $message): self
    {
        $this->preparedMessage = $message;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function from(string $from): self
    {
        $this->preparedFrom = $from;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): self
    {
        $this->preparedContacts = null;
        $this->preparedMessage = null;
        $this->preparedFrom = null;

        return $this;
    }

    /**
     * Process different types of send operations
     */
    protected function processSend(string|array $contacts, ?string $message, ?string $from): mixed
    {
        // Case 1: Single SMS - send('01712345678', 'message')
        if (is_string($contacts)) {
            $textifyMessage = TextifyMessage::create($contacts, $message, $from);

            return $this->getDriver()->send($textifyMessage);
        }

        // Case 2: Array input
        // Case 2a: Structured array with 'to' and 'message' keys
        // send([['to' => '01712345678', 'message' => 'msg1'], ['to' => '01812345678', 'message' => 'msg2']])
        if ($this->isStructuredArray($contacts)) {
            return $this->sendStructuredArray($contacts, $from);
        }

        // Case 2b: Simple array of phone numbers with same message
        // send(['01712345678', '01812345678'], 'message')
        if ($message !== null) {
            return $this->sendToMultiple($contacts, $message, $from);
        }

        throw new TextifyException('Invalid array format. Provide structured array with to/message or simple array with message parameter.');
    }

    /**
     * Check if array contains structured messages with 'to' and 'message' keys
     */
    protected function isStructuredArray(mixed $data): bool
    {
        if (! is_array($data) || empty($data)) {
            return false;
        }

        $firstElement = reset($data);

        return is_array($firstElement) && isset($firstElement['to']) && isset($firstElement['message']);
    }

    /**
     * Send structured array of messages
     */
    protected function sendStructuredArray(array $messages, ?string $defaultFrom = null): array
    {
        $responses = [];
        foreach ($messages as $messageData) {
            $textifyMessage = TextifyMessage::create(
                $messageData['to'],
                $messageData['message'],
                $messageData['from'] ?? $defaultFrom,
                $messageData['metadata'] ?? []
            );
            $responses[] = $this->getDriver()->send($textifyMessage);
        }

        return $responses;
    }

    /**
     * Send same message to multiple phone numbers
     */
    protected function sendToMultiple(array $phoneNumbers, string $message, ?string $from): array
    {
        $responses = [];
        foreach ($phoneNumbers as $phoneNumber) {
            $textifyMessage = TextifyMessage::create($phoneNumber, $message, $from);
            $responses[] = $this->getDriver()->send($textifyMessage);
        }

        return $responses;
    }

    /**
     * {@inheritdoc}
     */
    public function via(string $driver): self
    {
        $this->defaultProvider = $driver;

        return $this;
    }

    /**
     * Alias for via() method for Laravel Manager pattern compatibility
     */
    public function driver(string $driver): self
    {
        return $this->via($driver);
    }

    /**
     * {@inheritdoc}
     */
    public function queue(?string $queueName = null): mixed
    {
        if ($this->preparedContacts === null) {
            throw new TextifyException('No contacts prepared for queue. Use to() method first.');
        }

        if ($this->preparedMessage === null && ! $this->isStructuredArray($this->preparedContacts)) {
            throw new TextifyException('No message prepared for queue. Use message() method first.');
        }

        // Capture prepared data before reset
        $contacts = $this->preparedContacts;
        $message = $this->preparedMessage;
        $from = $this->preparedFrom;
        $provider = $this->defaultProvider ?: 'default';

        // Reset prepared data after use (consistent with send() method)
        $this->reset();

        // Handle single contact (string)
        if (is_string($contacts)) {
            $textifyMessage = TextifyMessage::create($contacts, $message, $from);
            $job = new SendTextifyJob($textifyMessage, $provider);

            if ($queueName) {
                return dispatch($job)->onQueue($queueName);
            }

            return dispatch($job);
        }

        // Handle array of contacts - create multiple jobs
        $jobs = [];

        // Case 1: Structured array with 'to' and 'message' keys
        if ($this->isStructuredArray($contacts)) {
            foreach ($contacts as $messageData) {
                $textifyMessage = TextifyMessage::create(
                    $messageData['to'],
                    $messageData['message'],
                    $messageData['from'] ?? $from,
                    $messageData['metadata'] ?? []
                );

                $job = new SendTextifyJob($textifyMessage, $provider);
                $dispatchedJob = $queueName ? dispatch($job)->onQueue($queueName) : dispatch($job);
                $jobs[] = $dispatchedJob;
            }
        } else {
            // Case 2: Simple array of phone numbers with same message
            foreach ($contacts as $phoneNumber) {
                $textifyMessage = TextifyMessage::create($phoneNumber, $message, $from);
                $job = new SendTextifyJob($textifyMessage, $provider);
                $dispatchedJob = $queueName ? dispatch($job)->onQueue($queueName) : dispatch($job);
                $jobs[] = $dispatchedJob;
            }
        }

        return $jobs;
    }

    /**
     * {@inheritdoc}
     */
    public function getDriver(?string $name = null): TextifyProviderInterface
    {
        $name = $name ?: $this->defaultProvider;

        if (! $name) {
            throw TextifyException::providerNotFound('No default provider configured');
        }

        if (! isset($this->providers[$name])) {
            throw TextifyException::providerNotFound($name);
        }

        $provider = $this->providers[$name];

        // If it's a closure (factory), call it to create the provider instance
        if ($provider instanceof Closure) {
            $provider = $provider();
            // Cache the resolved provider
            $this->providers[$name] = $provider;
        }

        return $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function fallback(string $driver): self
    {
        $this->fallbackProvider = $driver;

        return $this;
    }

    /**
     * Send SMS with fallback support
     */
    public function sendWithFallback(string $to, string $message, ?string $from = null): TextifyResponse
    {
        try {
            $response = $this->send($to, $message, $from);

            if ($response->isSuccessful()) {
                return $response;
            }

            // Try fallback if configured and primary failed
            if ($this->fallbackProvider && $this->shouldUseFallback($response)) {
                Log::warning('Primary SMS provider failed, trying fallback', [
                    'primary_provider' => $this->defaultProvider,
                    'fallback_provider' => $this->fallbackProvider,
                    'error' => $response->getErrorMessage(),
                ]);

                return $this->via($this->fallbackProvider)->send($to, $message, $from);
            }

            return $response;
        } catch (Throwable $e) {
            // Try fallback on exception
            if ($this->fallbackProvider) {
                Log::warning('Primary SMS provider threw exception, trying fallback', [
                    'primary_provider' => $this->defaultProvider,
                    'fallback_provider' => $this->fallbackProvider,
                    'exception' => $e->getMessage(),
                ]);

                try {
                    return $this->via($this->fallbackProvider)->send($to, $message, $from);
                } catch (Throwable $fallbackException) {
                    Log::error('Both primary and fallback SMS providers failed', [
                        'primary_error' => $e->getMessage(),
                        'fallback_error' => $fallbackException->getMessage(),
                    ]);
                }
            }

            throw $e;
        }
    }

    /**
     * Determine if fallback should be used based on response
     */
    protected function shouldUseFallback(TextifyResponse $response): bool
    {
        // Use fallback for certain error codes or conditions
        $fallbackErrorCodes = [
            'INSUFFICIENT_BALANCE',
            'RATE_LIMIT_EXCEEDED',
            'PROVIDER_ERROR',
            'NETWORK_ERROR',
        ];

        return in_array($response->getErrorCode(), $fallbackErrorCodes, true);
    }

    /**
     * Get all registered providers
     */
    public function getProviders(): array
    {
        return array_keys($this->providers);
    }

    /**
     * Check if provider exists
     */
    public function hasProvider(string $name): bool
    {
        return isset($this->providers[$name]);
    }

    /**
     * Get provider instance
     */
    public function getProvider(string $name): TextifyProviderInterface
    {
        return $this->getDriver($name);
    }

    /**
     * Get account balance from the default or specified provider
     */
    public function getBalance(?string $provider = null): float
    {
        $driverName = $provider ?? $this->defaultProvider;

        if (! $driverName) {
            throw new TextifyException('No provider specified and no default provider configured');
        }

        try {
            $driver = $this->getDriver($driverName);

            return $driver->getBalance();
        } catch (Exception $e) {
            throw new TextifyException(
                "Failed to get balance from provider '{$driverName}': ".$e->getMessage(),
                0,
                $e
            );
        }
    }
}
