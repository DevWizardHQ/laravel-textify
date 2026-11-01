<?php

declare(strict_types=1);

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\Exceptions\TextifyException;
use DevWizard\Textify\Providers\Bangladeshi\SasBulkSmsProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

function sasProviderWithResponses(array $responses, array &$history, array $config = []): SasBulkSmsProvider
{
    $provider = new SasBulkSmsProvider($config + ['apikey' => 'key', 'secretkey' => 'secret']);

    $stack = HandlerStack::create(new MockHandler($responses));
    $stack->push(Middleware::history($history));

    $property = new ReflectionProperty($provider, 'client');
    $property->setAccessible(true);
    $property->setValue($provider, new Client(['handler' => $stack, 'base_uri' => 'http://sms.sasbulksms.com:3040']));

    return $provider;
}

it('sends with the REVE parameters, 880 number format and default caller id 0', function () {
    $history = [];
    $provider = sasProviderWithResponses([
        new Response(200, [], json_encode(['Status' => '0', 'Text' => 'ACCEPTD', 'Message_ID' => '1373129'])),
    ], $history);

    $response = $provider->send(new TextifyMessage(to: '01712345678', message: 'Hello'));

    expect($response->isSuccessful())->toBeTrue()
        ->and($response->getMessageId())->toBe('1373129');

    $request = $history[0]['request'];
    parse_str((string) $request->getBody(), $body);

    expect($request->getMethod())->toBe('POST')
        ->and((string) $request->getUri())->toBe('http://sms.sasbulksms.com:3040/sendtext')
        ->and($body)->toBe([
            'apikey' => 'key',
            'secretkey' => 'secret',
            'callerID' => '0',
            'toUser' => '8801712345678',
            'messageContent' => 'Hello',
        ]);
});

it('returns the gateway status description on failure', function () {
    $history = [];
    $provider = sasProviderWithResponses([
        new Response(200, [], json_encode([
            'Status' => '-1',
            'StatusDescription' => 'Org Client Not Found',
            'Text' => 'REJECTD',
            'Message_ID' => '-1',
        ])),
    ], $history);

    $response = $provider->send(new TextifyMessage(to: '01712345678', message: 'Hello'));

    expect($response->isFailed())->toBeTrue()
        ->and($response->getErrorCode())->toBe('-1')
        ->and($response->getErrorMessage())->toBe('Org client not found (invalid apikey/secretkey)');
});

it('explains the IP whitelist rejection', function () {
    $history = [];
    $provider = sasProviderWithResponses([
        new Response(200, [], json_encode(['Status' => '-59', 'StatusDescription' => 'IP Not Allowed', 'Text' => 'REJECTD', 'Message_ID' => '-1'])),
    ], $history);

    $response = $provider->send(new TextifyMessage(to: '01712345678', message: 'Hello'));

    expect($response->getErrorCode())->toBe('-59')
        ->and($response->getErrorMessage())->toContain('whitelist');
});

it('maps delivery status', function () {
    $history = [];
    $provider = sasProviderWithResponses([
        new Response(200, [], json_encode(['Status' => '0', 'Text' => 'DELIVRD', 'Message_ID' => '1'])),
        new Response(200, [], json_encode(['Status' => '4', 'Text' => 'SENT', 'Message_ID' => '2'])),
    ], $history);

    expect($provider->getDeliveryStatus('1'))->toBe('delivered')
        ->and($provider->getDeliveryStatus('2'))->toBe('sent');
});

it('formats and validates Bangladeshi numbers', function (string $input, string $formatted, bool $valid) {
    $provider = new SasBulkSmsProvider(['apikey' => 'key', 'secretkey' => 'secret']);

    expect($provider->formatPhoneNumber($input))->toBe($formatted)
        ->and($provider->validatePhoneNumber($input))->toBe($valid);
})->with([
    ['01712345678', '8801712345678', true],
    ['+8801712345678', '8801712345678', true],
    ['1712345678', '8801712345678', true],
    ['0171234567', '880171234567', false],
    ['+1234567890', '8801234567890', false],
]);

it('requires apikey and secretkey only', function () {
    expect(new SasBulkSmsProvider(['apikey' => 'key', 'secretkey' => 'secret']))->getName()->toBe('sasbulksms');
    expect(fn () => new SasBulkSmsProvider(['apikey' => 'key']))->toThrow(TextifyException::class);
});

it('reports balance as unsupported', function () {
    $provider = new SasBulkSmsProvider(['apikey' => 'key', 'secretkey' => 'secret']);

    expect(fn () => $provider->getBalance())->toThrow(TextifyException::class);
});
