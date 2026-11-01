<?php

declare(strict_types=1);

namespace DevWizard\Textify\Providers\Bangladeshi;

use DevWizard\Textify\Exceptions\TextifyException;

class SasBulkSmsProvider extends ReveSmsProvider
{
    protected string $name = 'sasbulksms';

    protected string $defaultCallerId = '0';

    protected function getClientConfig(): array
    {
        return [
            'base_uri' => $this->config['base_uri'] ?? 'http://sms.sasbulksms.com:3040',
            'timeout' => $this->config['timeout'] ?? 30,
            'connect_timeout' => $this->config['connect_timeout'] ?? 10,
            'verify' => $this->config['verify_ssl'] ?? false,
        ];
    }

    protected function getRequiredConfigKeys(): array
    {
        return ['apikey', 'secretkey'];
    }

    public function getBalance(): float
    {
        throw new TextifyException('SAS Bulk SMS does not provide a balance API', 0, null, $this->name);
    }

    public function validatePhoneNumber(string $phoneNumber): bool
    {
        return preg_match('/^8801[3-9]\d{8}$/', $this->formatPhoneNumber($phoneNumber)) === 1;
    }

    public function formatPhoneNumber(string $phoneNumber): string
    {
        $phoneNumber = preg_replace('/[^\d]/', '', $phoneNumber);

        if (str_starts_with($phoneNumber, '01')) {
            return '88'.$phoneNumber;
        }

        if (strlen($phoneNumber) === 10 && str_starts_with($phoneNumber, '1')) {
            return '880'.$phoneNumber;
        }

        return $phoneNumber;
    }
}
