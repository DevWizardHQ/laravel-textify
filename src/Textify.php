<?php

declare(strict_types=1);

namespace DevWizard\Textify;

use DevWizard\Textify\Contracts\TextifyManagerInterface;
use DevWizard\Textify\Contracts\TextifyProviderInterface;

/**
 * Laravel Textify - Main SMS Facade Class
 *
 * This class serves as the main entry point for the Laravel Textify package,
 * providing a clean and simple interface for sending SMS messages through
 * multiple providers with support for fallbacks, queues, and activity tracking.
 *
 * The class delegates all functionality to the TextifyManagerInterface implementation,
 * acting as a facade that simplifies the API surface for end users.
 *
 * Features:
 * - Simple SMS sending with fluent interface
 * - Multi-provider support with automatic fallbacks
 * - Queue integration for background processing
 * - Comprehensive activity tracking and logging
 * - Phone number validation and formatting
 * - Real-time balance and delivery status checking
 */
class Textify
{
    protected TextifyManagerInterface $textify;

    public function __construct(TextifyManagerInterface $textify)
    {
        $this->textify = $textify;
    }

    /**
     * Unified send method that can handle:
     * 1. Single SMS: send('01712345678', 'message')
     * 2. Same message to multiple numbers: send(['01712345678', '01812345678'], 'message')
     * 3. Different messages to different numbers: send([['to' => '01712345678', 'message' => 'msg1'], ['to' => '01812345678', 'message' => 'msg2']])
     * 4. Pre-prepared contacts: send() // if contacts were prepared beforehand
     */
    public function send(string|array|null $to = null, ?string $message = null, ?string $from = null): mixed
    {
        return $this->textify->send($to, $message, $from);
    }

    /**
     * Prepare contacts for later sending
     */
    public function to(string|array $contacts): TextifyManagerInterface
    {
        return $this->textify->to($contacts);
    }

    /**
     * Set message content
     */
    public function message(string $message): TextifyManagerInterface
    {
        return $this->textify->message($message);
    }

    /**
     * Set sender ID
     */
    public function from(string $from): TextifyManagerInterface
    {
        return $this->textify->from($from);
    }

    /**
     * Send SMS using a specific driver
     */
    public function via(string $driver): TextifyManagerInterface
    {
        return $this->textify->via($driver);
    }

    /**
     * Queue SMS for later sending
     */
    public function queue(?string $queueName = null): mixed
    {
        return $this->textify->queue($queueName);
    }

    /**
     * Set fallback driver
     */
    public function fallback(string $driver): TextifyManagerInterface
    {
        return $this->textify->fallback($driver);
    }

    /**
     * Select SMS provider (alias for via)
     */
    public function driver(string $driver): TextifyManagerInterface
    {
        return $this->textify->driver($driver);
    }

    /**
     * Get account balance from provider
     */
    public function getBalance(?string $provider = null): float
    {
        return $this->textify->getBalance($provider);
    }

    /**
     * Get all registered providers
     */
    public function getProviders(): array
    {
        return $this->textify->getProviders();
    }

    /**
     * Check if provider exists
     */
    public function hasProvider(string $name): bool
    {
        return $this->textify->hasProvider($name);
    }

    /**
     * Get provider instance
     */
    public function getProvider(string $name): TextifyProviderInterface
    {
        return $this->textify->getProvider($name);
    }

    /**
     * Reset prepared data
     */
    public function reset(): TextifyManagerInterface
    {
        return $this->textify->reset();
    }

    /**
     * Get the SMS manager instance
     */
    public function manager(): TextifyManagerInterface
    {
        return $this->textify;
    }

    /**
     * Forward calls to the manager
     */
    public function __call(string $method, array $parameters)
    {
        return $this->textify->{$method}(...$parameters);
    }
}
