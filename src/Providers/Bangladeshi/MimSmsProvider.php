<?php

declare(strict_types=1);

namespace DevWizard\Textify\Providers\Bangladeshi;

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Providers\BaseProvider;
use GuzzleHttp\Exception\GuzzleException;

class MimSmsProvider extends BaseProvider
{
    protected string $name = 'mimsms';

    protected array $supportedCountries = ['BD'];

    protected function getClientConfig(): array
    {
        return [
            'base_uri' => $this->config['base_uri'] ?? 'https://api.mimsms.com',
            'timeout' => $this->config['timeout'] ?? 30,
            'verify' => $this->config['verify_ssl'] ?? true,
        ];
    }

    protected function getRequiredConfigKeys(): array
    {
        return ['username', 'apikey'];
    }

    protected function validateConfig(): void
    {
        $this->ensureConfigKeys();
    }

    protected function sendRequest(TextifyMessage $message): array
    {
        $payload = [
            'UserName' => $this->config['username'],
            'Apikey' => $this->config['apikey'],
            'MobileNumber' => $this->formatPhoneNumber($message->getTo()),
            'SenderName' => $message->getFrom() ?? $this->config['sender_id'] ?? 'MiMSMS',
            'TransactionType' => $this->config['transaction_type'] ?? 'T', // T=Transactional, P=Promotional, D=Dynamic
            'Message' => $message->getMessage(),
        ];

        // Add CampaignId for promotional messages
        if (($this->config['transaction_type'] ?? 'T') === 'P' && isset($this->config['campaign_id'])) {
            $payload['CampaignId'] = $this->config['campaign_id'];
        } else {
            $payload['CampaignId'] = 'null';
        }

        $response = $this->client->post('/api/SmsSending/SMS', [
            'json' => $payload,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function parseResponse(array $response): TextifyResponse
    {
        if (
            isset($response['statusCode']) && $response['statusCode'] === '200' &&
            isset($response['status']) && $response['status'] === 'Success'
        ) {
            return TextifyResponse::success(
                messageId: $response['trxnId'] ?? uniqid('mimsms_'),
                status: 'sent',
                cost: null, // MiMSMS doesn't return cost in response
                rawResponse: [
                    'provider' => $this->name,
                    'response' => $response,
                ]
            );
        }

        return TextifyResponse::failed(
            errorCode: $response['statusCode'] ?? 'unknown',
            errorMessage: $response['responseResult'] ?? $response['status'] ?? 'Unknown error occurred',
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
        // MiMSMS documentation doesn't specify a delivery status endpoint
        // The trxnId can be used for checking logs but no specific endpoint is documented
        return 'unknown';
    }

    public function getBalance(): float
    {
        try {
            $payload = [
                'UserName' => $this->config['username'],
                'Apikey' => $this->config['apikey'],
            ];

            $response = $this->client->post('/api/SmsSending/balanceCheck', [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if (
                $response->getStatusCode() === 200 &&
                isset($body['statusCode']) && $body['statusCode'] === '200' &&
                isset($body['responseResult'])
            ) {
                return (float) $body['responseResult'];
            }

            return 0.0;
        } catch (GuzzleException $e) {
            return 0.0;
        }
    }

    public function validatePhoneNumber(string $phoneNumber): bool
    {
        // Bangladesh mobile number validation for international format
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);

        // MiMSMS expects international format: 8801XXXXXXXXX (without +)
        return preg_match('/^8801[3-9]\d{8}$/', $phoneNumber) === 1;
    }

    public function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove non-digits and + sign
        $phoneNumber = preg_replace('/[^\d]/', '', $phoneNumber);

        // Handle different formats - convert to international format without +
        if (str_starts_with($phoneNumber, '8801')) {
            return $phoneNumber; // Already in correct format (8801XXXXXXXXX)
        }

        if (str_starts_with($phoneNumber, '01')) {
            return '880'.substr($phoneNumber, 1); // Convert 01XXXXXXXXX to 8801XXXXXXXXX (remove the 0, add 880)
        }

        // Assume it's a local number (11 digits starting with 1)
        if (strlen($phoneNumber) === 11 && str_starts_with($phoneNumber, '1')) {
            return '880'.$phoneNumber; // Convert 1XXXXXXXXX to 8801XXXXXXXXX
        }

        return $phoneNumber;
    }
}
