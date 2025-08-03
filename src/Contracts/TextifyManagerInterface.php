<?php

declare(strict_types=1);

namespace DevWizard\Textify\Contracts;

interface TextifyManagerInterface
{
    /**
     * Unified send method that can handle:
     * 1. Single SMS: send('01712345678', 'message')
     * 2. Same message to multiple numbers: send(['01712345678', '01812345678'], 'message')
     * 3. Different messages to different numbers: send([['to' => '01712345678', 'message' => 'msg1'], ['to' => '01812345678', 'message' => 'msg2']])
     * 4. Pre-prepared contacts: send() // if contacts were prepared beforehand
     */
    public function send(string|array|null $to = null, ?string $message = null, ?string $from = null): mixed;

    /**
     * Prepare contacts for later sending
     */
    public function to(string|array $contacts): self;

    /**
     * Set message content
     */
    public function message(string $message): self;

    /**
     * Set sender ID
     */
    public function from(string $from): self;

    /**
     * Send SMS using a specific driver
     */
    public function via(string $driver): self;

    /**
     * Alias for via() method for Laravel Manager pattern compatibility
     */
    public function driver(string $driver): self;

    /**
     * Queue SMS for later sending
     */
    public function queue(?string $queueName = null): mixed;

    /**
     * Get the current driver instance
     */
    public function getDriver(?string $name = null): TextifyProviderInterface;

    /**
     * Set fallback driver
     */
    public function fallback(string $driver): self;

    /**
     * Reset prepared data
     */
    public function reset(): self;

    /**
     * Get account balance from the default or specified provider
     */
    public function getBalance(?string $provider = null): float;

    /**
     * Get all registered providers
     */
    public function getProviders(): array;

    /**
     * Check if provider exists
     */
    public function hasProvider(string $name): bool;

    /**
     * Get provider instance
     */
    public function getProvider(string $name): TextifyProviderInterface;
}
