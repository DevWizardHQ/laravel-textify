<?php

declare(strict_types=1);

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Facades\Textify;
use DevWizard\Textify\Providers\ArrayProvider;

it('can send SMS using array driver', function () {
    // Clear any existing messages
    ArrayProvider::clearMessages();

    // Send SMS using array driver explicitly
    $response = Textify::via('array')->send('01712345678', 'Test message');

    // Verify response
    expect($response)->toBeInstanceOf(TextifyResponse::class);
    expect($response->isSuccessful())->toBeTrue();
    expect($response->getMessageId())->toStartWith('array_');
    expect($response->getStatus())->toBe('sent');

    // Verify message was stored
    $messages = ArrayProvider::getMessages();
    expect($messages)->toHaveCount(1);
    expect($messages[0]['to'])->toBe('01712345678');
    expect($messages[0]['message'])->toBe('Test message');
});

it('can send SMS using specific driver', function () {
    ArrayProvider::clearMessages();

    $response = Textify::via('array')->send('01812345678', 'Via specific driver');

    expect($response->isSuccessful())->toBeTrue();

    $messages = ArrayProvider::getMessages();
    expect($messages)->toHaveCount(1);
    expect($messages[0]['to'])->toBe('01812345678');
    expect($messages[0]['message'])->toBe('Via specific driver');
});

it('can send bulk SMS', function () {
    ArrayProvider::clearMessages();

    $messages = [
        ['to' => '01712345678', 'message' => 'Message 1'],
        ['to' => '01812345678', 'message' => 'Message 2'],
        ['to' => '01912345678', 'message' => 'Message 3'],
    ];

    $responses = Textify::via('array')->send($messages);

    expect($responses)->toHaveCount(3);

    foreach ($responses as $response) {
        expect($response->isSuccessful())->toBeTrue();
    }

    $storedMessages = ArrayProvider::getMessages();
    expect($storedMessages)->toHaveCount(3);
});

it('can send same message to multiple numbers', function () {
    ArrayProvider::clearMessages();

    $phoneNumbers = ['01712345678', '01812345678', '01912345678'];
    $message = 'Same message for everyone!';

    $responses = Textify::via('array')->send($phoneNumbers, $message);

    expect($responses)->toHaveCount(3);

    foreach ($responses as $response) {
        expect($response->isSuccessful())->toBeTrue();
    }

    $storedMessages = ArrayProvider::getMessages();
    expect($storedMessages)->toHaveCount(3);

    // Verify all messages have the same content
    foreach ($storedMessages as $storedMessage) {
        expect($storedMessage['message'])->toBe($message);
    }
});

it('can send personalized messages to different numbers', function () {
    ArrayProvider::clearMessages();

    $personalizedMessages = [
        ['to' => '01712345678', 'message' => 'Hello John, your order #123 is ready!'],
        ['to' => '01812345678', 'message' => 'Hi Sarah, your appointment is tomorrow at 2 PM'],
        ['to' => '01912345678', 'message' => 'Dear Mike, your payment of $50 was received'],
    ];

    $responses = Textify::via('array')->send($personalizedMessages);

    expect($responses)->toHaveCount(3);

    foreach ($responses as $response) {
        expect($response->isSuccessful())->toBeTrue();
    }

    $storedMessages = ArrayProvider::getMessages();
    expect($storedMessages)->toHaveCount(3);

    // Verify messages are personalized
    expect($storedMessages[0]['message'])->toContain('John');
    expect($storedMessages[1]['message'])->toContain('Sarah');
    expect($storedMessages[2]['message'])->toContain('Mike');
});
it('validates bangladeshi phone numbers correctly', function () {
    $provider = new \DevWizard\Textify\Providers\Bangladeshi\DhorolaSmsProvider([
        'api_key' => 'test',
        'sender_id' => 'test',
        'base_uri' => 'https://test.com',
        'version_endpoint' => '/api/v1/send-sms',
    ]);

    // Valid formats
    expect($provider->validatePhoneNumber('01712345678'))->toBeTrue();
    expect($provider->validatePhoneNumber('8801712345678'))->toBeTrue();
    expect($provider->validatePhoneNumber('+8801712345678'))->toBeTrue();

    // Invalid formats
    expect($provider->validatePhoneNumber('1712345678'))->toBeFalse();
    expect($provider->validatePhoneNumber('0171234567'))->toBeFalse();
    expect($provider->validatePhoneNumber('+1234567890'))->toBeFalse();
});

it('formats bangladeshi phone numbers correctly', function () {
    $provider = new \DevWizard\Textify\Providers\Bangladeshi\DhorolaSmsProvider([
        'api_key' => 'test',
        'sender_id' => 'test',
        'base_uri' => 'https://test.com',
    ]);

    // Dhorola SMS uses international format (8801XXXXXXXXX)
    expect($provider->formatPhoneNumber('8801712345678'))->toBe('8801712345678');
    expect($provider->formatPhoneNumber('01712345678'))->toBe('8801712345678');
    expect($provider->formatPhoneNumber('+8801712345678'))->toBe('8801712345678');
});

it('can create SMS message DTO', function () {
    $message = TextifyMessage::create('01712345678', 'Test message', '8801', ['key' => 'value']);

    expect($message->to)->toBe('01712345678');
    expect($message->message)->toBe('Test message');
    expect($message->from)->toBe('8801');
    expect($message->metadata)->toBe(['key' => 'value']);

    $array = $message->toArray();
    expect($array)->toHaveKeys(['to', 'message', 'from', 'metadata']);
});

it('can create success response', function () {
    $response = TextifyResponse::success('msg_123', 0.05, 'sent', ['raw' => 'data']);

    expect($response->isSuccessful())->toBeTrue();
    expect($response->isFailed())->toBeFalse();
    expect($response->getMessageId())->toBe('msg_123');
    expect($response->getCost())->toBe(0.05);
    expect($response->getStatus())->toBe('sent');
    expect($response->getRawResponse())->toBe(['raw' => 'data']);
});

it('can create failed response', function () {
    $response = TextifyResponse::failed('Error message', 'ERROR_CODE', ['error' => 'details']);

    expect($response->isFailed())->toBeTrue();
    expect($response->isSuccessful())->toBeFalse();
    expect($response->getErrorMessage())->toBe('Error message');
    expect($response->getErrorCode())->toBe('ERROR_CODE');
    expect($response->getRawResponse())->toBe(['error' => 'details']);
});
