<?php

declare(strict_types=1);

namespace DevWizard\Textify\Providers\Bangladeshi;

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Providers\BaseProvider;
use GuzzleHttp\Exception\GuzzleException;

class DhorolaSmsProvider extends BaseProvider
{
    protected array $supportedCountries = ['BD'];

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'dhorola';
    }

    /**
     * {@inheritdoc}
     */
    protected function getClientConfig(): array
    {
        return [
            'base_uri' => $this->config['base_uri'] ?? 'https://api.dhorolasms.net',
            'timeout' => $this->config['timeout'] ?? 30,
            'verify' => $this->config['verify_ssl'] ?? true,
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
                'apikey' => $this->config['api_key'],
                'sender' => $message->getFrom() ?: $this->config['sender_id'],
                'msisdn' => $message->getTo(),
                'smstext' => $message->getMessage(),
            ];

            // Use GET method as shown in documentation
            $response = $this->client->get('/smsapiv3', [
                'query' => $params,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true) ?: [];
        } catch (GuzzleException $e) {
            throw new \Exception('Dhorola SMS API request failed: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function parseResponse(array $response): TextifyResponse
    {
        // Parse Dhorola SMS response format
        if (isset($response['response']) && is_array($response['response'])) {
            $firstResponse = $response['response'][0] ?? null;

            if ($firstResponse && isset($firstResponse['status']) && $firstResponse['status'] === 0) {
                return TextifyResponse::success(
                    messageId: (string) ($firstResponse['id'] ?? uniqid('dhorola_')),
                    status: 'sent',
                    rawResponse: $response
                );
            }

            // Handle error response with status codes
            if ($firstResponse && isset($firstResponse['status'])) {
                $errorCode = (string) $firstResponse['status'];
                $errorMessage = $this->getErrorMessage($errorCode);

                return TextifyResponse::failed(
                    errorMessage: $errorMessage,
                    errorCode: $errorCode,
                    rawResponse: $response
                );
            }
        }

        return TextifyResponse::failed(
            errorMessage: 'Unknown error occurred',
            errorCode: 'UNKNOWN_ERROR',
            rawResponse: $response
        );
    }

    /**
     * Map error codes to messages based on documentation
     */
    private function getErrorMessage(string $code): string
    {
        $errorMessages = [
            '101' => 'Message length error',
            '102' => 'Invalid sender',
            '103' => 'Authentication Failure',
            '104' => 'User Invalid',
            '105' => 'Wrong MSISDN',
            '106' => 'Wrong API key',
            '107' => 'User Suspended',
            '108' => 'Ip Is Not Allow',
            '109' => 'Api Is Not Allow',
            '1000' => 'Low balance',
            '2300' => 'Destination Route Error',
            '3300' => 'Internal Error',
            '2000' => 'Destination provider not available',
            '3000' => 'Destination provider not available',
            '4000' => 'Destination provider not available',
        ];

        return $errorMessages[$code] ?? "Error code: {$code}";
    }

    /**
     * {@inheritdoc}
     */
    public function getDeliveryStatus(string $messageId): string
    {
        // Dhorola SMS documentation doesn't specify a delivery status endpoint
        // Return 'unknown' as this feature may not be available
        return 'unknown';
    }

    /**
     * {@inheritdoc}
     */
    public function getBalance(): float
    {
        try {
            $response = $this->client->get('/getbalancev3', [
                'query' => [
                    'apikey' => $this->config['api_key'],
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            // Response format: {"response": "xxxx.xx"}
            if (isset($data['response']) && is_string($data['response'])) {
                return (float) $data['response'];
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

        // Bangladesh phone number validation for Dhorola SMS format
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

        // Convert to Dhorola SMS format (88019XXXXXXXX - international format)
        if (strlen($cleaned) === 11 && str_starts_with($cleaned, '01')) {
            return '88'.$cleaned; // Convert 01XXXXXXXXX to 8801XXXXXXXXX
        }

        if (strlen($cleaned) === 13 && str_starts_with($cleaned, '880')) {
            return $cleaned; // Already in correct format 8801XXXXXXXXX
        }

        if (strlen($cleaned) === 14 && str_starts_with($cleaned, '8801')) {
            return $cleaned; // Already in correct format 8801XXXXXXXXX
        }

        return $phoneNumber; // Return original if can't format
    }
}
