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

it('throws on a failed send so the queue retries it', function () {
    Textify::shouldReceive('send')->once()->andReturn(TextifyResponse::failed(errorMessage: 'IP not allowed', errorCode: '-59'));

    expect(fn () => (new SendTextifyJob(queuedMessage()))->handle())
        ->toThrow(TextifyException::class, 'IP not allowed');
});

it('does not throw on a failed send when retry_failed_sends is off', function () {
    config()->set('textify.queue.retry_failed_sends', false);
    Textify::shouldReceive('send')->once()->andReturn(TextifyResponse::failed(errorMessage: 'IP not allowed', errorCode: '-59'));

    expect(fn () => (new SendTextifyJob(queuedMessage()))->handle())->not->toThrow(TextifyException::class);
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
