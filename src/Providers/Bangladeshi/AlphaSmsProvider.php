<?php

declare(strict_types=1);

namespace DevWizard\Textify\Providers\Bangladeshi;

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Providers\BaseProvider;
use GuzzleHttp\Exception\GuzzleException;

class AlphaSmsProvider extends BaseProvider
{
    protected string $name = 'alphasms';

    protected array $supportedCountries = ['BD'];

    protected function getClientConfig(): array
    {
        return [
            'base_uri' => $this->config['base_uri'] ?? 'https://api.sms.net.bd',
            'timeout' => $this->config['timeout'] ?? 30,
            'verify' => $this->config['verify_ssl'] ?? false,
        ];
    }

    protected function getRequiredConfigKeys(): array
    {
        return ['api_key'];
    }

    protected function validateConfig(): void
    {
        $this->ensureConfigKeys();
    }

    protected function sendRequest(TextifyMessage $message): array
    {
        $payload = [
            'api_key' => $this->config['api_key'],
            'to' => $message->getTo(),
            'msg' => $message->getMessage(),
        ];

        // Add sender_id only if provided
        if ($message->getFrom() || ! empty($this->config['sender_id'])) {
            $payload['sender_id'] = $message->getFrom() ?? $this->config['sender_id'];
        }

        $response = $this->client->post('/sendsms', [
            'form_params' => $payload,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function parseResponse(array $response): TextifyResponse
    {
        // Check if the request was successful (error = 0 means success in Alpha SMS)
        if (isset($response['error']) && (int) $response['error'] === 0) {
            return TextifyResponse::success(
                messageId: (string) ($response['data']['request_id'] ?? uniqid('alphasms_')),
                status: 'sent',
                cost: null, // Alpha SMS doesn't return cost in send response
                rawResponse: [
                    'provider' => $this->name,
                    'response' => $response,
                ]
            );
        }

        return TextifyResponse::failed(
            errorCode: (string) ($response['error'] ?? 'unknown'),
            errorMessage: $response['msg'] ?? 'Unknown error occurred',
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
            $response = $this->client->get("/report/request/{$messageId}/", [
                'query' => [
                    'api_key' => $this->config['api_key'],
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() === 200 && isset($body['error']) && (int) $body['error'] === 0) {
                // Return overall request status
                if (isset($body['data']['request_status'])) {
                    return strtolower($body['data']['request_status']);
                }

                // If recipients data exists, return first recipient status
                if (isset($body['data']['recipients'][0]['status'])) {
                    return strtolower($body['data']['recipients'][0]['status']);
                }
            }

            return 'unknown';
        } catch (GuzzleException $e) {
            return 'unknown';
        }
    }

    public function getBalance(): float
    {
        try {
            $response = $this->client->get('/user/balance/', [
                'query' => [
                    'api_key' => $this->config['api_key'],
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() === 200 && isset($body['error']) && (int) $body['error'] === 0) {
                return (float) ($body['data']['balance'] ?? 0.0);
            }

            return 0.0;
        } catch (GuzzleException $e) {
            return 0.0;
        }
    }

    public function validatePhoneNumber(string $phoneNumber): bool
    {
        // Bangladesh mobile number validation - Alpha SMS accepts both formats
        $phoneNumber = preg_replace('/[^\d]/', '', $phoneNumber);

        // Check for 01XXXXXXXXX format (11 digits)
        if (preg_match('/^01[3-9]\d{8}$/', $phoneNumber)) {
            return true;
        }

        // Check for 8801XXXXXXXXX format (13 digits)
        if (preg_match('/^8801[3-9]\d{8}$/', $phoneNumber)) {
            return true;
        }

        return false;
    }

    public function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove non-digits
        $phoneNumber = preg_replace('/[^\d]/', '', $phoneNumber);

        // Handle different formats - Alpha SMS accepts both 880XXXXXXXXXX and 01XXXXXXXXX
        if (str_starts_with($phoneNumber, '8801')) {
            return $phoneNumber; // Keep international format
        }

        if (str_starts_with($phoneNumber, '01')) {
            return $phoneNumber; // Keep local format
        }

        // Assume it's a local number (11 digits starting with 1)
        if (strlen($phoneNumber) === 11 && str_starts_with($phoneNumber, '1')) {
            return '0'.$phoneNumber;
        }

        return $phoneNumber;
    }
}
