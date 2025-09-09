<?php

declare(strict_types=1);

namespace DevWizard\Textify\Tests;

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\Providers\LogProvider;

it('rejects empty messages by default (required)', function () {
    $provider = new LogProvider([]);

    $message = TextifyMessage::create('01712345678', '', 'TestSender');
    $response = $provider->send($message);

    expect($response->isSuccessful())->toBeFalse();
    expect($response->getErrorCode())->toBe('INVALID_MESSAGE_CONTENT');
    expect($response->getErrorMessage())->toContain('field is required');
});

it('accepts empty messages when not required (nullable)', function () {
    // Temporarily override config to make message not required (nullable)
    config(['textify.validation.message.required' => false]);
    
    $provider = new LogProvider([]);

    $message = TextifyMessage::create('01712345678', '', 'TestSender');
    $response = $provider->send($message);

    expect($response->isSuccessful())->toBeTrue();

    // Reset config
    config(['textify.validation.message.required' => true]);
});

it('rejects messages shorter than minimum length', function () {
    config(['textify.validation.message.min' => 5]);
    
    $provider = new LogProvider([]);

    $message = TextifyMessage::create('01712345678', 'Hi', 'TestSender');
    $response = $provider->send($message);

    expect($response->isSuccessful())->toBeFalse();
    expect($response->getErrorCode())->toBe('INVALID_MESSAGE_CONTENT');
    expect($response->getErrorMessage())->toContain('must be at least 5 character');

    // Reset config
    config(['textify.validation.message.min' => 1]);
});

it('rejects messages longer than maximum length', function () {
    config(['textify.validation.message.max' => 10]);
    
    $provider = new LogProvider([]);

    $message = TextifyMessage::create('01712345678', 'This message is way too long', 'TestSender');
    $response = $provider->send($message);

    expect($response->isSuccessful())->toBeFalse();
    expect($response->getErrorCode())->toBe('INVALID_MESSAGE_CONTENT');
    expect($response->getErrorMessage())->toContain('may not be greater than 10 characters');

    // Reset config
    config(['textify.validation.message.max' => null]);
});

it('accepts valid messages', function () {
    $provider = new LogProvider([]);

    $message = TextifyMessage::create('01712345678', 'Valid message content', 'TestSender');
    $response = $provider->send($message);

    expect($response->isSuccessful())->toBeTrue();
});

it('validates whitespace-only messages as empty', function () {
    $provider = new LogProvider([]);

    $message = TextifyMessage::create('01712345678', '   ', 'TestSender');
    $response = $provider->send($message);

    expect($response->isSuccessful())->toBeFalse();
    expect($response->getErrorCode())->toBe('INVALID_MESSAGE_CONTENT');
    expect($response->getErrorMessage())->toContain('field is required');
});
