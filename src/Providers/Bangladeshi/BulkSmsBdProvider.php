<?php

declare(strict_types=1);

namespace DevWizard\Textify\Providers\Bangladeshi;

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Providers\BaseProvider;

class BulkSmsBdProvider extends BaseProvider
{
    protected array $supportedCountries = ['BD'];

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'bulksmsbd';
    }

    /**
     * {@inheritdoc}
     */
    protected function getClientConfig(): array
    {
        return [
            'base_uri' => $this->config['base_uri'] ?? 'http://bulksmsbd.net',
            'timeout' => $this->config['timeout'] ?? 30,
            'verify' => $this->config['verify_ssl'] ?? false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function validateConfig(): void
    {
        $this->ensureConfigKeys();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredConfigKeys(): array
    {
        return ['api_key', 'sender_id'];
    }

    /**
     * {@inheritdoc}
     */
    protected function sendRequest(TextifyMessage $message): array
    {
        try {
            $params = [
                'api_key' => $this->config['api_key'],
                'type' => 'text',
                'number' => $message->getTo(),
                'senderid' => $message->getFrom() ?: $this->config['sender_id'],
                'message' => $message->getMessage(),
            ];

            // Use GET method as documentation shows it supports both GET and POST
            $response = $this->client->get('/api/smsapi', [
                'query' => $params,
                'headers' => [
                    'Accept' => 'text/plain,application/json',
                ],
            ]);

            $responseBody = $response->getBody()->getContents();

            // Try to decode as JSON first, fallback to plain text
            $decodedResponse = json_decode($responseBody, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decodedResponse;
            }

            // If not JSON, treat as plain text response
            return ['response' => trim($responseBody)];
        } catch (\Exception $e) {
            throw new \Exception('BulkSMSBD API request failed: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function parseResponse(array $response): TextifyResponse
    {
        // Parse BulkSMSBD response format
        // Check if it's a plain text response
        if (isset($response['response'])) {
            $responseText = $response['response'];

            // BulkSMSBD returns success code 202 for successful SMS
            if (str_contains($responseText, '202') || str_contains(strtolower($responseText), 'success')) {
                return TextifyResponse::success(
                    messageId: uniqid('bulksmsbd_'),
                    status: 'sent',
                    rawResponse: $response
                );
            }

            // Check for specific error codes from documentation
            $errorCode = null;
            $errorMessage = $responseText;

            // Extract error code if present
            if (preg_match('/(\d{4})/', $responseText, $matches)) {
                $errorCode = $matches[1];
                // Map error codes to messages based on documentation
                $errorMessages = [
                    '1002' => 'Sender id not correct/sender id is disabled',
                    '1003' => 'Please Required all fields/Contact Your System Administrator',
                    '1005' => 'Internal Error',
                    '1006' => 'Balance Validity Not Available',
                    '1007' => 'Balance Insufficient',
                    '1011' => 'User Id not found',
                    // Add more error codes as needed
                ];
                $errorMessage = $errorMessages[$errorCode] ?? $responseText;
            }

            return TextifyResponse::failed(
                errorMessage: $errorMessage,
                errorCode: $errorCode ?? 'UNKNOWN_ERROR',
                rawResponse: $response
            );
        }

        // Fallback for JSON response format
        if (isset($response['status_code']) && $response['status_code'] == 202) {
            return TextifyResponse::success(
                messageId: $response['message_id'] ?? uniqid('bulksmsbd_'),
                status: 'sent',
                rawResponse: $response
            );
        }

        return TextifyResponse::failed(
            errorMessage: $response['error'] ?? $response['message'] ?? 'Unknown error occurred',
            errorCode: (string) ($response['status_code'] ?? $response['code'] ?? 'UNKNOWN_ERROR'),
            rawResponse: $response
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDeliveryStatus(string $messageId): string
    {
        // BulkSMSBD documentation doesn't specify a delivery status endpoint
        // Return 'unknown' as this feature may not be available
        return 'unknown';
    }

    /**
     * {@inheritdoc}
     */
    public function getBalance(): float
    {
        try {
            $response = $this->client->get('/api/getBalanceApi', [
                'query' => [
                    'api_key' => $this->config['api_key'],
                ],
            ]);

            $responseBody = $response->getBody()->getContents();
            // Try to decode as JSON first
            $data = json_decode($responseBody, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($data['balance'])) {
                return (float) $data['balance'];
            }

            // If plain text response, try to extract balance number
            if (preg_match('/(\d+(?:\.\d+)?)/', $responseBody, $matches)) {
                return (float) $matches[1];
            }

            return 0.0;
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        // Remove any non-digit characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Bangladesh phone number validation for BulkSMSBD format
        // Accepts: 01XXXXXXXXX (11 digits) or 8801XXXXXXXXX (13 digits)
        if (strlen($cleaned) === 11 && str_starts_with($cleaned, '01')) {
            return preg_match('/^01[3-9]\d{8}$/', $cleaned) === 1;
        }

        if (strlen($cleaned) === 13 && str_starts_with($cleaned, '880')) {
            return preg_match('/^8801[3-9]\d{8}$/', $cleaned) === 1;
        }

        if (strlen($cleaned) === 14 && str_starts_with($cleaned, '8801')) {
            return preg_match('/^8801[3-9]\d{8}$/', $cleaned) === 1;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-digit characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Convert to BulkSMSBD format (8801XXXXXXXXX - without plus sign)
        if (strlen($cleaned) === 11 && str_starts_with($cleaned, '01')) {
            return '88'.$cleaned; // Convert 01XXXXXXXXX to 8801XXXXXXXXX
        }

        if (strlen($cleaned) === 13 && str_starts_with($cleaned, '880')) {
            return $cleaned; // Already in correct format 8801XXXXXXXXX
        }

        if (strlen($cleaned) === 14 && str_starts_with($cleaned, '8801')) {
            return $cleaned; // Already in correct format 8801XXXXXXXXX
        }

        // If it starts with +88, remove the plus
        if (str_starts_with($cleaned, '88')) {
            return $cleaned;
        }

        return $phoneNumber; // Return original if can't format
    }
}
