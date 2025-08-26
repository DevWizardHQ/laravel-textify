<?php

declare(strict_types=1);

namespace DevWizard\Textify\Providers\Bangladeshi;

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Providers\BaseProvider;
use GuzzleHttp\Exception\GuzzleException;

class ReveSmsProvider extends BaseProvider
{
    protected string $name = 'revesms';

    protected array $supportedCountries = ['BD'];

    protected function getClientConfig(): array
    {
        return [
            'base_uri' => $this->config['base_uri'] ?? 'https://smpp.revesms.com:7790',
            'timeout' => $this->config['timeout'] ?? 30,
            'verify' => $this->config['verify_ssl'] ?? false,
        ];
    }

    protected function getRequiredConfigKeys(): array
    {
        return ['apikey', 'secretkey', 'client_id'];
    }

    protected function validateConfig(): void
    {
        $this->ensureConfigKeys();
    }

    protected function sendRequest(TextifyMessage $message): array
    {
        $query = [
            'apikey' => $this->config['apikey'],
            'secretkey' => $this->config['secretkey'],
            'callerID' => $message->getFrom() ?? $this->config['sender_id'] ?? 'REVESMS',
            'toUser' => $this->formatPhoneNumber($message->getTo()),
            'messageContent' => $message->getMessage(),
        ];

        $response = $this->client->get('/sendtext', [
            'query' => $query,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function parseResponse(array $response): TextifyResponse
    {
        // REVE SMS returns Status "0" for success
        if (isset($response['Status']) && $response['Status'] === '0') {
            return TextifyResponse::success(
                messageId: $response['Message_ID'] ?? uniqid('revesms_'),
                status: 'sent',
                cost: null, // REVE SMS doesn't return cost in response
                rawResponse: [
                    'provider' => $this->name,
                    'response' => $response,
                ]
            );
        }

        // Map REVE SMS error codes to readable messages
        $errorMessages = [
            '109' => 'User not provided/Deleted',
            '108' => 'Wrong password/not provided',
            '114' => 'Content not provided',
            '101' => 'Internal server error',
            '1' => 'Request failed',
            '-42' => 'Authorization failed',
        ];

        $errorCode = $response['Status'] ?? 'unknown';
        $errorMessage = $errorMessages[$errorCode] ?? $response['Text'] ?? 'Unknown error occurred';

        return TextifyResponse::failed(
            errorCode: $errorCode,
            errorMessage: $errorMessage,
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
            $response = $this->client->get('/getstatus', [
                'query' => [
                    'apikey' => $this->config['apikey'],
                    'secretkey' => $this->config['secretkey'],
                    'messageid' => $messageId,
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() === 200 && isset($body['Status'])) {
                // Map REVE SMS delivery status codes
                $statusMap = [
                    '0' => 'delivered',
                    '2' => 'pending',
                    '4' => 'sent',
                    '1' => 'failed',
                    '109' => 'failed',
                    '108' => 'failed',
                    '114' => 'failed',
                    '101' => 'failed',
                    '-42' => 'failed',
                ];

                return $statusMap[$body['Status']] ?? 'unknown';
            }

            return 'unknown';
        } catch (GuzzleException $e) {
            return 'unknown';
        }
    }

    public function getBalance(): float
    {
        try {
            // Get balance URI from config or use default
            $balanceUri = $this->config['balance_uri'] ?? 'https://smpp.revesms.com';

            // Create a separate client for balance API if needed
            $balanceClient = new \GuzzleHttp\Client([
                'base_uri' => $balanceUri,
                'timeout' => $this->config['timeout'] ?? 30,
                'verify' => $this->config['verify_ssl'] ?? false,
            ]);

            $response = $balanceClient->get('/sms/smsConfiguration/smsClientBalance.jsp', [
                'query' => [
                    'client' => $this->config['client_id'],
                ],
            ]);

            $responseBody = $response->getBody()->getContents();

            // REVE SMS balance API returns plain text with the balance amount
            // The response is typically just the balance number
            if ($response->getStatusCode() === 200) {
                $balance = trim($responseBody);

                // Check if the response is numeric
                if (is_numeric($balance)) {
                    return (float) $balance;
                }

                // If response contains text, try to extract number
                if (preg_match('/[\d.]+/', $balance, $matches)) {
                    return (float) $matches[0];
                }
            }

            return 0.0;
        } catch (GuzzleException $e) {
            // Return 0.0 on error - the actual error handling should be done
            // by the calling code if needed
            return 0.0;
        }
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
