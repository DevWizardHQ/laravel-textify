<?php

declare(strict_types=1);

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Events\TextifyFailed;
use DevWizard\Textify\Events\TextifySending;
use DevWizard\Textify\Events\TextifySent;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
});

it('dispatches TextifySending event when SMS is about to be sent', function () {
    $message = new TextifyMessage('01712345678', 'Test message');

    $event = new TextifySending($message, 'array');

    Event::dispatch($event);

    Event::assertDispatched(TextifySending::class, function ($dispatchedEvent) use ($message) {
        return $dispatchedEvent->message->to === $message->to &&
            $dispatchedEvent->message->message === $message->message &&
            $dispatchedEvent->provider === 'array';
    });
});

it('dispatches TextifySent event when SMS is sent successfully', function () {
    $message = new TextifyMessage('01712345678', 'Test message');
    $response = TextifyResponse::success('msg_123', null, 'sent');

    $event = new TextifySent($message, $response, 'array');

    Event::dispatch($event);

    Event::assertDispatched(TextifySent::class, function ($dispatchedEvent) use ($message, $response) {
        return $dispatchedEvent->message->to === $message->to &&
            $dispatchedEvent->response->getMessageId() === $response->getMessageId() &&
            $dispatchedEvent->provider === 'array';
    });
});

it('dispatches TextifyFailed event when SMS sending fails', function () {
    $message = new TextifyMessage('01712345678', 'Test message');
    $response = TextifyResponse::failed('Invalid phone number', 'INVALID_PHONE');

    $event = new TextifyFailed($message, $response, 'array');

    Event::dispatch($event);

    Event::assertDispatched(TextifyFailed::class, function ($dispatchedEvent) use ($message, $response) {
        return $dispatchedEvent->message->to === $message->to &&
            $dispatchedEvent->response->getErrorMessage() === $response->getErrorMessage() &&
            $dispatchedEvent->provider === 'array';
    });
});

it('creates TextifySending event with correct properties', function () {
    $message = new TextifyMessage('01712345678', 'Test message', 'TEST');
    $event = new TextifySending($message, 'dhorola');

    expect($event->message)->toBe($message);
    expect($event->provider)->toBe('dhorola');
});

it('creates TextifySent event with correct properties', function () {
    $message = new TextifyMessage('01712345678', 'Test message', 'TEST');
    $response = TextifyResponse::success('msg_123', null, 'sent');
    $event = new TextifySent($message, $response, 'dhorola');

    expect($event->message)->toBe($message);
    expect($event->response)->toBe($response);
    expect($event->provider)->toBe('dhorola');
});

it('creates TextifyFailed event with correct properties', function () {
    $message = new TextifyMessage('01712345678', 'Test message', 'TEST');
    $response = TextifyResponse::failed('Failed to send', 'SEND_ERROR');
    $event = new TextifyFailed($message, $response, 'dhorola');

    expect($event->message)->toBe($message);
    expect($event->response)->toBe($response);
    expect($event->provider)->toBe('dhorola');
});
