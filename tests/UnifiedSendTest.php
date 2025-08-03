<?php

declare(strict_types=1);

use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Facades\Textify;
use DevWizard\Textify\Providers\ArrayProvider;

describe('Unified Send Method', function () {
    beforeEach(function () {
        ArrayProvider::clearMessages();
    });

    it('can send single SMS with direct parameters', function () {
        // Case 1: Single SMS - send('01712345678', 'message')
        $response = Textify::via('array')->send('01712345678', 'Hello World!');

        expect($response)->toBeInstanceOf(TextifyResponse::class);
        expect($response->isSuccessful())->toBeTrue();

        $messages = ArrayProvider::getMessages();
        expect($messages)->toHaveCount(1);
        expect($messages[0]['to'])->toBe('01712345678');
        expect($messages[0]['message'])->toBe('Hello World!');
    });

    it('can send same message to multiple numbers', function () {
        // Case 2: Same message to multiple numbers - send(['01712345678', '01812345678'], 'message')
        $phoneNumbers = ['01712345678', '01812345678', '01912345678'];
        $responses = Textify::via('array')->send($phoneNumbers, 'Same message for all');

        expect($responses)->toBeArray();
        expect($responses)->toHaveCount(3);

        foreach ($responses as $response) {
            expect($response->isSuccessful())->toBeTrue();
        }

        $messages = ArrayProvider::getMessages();
        expect($messages)->toHaveCount(3);

        foreach ($messages as $i => $message) {
            expect($message['to'])->toBe($phoneNumbers[$i]);
            expect($message['message'])->toBe('Same message for all');
        }
    });

    it('can send different messages to different numbers', function () {
        // Case 3: Different messages to different numbers
        $messageData = [
            ['to' => '01712345678', 'message' => 'Welcome John!'],
            ['to' => '01812345678', 'message' => 'Hello Sarah!'],
            ['to' => '01912345678', 'message' => 'Hi Bob!', 'from' => 'CustomSender'],
        ];

        $responses = Textify::via('array')->send($messageData);

        expect($responses)->toBeArray();
        expect($responses)->toHaveCount(3);

        foreach ($responses as $response) {
            expect($response->isSuccessful())->toBeTrue();
        }

        $messages = ArrayProvider::getMessages();
        expect($messages)->toHaveCount(3);
        expect($messages[0]['message'])->toBe('Welcome John!');
        expect($messages[1]['message'])->toBe('Hello Sarah!');
        expect($messages[2]['message'])->toBe('Hi Bob!');
    });
});

describe('Fluent Interface with Preparation', function () {
    beforeEach(function () {
        ArrayProvider::clearMessages();
    });

    it('can prepare contacts and message separately', function () {
        // Case 4a: Prepare contacts and message separately, then send
        $response = Textify::via('array')
            ->to('01712345678')
            ->message('Prepared message')
            ->send();

        expect($response)->toBeInstanceOf(TextifyResponse::class);
        expect($response->isSuccessful())->toBeTrue();

        $messages = ArrayProvider::getMessages();
        expect($messages)->toHaveCount(1);
        expect($messages[0]['to'])->toBe('01712345678');
        expect($messages[0]['message'])->toBe('Prepared message');
    });

    it('can prepare multiple contacts with same message', function () {
        // Case 4b: Prepare multiple contacts with same message
        $phoneNumbers = ['01712345678', '01812345678'];
        $responses = Textify::via('array')
            ->to($phoneNumbers)
            ->message('Bulk message')
            ->from('BrandName')
            ->send();

        expect($responses)->toBeArray();
        expect($responses)->toHaveCount(2);

        $messages = ArrayProvider::getMessages();
        expect($messages)->toHaveCount(2);

        foreach ($messages as $message) {
            expect($message['message'])->toBe('Bulk message');
            expect($message['from'])->toBe('BrandName');
        }
    });

    it('can chain preparation methods in any order', function () {
        // Test method chaining flexibility
        $response = Textify::via('array')
            ->from('Sender123')
            ->message('Chained message')
            ->to('01712345678')
            ->send();

        expect($response->isSuccessful())->toBeTrue();

        $messages = ArrayProvider::getMessages();
        expect($messages[0]['from'])->toBe('Sender123');
        expect($messages[0]['message'])->toBe('Chained message');
    });

    it('can override prepared data with send parameters', function () {
        // Prepared data can be overridden by send() parameters
        $response = Textify::via('array')
            ->to('01712345678')
            ->message('Prepared message')
            ->send('01812345678', 'Override message');

        expect($response->isSuccessful())->toBeTrue();

        $messages = ArrayProvider::getMessages();
        expect($messages)->toHaveCount(1);
        expect($messages[0]['to'])->toBe('01812345678'); // Overridden
        expect($messages[0]['message'])->toBe('Override message'); // Overridden
    });
});

describe('Queue with Unified Interface', function () {
    it('can queue with prepared data', function () {
        // Note: This test assumes queue functionality works
        // In real scenario, this would dispatch a job
        expect(function () {
            Textify::via('array')
                ->to('01712345678')
                ->message('Queued message')
                ->queue('sms-queue');
        })->not->toThrow(Exception::class);
    });
});

describe('Error Handling', function () {
    it('throws exception when no contacts provided', function () {
        expect(function () {
            Textify::via('array')->send();
        })->toThrow('No contacts specified');
    });

    it('throws exception when no message provided for simple array', function () {
        expect(function () {
            Textify::via('array')->send(['01712345678', '01812345678']);
        })->toThrow('No message specified');
    });

    it('throws exception when invalid array format provided', function () {
        expect(function () {
            Textify::via('array')->send(['invalid', 'array', 'format']);
        })->toThrow('No message specified. Use message() method or provide message in send().');
    });
});

describe('Real-world Usage Patterns', function () {
    beforeEach(function () {
        ArrayProvider::clearMessages();
    });

    it('can handle marketing campaign scenario', function () {
        // Marketing campaign: same message to many customers
        $customers = ['01712345678', '01812345678', '01912345678'];
        $campaignMessage = 'Special offer: 50% off! Use code SAVE50. Valid until tomorrow.';

        $responses = Textify::via('array')
            ->from('ShopBrand')
            ->send($customers, $campaignMessage);

        expect($responses)->toHaveCount(3);

        $messages = ArrayProvider::getMessages();
        foreach ($messages as $message) {
            expect($message['message'])->toBe($campaignMessage);
            expect($message['from'])->toBe('ShopBrand');
        }
    });

    it('can handle personalized notifications', function () {
        // Personalized notifications: different message per user
        $notifications = [
            ['to' => '01712345678', 'message' => 'Hi John, your order #1234 is ready for pickup.'],
            ['to' => '01812345678', 'message' => 'Hi Sarah, your appointment is tomorrow at 3 PM.'],
            ['to' => '01912345678', 'message' => 'Hi Bob, your subscription expires in 3 days.'],
        ];

        $responses = Textify::via('array')
            ->from('ServiceApp')
            ->send($notifications);

        expect($responses)->toHaveCount(3);

        $messages = ArrayProvider::getMessages();
        expect($messages[0]['message'])->toContain('John');
        expect($messages[1]['message'])->toContain('Sarah');
        expect($messages[2]['message'])->toContain('Bob');
    });

    it('can handle prepared contact list scenario', function () {
        // Business scenario: prepare contact list, then send different messages
        $vipCustomers = ['01712345678', '01812345678'];

        // First message
        $responses1 = Textify::via('array')
            ->to($vipCustomers)
            ->from('VIPService')
            ->send(null, 'VIP Flash Sale starts now! 70% off everything.');

        // Second message to same list
        $responses2 = Textify::via('array')
            ->to($vipCustomers)
            ->from('VIPService')
            ->send(null, 'Only 2 hours left for VIP Flash Sale!');

        expect($responses1)->toHaveCount(2);
        expect($responses2)->toHaveCount(2);

        $messages = ArrayProvider::getMessages();
        expect($messages)->toHaveCount(4); // 2 messages × 2 customers = 4 total
    });
});
