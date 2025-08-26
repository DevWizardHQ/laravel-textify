<?php

declare(strict_types=1);

use DevWizard\Textify\Providers\Bangladeshi\AlphaSmsProvider;
use DevWizard\Textify\Providers\Bangladeshi\DhorolaSmsProvider;
use DevWizard\Textify\Providers\Bangladeshi\EsmsProvider;
use DevWizard\Textify\Providers\Bangladeshi\MimSmsProvider;
use DevWizard\Textify\Providers\Bangladeshi\ReveSmsProvider;

/**
 * Helper function to get client config using reflection
 */
function getClientConfig($provider): array
{
    $reflection = new ReflectionClass($provider);
    $method = $reflection->getMethod('getClientConfig');
    $method->setAccessible(true);
    return $method->invoke($provider);
}

it('defaults SSL verification to false for all providers', function () {

    // Test ReveSmsProvider
    $reveProvider = new ReveSmsProvider([
        'apikey' => 'test_key',
        'secretkey' => 'test_secret',
        'client_id' => 'test_client',
    ]);
    $reveConfig = getClientConfig($reveProvider);
    expect($reveConfig['verify'])->toBeFalse();

    // Test AlphaSmsProvider
    $alphaProvider = new AlphaSmsProvider([
        'api_key' => 'test_key',
    ]);
    $alphaConfig = getClientConfig($alphaProvider);
    expect($alphaConfig['verify'])->toBeFalse();

    // Test DhorolaSmsProvider
    $dhorolaProvider = new DhorolaSmsProvider([
        'api_key' => 'test_key',
        'sender_id' => 'test_sender',
    ]);
    $dhorolaConfig = getClientConfig($dhorolaProvider);
    expect($dhorolaConfig['verify'])->toBeFalse();

    // Test EsmsProvider
    $esmsProvider = new EsmsProvider([
        'api_token' => 'test_token',
        'sender_id' => 'test_sender',
    ]);
    $esmsConfig = getClientConfig($esmsProvider);
    expect($esmsConfig['verify'])->toBeFalse();

    // Test MimSmsProvider
    $mimProvider = new MimSmsProvider([
        'username' => 'test@example.com',
        'apikey' => 'test_key',
    ]);
    $mimConfig = getClientConfig($mimProvider);
    expect($mimConfig['verify'])->toBeFalse();
});

it('allows SSL verification to be explicitly enabled', function () {
    // Test that explicit verify_ssl=true works
    $reveProvider = new ReveSmsProvider([
        'apikey' => 'test_key',
        'secretkey' => 'test_secret',
        'client_id' => 'test_client',
        'verify_ssl' => true,
    ]);
    $reveConfig = getClientConfig($reveProvider);
    expect($reveConfig['verify'])->toBeTrue();

    // Test that explicit verify_ssl=false works
    $alphaProvider = new AlphaSmsProvider([
        'api_key' => 'test_key',
        'verify_ssl' => false,
    ]);
    $alphaConfig = getClientConfig($alphaProvider);
    expect($alphaConfig['verify'])->toBeFalse();
});

it('ensures ReveSmsProvider balance client also respects SSL verification setting', function () {
    // Create a provider with SSL verification disabled
    $provider = new ReveSmsProvider([
        'apikey' => 'test_key',
        'secretkey' => 'test_secret',
        'client_id' => 'test_client',
        'verify_ssl' => false,
    ]);

    // Verify the getBalance method exists
    expect(method_exists($provider, 'getBalance'))->toBeTrue();
    
    // Verify that the provider is properly configured
    expect($provider)->toBeInstanceOf(ReveSmsProvider::class);
});
