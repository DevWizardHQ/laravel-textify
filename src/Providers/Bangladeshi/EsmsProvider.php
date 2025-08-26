<?php

declare(strict_types=1);

namespace DevWizard\Textify\Providers\Bangladeshi;

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Providers\BaseProvider;
use GuzzleHttp\Exception\GuzzleException;

class EsmsProvider extends BaseProvider
{
    protected string $name = 'esms';

    protected array $supportedCountries = ['BD'];

    protected function getClientConfig(): array
    {
        return [
            'base_uri' => $this->config['base_uri'] ?? 'https://login.esms.com.bd',
            'timeout' => $this->config['timeout'] ?? 30,
            'verify' => $this->config['verify_ssl'] ?? false,
        ];
    }

    protected function getRequiredConfigKeys(): array
    {
        return ['api_token', 'sender_id'];
    }

    protected function validateConfig(): void
    {
        $this->ensureConfigKeys();
    }

    protected function sendRequest(TextifyMessage $message): array
    {
        $payload = [
            'recipient' => $message->getTo(),
            'sender_id' => $message->getFrom() ?? $this->config['sender_id'] ?? 'eSMS',
            'type' => 'plain',
            'message' => $message->getMessage(),
        ];

        $response = $this->client->post('/api/v3/sms/send', [
            'form_params' => $payload,
            'headers' => [
                'Authorization' => 'Bearer '.$this->config['api_token'],
                'Accept' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function parseResponse(array $response): TextifyResponse
    {
        if (isset($response['status']) && $response['status'] === 'success') {
            return TextifyResponse::success(
                messageId: $response['data']['message_id'] ?? $response['data']['uid'] ?? uniqid('esms_'),
                status: 'sent',
                cost: $response['data']['cost'] ?? null,
                rawResponse: [
                    'provider' => $this->name,
                    'response' => $response,
                ]
            );
        }

        return TextifyResponse::failed(
            errorCode: $response['error_code'] ?? 'unknown',
            errorMessage: $response['message'] ?? 'Unknown error occurred',
            rawResponse: [
                'provider' => $this->name,
                'response' => $response,
            ]
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDeliveryStatus(string $messageId): string
    {
        try {
            $response = $this->client->get("/api/v3/sms/{$messageId}", [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->config['api_token'],
                    'Accept' => 'application/json',
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() === 200 && isset($body['status']) && $body['status'] === 'success') {
                return $body['data']['status'] ?? 'unknown';
            }

            return 'unknown';
        } catch (GuzzleException $e) {
            return 'unknown';
        }
    }

    public function getBalance(): float
    {
        // eSMS API documentation doesn't specify a balance endpoint
        // Return 0.0 as balance checking is not available
        return 0.0;
    }

    public function validatePhoneNumber(string $phoneNumber): bool
    {
        // Bangladesh mobile number validation
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);

        return preg_match('/^01[3-9]\d{8}$/', $phoneNumber) === 1;
    }

    public function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove non-digits
        $phoneNumber = preg_replace('/[^\d]/', '', $phoneNumber);

        // Handle different formats
        if (str_starts_with($phoneNumber, '8801')) {
            return '0'.substr($phoneNumber, 3); // Remove 880 and add 0
        }

        if (str_starts_with($phoneNumber, '01')) {
            return $phoneNumber; // Already in correct format
        }

        // Assume it's a local number (11 digits starting with 1)
        if (strlen($phoneNumber) === 11 && str_starts_with($phoneNumber, '1')) {
            return '0'.$phoneNumber;
        }

        return $phoneNumber;
    }
}
