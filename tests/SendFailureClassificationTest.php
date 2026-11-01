<?php

declare(strict_types=1);

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Providers\ArrayProvider;
use DevWizard\Textify\Providers\Bangladeshi\ReveSmsProvider;
use DevWizard\Textify\TextifyManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

function reveProviderThrowing(Throwable $exception): ReveSmsProvider
{
    $provider = new ReveSmsProvider(['apikey' => 'key', 'secretkey' => 'secret', 'client_id' => 'client']);

    $property = new ReflectionProperty($provider, 'client');
    $property->setAccessible(true);
    $property->setValue($provider, new Client(['handler' => HandlerStack::create(new MockHandler([$exception]))]));

    return $provider;
}

it('classifies transport failures by whether the SMS may have been sent', function (Closure $exception, string $expectedCode) {
    $response = reveProviderThrowing($exception(new Request('POST', 'http://sms.test/sendtext')))
        ->send(new TextifyMessage(to: '01712345678', message: 'Hello'));

    expect($response->isFailed())->toBeTrue()
        ->and($response->getErrorCode())->toBe($expectedCode);
})->with([
    'host not resolved' => [fn ($request) => new ConnectException('cURL error 6: Could not resolve host: sms.test', $request), TextifyResponse::ERROR_CONNECTION_FAILED],
    'connect timeout' => [fn ($request) => new ConnectException('cURL error 28: Connection timed out after 10001 milliseconds', $request), TextifyResponse::ERROR_CONNECTION_FAILED],
    'response timeout (Guzzle 7)' => [fn ($request) => new ConnectException('cURL error 28: Operation timed out after 30001 milliseconds with 0 bytes received', $request), TextifyResponse::ERROR_TIMEOUT],
    'empty reply' => [fn ($request) => new RequestException('cURL error 52: Empty reply from server', $request), TextifyResponse::ERROR_TIMEOUT],
    'service unavailable' => [fn ($request) => new ServerException('503', $request, new Response(503)), TextifyResponse::ERROR_SERVER_ERROR],
    'gateway timeout' => [fn ($request) => new ServerException('504', $request, new Response(504)), TextifyResponse::ERROR_TIMEOUT],
    'unauthorized' => [fn ($request) => new ClientException('401', $request, new Response(401)), TextifyResponse::ERROR_PROVIDER_ERROR],
    'wrapped connect error' => [fn ($request) => new Exception('API request failed', 0, new ConnectException('cURL error 7: Failed to connect', $request)), TextifyResponse::ERROR_CONNECTION_FAILED],
]);

it('falls back on connection failures but not on timeouts by default', function (string $errorCode, bool $fallbackOnTimeout, bool $expected) {
    $manager = new class(['fallback_on_timeout' => $fallbackOnTimeout]) extends TextifyManager
    {
        public function usesFallback(TextifyResponse $response): bool
        {
            return $this->shouldUseFallback($response);
        }
    };
    $manager->extend('array', new ArrayProvider);

    expect($manager->usesFallback(TextifyResponse::failed(errorMessage: 'boom', errorCode: $errorCode)))->toBe($expected);
})->with([
    [TextifyResponse::ERROR_CONNECTION_FAILED, false, true],
    [TextifyResponse::ERROR_SERVER_ERROR, false, true],
    [TextifyResponse::ERROR_TIMEOUT, false, false],
    [TextifyResponse::ERROR_TIMEOUT, true, true],
]);
