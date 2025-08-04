<?php

declare(strict_types=1);

use DevWizard\Textify\Channels\TextifyChannel;
use DevWizard\Textify\Contracts\TextifyManagerInterface;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Exceptions\TextifyException;
use DevWizard\Textify\Notifications\TextifyMessage;
use DevWizard\Textify\Providers\ArrayProvider;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

beforeEach(function () {
    // Clear any existing messages
    ArrayProvider::clearMessages();

    // Ensure textify is configured to use array driver by default
    config(['textify.default' => 'array']);
});

it('can send notifications via textify channel', function () {
    // Create a test notification
    $notification = new class extends Notification
    {
        public function via($notifiable): array
        {
            return ['textify'];
        }

        public function toTextify($notifiable): TextifyMessage
        {
            return TextifyMessage::create('Hello from notification!');
        }
    };

    // Create a test notifiable
    $notifiable = new class
    {
        public function routeNotificationForTextify($notification): string
        {
            return '01712345678';
        }
    };

    // Send the notification
    NotificationFacade::send($notifiable, $notification);

    // Verify the SMS was sent
    $messages = ArrayProvider::getMessages();
    expect($messages)->toHaveCount(1);
    expect($messages[0]['to'])->toBe('01712345678');
    expect($messages[0]['message'])->toBe('Hello from notification!');
});

it('can send notifications with custom driver and sender', function () {
    // Create a test notification with custom driver and sender
    $notification = new class extends Notification
    {
        public function via($notifiable): array
        {
            return ['textify'];
        }

        public function toTextify($notifiable): TextifyMessage
        {
            return TextifyMessage::create(
                message: 'Custom notification',
                from: 'MyApp',
                driver: 'array'
            );
        }
    };

    // Create a test notifiable
    $notifiable = new class
    {
        public function routeNotificationForTextify($notification): string
        {
            return '01812345678';
        }
    };

    // Send the notification
    NotificationFacade::send($notifiable, $notification);

    // Verify the SMS was sent with custom settings
    $messages = ArrayProvider::getMessages();
    expect($messages)->toHaveCount(1);
    expect($messages[0]['to'])->toBe('01812345678');
    expect($messages[0]['message'])->toBe('Custom notification');
    expect($messages[0]['from'])->toBe('MyApp');
});

it('can send notifications with array format', function () {
    // Create a test notification returning array
    $notification = new class extends Notification
    {
        public function via($notifiable): array
        {
            return ['textify'];
        }

        public function toTextify($notifiable): array
        {
            return [
                'message' => 'Array notification',
                'from' => 'ArrayApp',
            ];
        }
    };

    // Create a test notifiable
    $notifiable = new class
    {
        public function routeNotificationForTextify($notification): string
        {
            return '01912345678';
        }
    };

    // Send the notification
    NotificationFacade::send($notifiable, $notification);

    // Verify the SMS was sent
    $messages = ArrayProvider::getMessages();
    expect($messages)->toHaveCount(1);
    expect($messages[0]['to'])->toBe('01912345678');
    expect($messages[0]['message'])->toBe('Array notification');
    expect($messages[0]['from'])->toBe('ArrayApp');
});

it('can send notifications with string format', function () {
    // Create a test notification returning string
    $notification = new class extends Notification
    {
        public function via($notifiable): array
        {
            return ['textify'];
        }

        public function toTextify($notifiable): string
        {
            return 'Simple string notification';
        }
    };

    // Create a test notifiable
    $notifiable = new class
    {
        public function routeNotificationForTextify($notification): string
        {
            return '01512345678';
        }
    };

    // Send the notification
    NotificationFacade::send($notifiable, $notification);

    // Verify the SMS was sent
    $messages = ArrayProvider::getMessages();
    expect($messages)->toHaveCount(1);
    expect($messages[0]['to'])->toBe('01512345678');
    expect($messages[0]['message'])->toBe('Simple string notification');
});

it('throws exception when phone number cannot be determined', function () {
    // Create a test notification
    $notification = new class extends Notification
    {
        public function via($notifiable): array
        {
            return ['textify'];
        }

        public function toTextify($notifiable): string
        {
            return 'Test message';
        }
    };

    // Create a test notifiable without phone number
    $notifiable = new class
    {
        // No phone number methods or properties
    };

    // Send the notification - should throw exception
    expect(fn () => NotificationFacade::send($notifiable, $notification))
        ->toThrow(TextifyException::class, 'Unable to determine phone number for notification');
});

it('uses getTextifyPhoneNumber method when available', function () {
    // Create a test notification
    $notification = new class extends Notification
    {
        public function via($notifiable): array
        {
            return ['textify'];
        }

        public function toTextify($notifiable): string
        {
            return 'Custom method test';
        }
    };

    // Create a test notifiable with getTextifyPhoneNumber method
    $notifiable = new class
    {
        public function getTextifyPhoneNumber(): string
        {
            return '01312345678';
        }
    };

    // Send the notification
    NotificationFacade::send($notifiable, $notification);

    // Verify the SMS was sent
    $messages = ArrayProvider::getMessages();
    expect($messages)->toHaveCount(1);
    expect($messages[0]['to'])->toBe('01312345678');
    expect($messages[0]['message'])->toBe('Custom method test');
});

it('uses phone_number attribute when methods are not available', function () {
    // Create a test notification
    $notification = new class extends Notification
    {
        public function via($notifiable): array
        {
            return ['textify'];
        }

        public function toTextify($notifiable): string
        {
            return 'Phone attribute test';
        }
    };

    // Create a test notifiable with phone_number attribute
    $notifiable = new class
    {
        public $phone_number = '01612345678';
    };

    // Send the notification
    NotificationFacade::send($notifiable, $notification);

    // Verify the SMS was sent
    $messages = ArrayProvider::getMessages();
    expect($messages)->toHaveCount(1);
    expect($messages[0]['to'])->toBe('01612345678');
    expect($messages[0]['message'])->toBe('Phone attribute test');
});

it('prioritizes routeNotificationForTextify over getTextifyPhoneNumber', function () {
    // Create a test notification
    $notification = new class extends Notification
    {
        public function via($notifiable): array
        {
            return ['textify'];
        }

        public function toTextify($notifiable): string
        {
            return 'Priority test';
        }
    };

    // Create a test notifiable with both methods
    $notifiable = new class
    {
        public function routeNotificationForTextify($notification): string
        {
            return '01112345678'; // This should be used
        }

        public function getTextifyPhoneNumber(): string
        {
            return '01999999999'; // This should be ignored
        }
    };

    // Send the notification
    NotificationFacade::send($notifiable, $notification);

    // Verify the SMS was sent to the route method number
    $messages = ArrayProvider::getMessages();
    expect($messages)->toHaveCount(1);
    expect($messages[0]['to'])->toBe('01112345678');
    expect($messages[0]['message'])->toBe('Priority test');
});

it('can use textify channel directly', function () {
    $manager = app(TextifyManagerInterface::class);
    $channel = new TextifyChannel($manager);

    // Create a test notification
    $notification = new class extends Notification
    {
        public function toTextify($notifiable): TextifyMessage
        {
            return TextifyMessage::create('Direct channel test');
        }
    };

    // Create a test notifiable
    $notifiable = new class
    {
        public function routeNotificationForTextify($notification): string
        {
            return '01412345678';
        }
    };

    // Send via channel directly
    $response = $channel->send($notifiable, $notification);

    // Verify response
    expect($response)->toBeInstanceOf(TextifyResponse::class);
    expect($response->isSuccessful())->toBeTrue();

    // Verify the SMS was sent
    $messages = ArrayProvider::getMessages();
    expect($messages)->toHaveCount(1);
    expect($messages[0]['to'])->toBe('01412345678');
    expect($messages[0]['message'])->toBe('Direct channel test');
});
