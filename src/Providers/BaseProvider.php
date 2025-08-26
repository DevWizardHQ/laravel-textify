<?php

declare(strict_types=1);

namespace DevWizard\Textify\Providers;

use DevWizard\Textify\Contracts\ActivityTrackerInterface;
use DevWizard\Textify\Contracts\TextifyProviderInterface;
use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Events\TextifyFailed;
use DevWizard\Textify\Events\TextifySending;
use DevWizard\Textify\Events\TextifySent;
use DevWizard\Textify\Exceptions\TextifyException;
use DevWizard\Textify\Factories\ActivityTrackerFactory;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * BaseProvider - Abstract base class for all SMS providers
 *
 * This class provides the foundation for all SMS providers in the Laravel Textify package.
 * It handles common functionality like HTTP client setup, activity tracking, event dispatching,
 * logging, and error handling. All concrete providers should extend this class.
 *
 * Key features:
 * - HTTP client management with GuzzleHTTP
 * - Automatic activity tracking
 * - Event dispatching for SMS lifecycle
 * - Comprehensive error handling
 * - Phone number validation and formatting
 * - Configuration validation
 * - Logging integration
 */
abstract class BaseProvider implements TextifyProviderInterface
{
    /**
     * HTTP client for API requests
     */
    protected Client $client;

    /**
     * Provider configuration
     */
    protected array $config;

    /**
     * List of supported country codes
     *
     * @var array<string>
     */
    protected array $supportedCountries = [];

    /**
     * Activity tracker instance
     */
    protected ActivityTrackerInterface $activityTracker;

    /**
     * Create a new provider instance
     *
     * Initializes the HTTP client, activity tracker, and validates configuration.
     *
     * @param  array  $config  Provider configuration
     *
     * @throws TextifyException If configuration is invalid
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->client = new Client($this->getClientConfig());
        $this->activityTracker = $this->createActivityTracker();
        $this->validateConfig();
    }

    /**
     * Create activity tracker based on configuration
     *
     * Creates the appropriate activity tracker based on the global configuration.
     * Returns a null tracker if activity tracking is disabled.
     */
    protected function createActivityTracker(): ActivityTrackerInterface
    {
        $enabled = config('textify.activity_tracking.enabled', true);
        $driver = config('textify.activity_tracking.driver', 'database');

        if (! $enabled) {
            $driver = 'null';
        }

        return ActivityTrackerFactory::create($driver);
    }

    /**
     * Get HTTP client configuration
     */
    protected function getClientConfig(): array
    {
        return [
            'timeout' => $this->config['timeout'] ?? 30,
            'verify' => $this->config['verify_ssl'] ?? false,
        ];
    }

    /**
     * Validate provider configuration
     */
    abstract protected function validateConfig(): void;

    /**
     * Send HTTP request to provider API
     */
    abstract protected function sendRequest(TextifyMessage $message): array;

    /**
     * Parse provider response
     */
    abstract protected function parseResponse(array $response): TextifyResponse;

    /**
     * {@inheritdoc}
     */
    public function send(TextifyMessage $message): TextifyResponse
    {
        try {
            // Validate phone number
            if (! $this->validatePhoneNumber($message->to)) {
                return TextifyResponse::failed(
                    errorMessage: 'Invalid phone number format',
                    errorCode: 'INVALID_PHONE_NUMBER'
                );
            }

            // Format phone number
            $formattedMessage = new TextifyMessage(
                to: $this->formatPhoneNumber($message->to),
                message: $message->message,
                from: $message->from,
                metadata: $message->metadata
            );

            // Dispatch sending event
            event(new TextifySending($formattedMessage, $this->getName()));

            // Track sending attempt
            $this->activityTracker->trackSending($formattedMessage, $this->getName());

            // Log for debugging (if enabled)
            if (config('textify.logging.enabled', true)) {
                Log::info('Textify sending attempt', [
                    'provider' => $this->getName(),
                    'to' => $formattedMessage->getTo(),
                    'message_id' => $formattedMessage->getId(),
                ]);
            }

            // Send request
            $rawResponse = $this->sendRequest($formattedMessage);

            // Parse response
            $response = $this->parseResponse($rawResponse);

            // Track and log the result
            if ($response->isSuccessful()) {
                $this->activityTracker->trackSent($formattedMessage, $response, $this->getName());

                if (config('textify.logging.log_successful', true)) {
                    Log::info('Textify sent successfully', [
                        'provider' => $this->getName(),
                        'message_id' => $formattedMessage->getId(),
                        'provider_message_id' => $response->getMessageId(),
                    ]);
                }

                event(new TextifySent($formattedMessage, $response, $this->getName()));
            } else {
                $this->activityTracker->trackFailed($formattedMessage, $response, $this->getName());

                if (config('textify.logging.log_failed', true)) {
                    Log::error('Textify sending failed', [
                        'provider' => $this->getName(),
                        'message_id' => $formattedMessage->getId(),
                        'error_code' => $response->getErrorCode(),
                        'error_message' => $response->getErrorMessage(),
                    ]);
                }

                event(new TextifyFailed($formattedMessage, $response, $this->getName()));
            }

            return $response;
        } catch (\Throwable $e) {
            $response = TextifyResponse::failed(
                errorMessage: $e->getMessage(),
                errorCode: 'PROVIDER_ERROR'
            );

            // Track failed activity
            $this->activityTracker->trackFailed($message, $response, $this->getName());

            // Log exception for debugging
            Log::error('Textify sending failed with exception', [
                'provider' => $this->getName(),
                'to' => $message->to,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            event(new TextifyFailed($message, $response, $this->getName(), $e));

            return $response;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sendBulk(array $messages): array
    {
        $responses = [];

        foreach ($messages as $message) {
            if ($message instanceof TextifyMessage) {
                $responses[] = $this->send($message);
            } else {
                $responses[] = TextifyResponse::failed(
                    errorMessage: 'Invalid message format',
                    errorCode: 'INVALID_MESSAGE_FORMAT'
                );
            }
        }

        return $responses;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCountry(string $countryCode): bool
    {
        if (empty($this->supportedCountries)) {
            return true; // Support all countries if not specified
        }

        return in_array(strtoupper($countryCode), $this->supportedCountries, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
        $this->validateConfig();
    }

    /**
     * Get required config keys
     */
    abstract protected function getRequiredConfigKeys(): array;

    /**
     * Check if required config keys are present
     */
    protected function ensureConfigKeys(): void
    {
        $requiredKeys = $this->getRequiredConfigKeys();
        $missingKeys = [];

        foreach ($requiredKeys as $key) {
            if (! isset($this->config[$key]) || empty($this->config[$key])) {
                $missingKeys[] = $key;
            }
        }

        if (! empty($missingKeys)) {
            throw new TextifyException(
                sprintf(
                    'Missing required configuration keys for %s provider: %s',
                    $this->getName(),
                    implode(', ', $missingKeys)
                )
            );
        }
    }
}
