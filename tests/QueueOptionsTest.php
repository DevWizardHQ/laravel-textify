<?php

declare(strict_types=1);

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Exceptions\TextifyException;
use DevWizard\Textify\Facades\Textify;
use DevWizard\Textify\Jobs\SendTextifyJob;
use DevWizard\Textify\Providers\ArrayProvider;
use DevWizard\Textify\TextifyManager;
use Illuminate\Support\Facades\Queue;

function queuedMessage(): TextifyMessage
{
    return new TextifyMessage(to: '01712345678', message: 'Hello');
}

it('uses default queue options', function () {
    $job = new SendTextifyJob(queuedMessage());

    expect($job->tries)->toBe(3)
        ->and($job->backoff)->toBe([10, 30, 60])
        ->and($job->timeout)->toBe(60)
        ->and($job->connection)->toBeNull()
        ->and($job->queue)->toBeNull()
        ->and($job->delay)->toBeNull();
});

it('reads queue options from config', function () {
    config()->set('textify.queue', [
        'connection' => 'redis',
        'name' => 'sms',
        'tries' => 5,
        'backoff' => [15],
        'timeout' => 20,
        'delay' => 30,
    ]);

    $job = new SendTextifyJob(queuedMessage());

    expect($job->tries)->toBe(5)
        ->and($job->backoff)->toBe(15)
        ->and($job->timeout)->toBe(20)
        ->and($job->connection)->toBe('redis')
        ->and($job->queue)->toBe('sms')
        ->and($job->delay)->toBe(30);
});

function handleQueuedFailure(string $errorCode): SendTextifyJob
{
    Textify::shouldReceive('send')->once()->andReturn(TextifyResponse::failed(errorMessage: 'boom', errorCode: $errorCode));

    $job = (new SendTextifyJob(queuedMessage()))->withFakeQueueInteractions();
    $job->handle();

    return $job;
}

it('retries failures where the SMS was never sent', function (string $errorCode) {
    expect(fn () => handleQueuedFailure($errorCode))->toThrow(TextifyException::class, $errorCode);
})->with([
    TextifyResponse::ERROR_CONNECTION_FAILED,
    TextifyResponse::ERROR_SERVER_ERROR,
]);

it('fails without retrying when a retry could duplicate or cannot help', function (string $errorCode) {
    handleQueuedFailure($errorCode)->assertFailed();
})->with([
    TextifyResponse::ERROR_TIMEOUT,
    TextifyResponse::ERROR_PROVIDER_ERROR,
    TextifyResponse::ERROR_INVALID_PHONE_NUMBER,
    '-59',
]);

it('retries timeouts and provider errors when enabled', function (string $errorCode) {
    config()->set('textify.queue.retry', ['timeouts' => true, 'provider_errors' => true]);

    expect(fn () => handleQueuedFailure($errorCode))->toThrow(TextifyException::class);
})->with([
    TextifyResponse::ERROR_TIMEOUT,
    '-59',
]);

it('never retries invalid input even when provider errors retry', function () {
    config()->set('textify.queue.retry.provider_errors', true);

    handleQueuedFailure(TextifyResponse::ERROR_INVALID_MESSAGE_CONTENT)->assertFailed();
});

it('does nothing more after a successful send', function () {
    Textify::shouldReceive('send')->once()->andReturn(TextifyResponse::success(messageId: '1'));

    $job = (new SendTextifyJob(queuedMessage()))->withFakeQueueInteractions();
    $job->handle();

    $job->assertNotFailed();
});

it('spaces bulk queued messages by the cooldown and honours the queue name override', function () {
    Queue::fake();
    config()->set('textify.queue.delay', 5);

    $manager = new TextifyManager(['default' => 'array', 'queue' => ['cooldown' => 10]]);
    $manager->extend('array', new ArrayProvider);

    $manager->to(['01712345678', '01812345678', '01912345678'])->message('Hi')->queue('priority');

    $delays = [];
    Queue::assertPushedOn('priority', SendTextifyJob::class, function (SendTextifyJob $job) use (&$delays) {
        $delays[] = $job->delay;

        return true;
    });

    expect($delays)->toBe([5, 15, 25]);
});
