<?php

declare(strict_types=1);

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\Events\TextifyJobFailed;
use DevWizard\Textify\Jobs\SendTextifyJob;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Event::fake();
    Queue::fake();
});

it('dispatches TextifyJobFailed event when job fails', function () {
    $message = new TextifyMessage(
        to: '01712345678',
        message: 'Test message',
        from: 'TestSender'
    );

    $job = new SendTextifyJob($message, 'test-provider');
    $exception = new \Exception('Test failure message');

    // Simulate job failure
    $job->failed($exception);

    // Assert that the TextifyJobFailed event was dispatched
    Event::assertDispatched(TextifyJobFailed::class, function ($event) use ($message, $exception) {
        return $event->getMessage()->getTo() === $message->getTo() &&
            $event->getMessage()->getMessage() === $message->getMessage() &&
            $event->getProvider() === 'test-provider' &&
            $event->getException() === $exception;
    });
});

it('creates TextifyJobFailed event with correct properties', function () {
    $message = new TextifyMessage(
        to: '01712345678',
        message: 'Hello World',
        from: 'Sender123'
    );

    $exception = new \Exception('Connection failed', 500);
    $event = new TextifyJobFailed($message, 'revesms', $exception);

    expect($event->getMessage())->toBe($message);
    expect($event->getProvider())->toBe('revesms');
    expect($event->getException())->toBe($exception);
    expect($event->getRecipient())->toBe('01712345678');
    expect($event->getMessageContent())->toBe('Hello World');
    expect($event->getSender())->toBe('Sender123');
    expect($event->getErrorMessage())->toBe('Connection failed');
    expect($event->getErrorCode())->toBe('500');
});

it('provides comprehensive metadata for TextifyJobFailed event', function () {
    $message = new TextifyMessage(
        to: '01887654321',
        message: 'Test SMS',
        from: 'TestApp'
    );

    $exception = new \Exception('API Error', 400);
    $event = new TextifyJobFailed($message, 'mimsms', $exception);

    $metadata = $event->getMetadata();

    expect($metadata)->toHaveKey('message_id');
    expect($metadata['to'])->toBe('01887654321');
    expect($metadata['from'])->toBe('TestApp');
    expect($metadata['provider'])->toBe('mimsms');
    expect($metadata['error_message'])->toBe('API Error');
    expect($metadata['error_code'])->toBe(400);
    expect($metadata)->toHaveKey('error_file');
    expect($metadata)->toHaveKey('error_line');
    expect($metadata)->toHaveKey('timestamp');
});
