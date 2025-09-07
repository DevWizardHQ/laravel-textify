# Laravel Textify 📱

[![Latest Version on Packagist](https://img.shields.io/packagist/v/devwizardhq/laravel-textify.svg?style=flat-square)](https://packagist.org/packages/devwizardhq/laravel-textify)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/devwizardhq/laravel-textify/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/devwizardhq/laravel-textify/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/devwizardhq/laravel-textify/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/devwizardhq/laravel-textify/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/devwizardhq/laravel-textify.svg?style=flat-square)](https://packagist.org/packages/devwizardhq/laravel-textify)
[![License](https://img.shields.io/packagist/l/devwizardhq/laravel-textify.svg?style=flat-square)](https://packagist.org/packages/devwizardhq/laravel-textify)

A powerful and enterprise-ready SMS package for Laravel applications supporting **8+ SMS providers** including Bangladeshi and international gateways. Built with modern PHP 8.3+ and Laravel 10+ support, featuring queue integration, automatic fallback system, comprehensive activity tracking, and an intuitive fluent API.

**Perfect for Laravel developers who need reliable SMS functionality with multiple provider support and enterprise-grade features.**

## ✨ Features

-   🚀 **Multiple SMS Providers**: Support for 8+ SMS gateways with unified API
-   🇧🇩 **Bangladeshi SMS Providers**: DhorolaSMS, BulkSMSBD, MimSMS, eSMS, REVE SMS, Alpha SMS
-   🌍 **International SMS Providers**: Twilio, Nexmo (Vonage) with optional SDK installation
-   🔄 **Automatic Fallback System**: Seamless failover between providers for maximum reliability
-   📊 **Comprehensive Activity Tracking**: Database and file-based logging with audit trails
-   ⚡ **Laravel Queue Integration**: Background SMS processing for improved performance
-   🔔 **Laravel Notifications**: Native notification channel support with `toTextify()` method
-   🎯 **Fluent API**: Intuitive and chainable methods for developer-friendly experience
-   📱 **Smart Phone Number Validation**: Automatic formatting and validation for multiple countries
-   🎨 **Event-Driven Architecture**: Listen to SMS lifecycle events (sending, sent, failed)
-   ⚙️ **Highly Configurable**: Flexible configuration with environment variable support
-   🛡️ **Production Ready**: Built with enterprise-grade error handling and logging
-   🔧 **Extensible**: Easy custom provider integration

## � Table of Contents

-   [📡 Supported SMS Providers](#-supported-sms-providers)
-   [📦 Installation](#-installation)
-   [Configuration](#configuration)
-   [🚀 Quick Start](#-quick-start)
-   [🔔 Laravel Notifications](#-laravel-notifications)
-   [📋 Provider-Specific Usage](#-provider-specific-usage)
-   [📚 API Reference](#-api-reference)
-   [🔧 Advanced Usage](#-advanced-usage)
-   [Testing](#testing)
-   [Contributing](#contributing)

## �📡 Supported SMS Providers

### 🇧🇩 Bangladeshi Providers

| Provider       | Features                                         | Status   | Methods                  |
| -------------- | ------------------------------------------------ | -------- | ------------------------ |
| **DhorolaSMS** | GET API, Status tracking, SSL support            | ✅ Ready | `send()`, `getBalance()` |
| **BulkSMSBD**  | GET/POST API, Bulk sending, Plain text response  | ✅ Ready | `send()`, `getBalance()` |
| **MimSMS**     | Transactional/Promotional, Campaign support      | ✅ Ready | `send()`, `getBalance()` |
| **eSMS**       | Enterprise API, Bearer token auth, Cost tracking | ✅ Ready | `send()`                 |
| **REVE SMS**   | Premium gateway, Balance check, Multi-endpoint   | ✅ Ready | `send()`, `getBalance()` |
| **Alpha SMS**  | Dual format support, Balance check, SSL/Non-SSL  | ✅ Ready | `send()`, `getBalance()` |

### 🌍 International Providers

| Provider           | Features                                   | Status   | Installation                     | Methods                 |
| ------------------ | ------------------------------------------ | -------- | -------------------------------- | ----------------------- |
| **Twilio**         | Global leader, Advanced features, Webhooks | ✅ Ready | `composer require twilio/sdk`    | `send()`, Advanced APIs |
| **Nexmo (Vonage)** | International coverage, Client tracking    | ✅ Ready | `composer require vonage/client` | `send()`, Analytics     |

### 🛠️ Development & Testing Providers

| Provider           | Purpose             | Features                   |
| ------------------ | ------------------- | -------------------------- |
| **Log Provider**   | Development testing | Logs SMS to Laravel logs   |
| **Array Provider** | Unit testing        | Stores SMS in memory array |

> **Note**: International providers require additional SDK installation for full functionality. Development providers are included for testing purposes.

## 📦 Installation

Install the package via Composer:

```bash
composer require devwizardhq/laravel-textify
```

### 🔧 Laravel Auto-Discovery

Laravel will automatically register the service provider and facade. No additional configuration required!

### 📄 Publish Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="DevWizard\Textify\TextifyServiceProvider" --tag="textify-config"
```

### 🗄️ Optional: Database Activity Tracking

If you want to track SMS activities in your database:

```bash
php artisan textify:table
php artisan migrate
```

### 📋 Requirements

-   **PHP**: 8.3 or higher
-   **Laravel**: 10.0, 11.0, or 12.0
-   **Extensions**: cURL, JSON

## Configuration

### Timeout Configuration

Laravel Textify supports two types of timeout configurations for all SMS providers:

- **`timeout`**: Maximum time (in seconds) to wait for a response from the API (default: 30s)
- **`connect_timeout`**: Maximum time (in seconds) to wait for connection establishment (default: 10s)

These settings help ensure reliable SMS delivery by preventing indefinite waits when providers are slow or unreachable.

### Environment Variables

Add these to your `.env` file based on the providers you want to use:

```env
# Primary Provider Selection
TEXTIFY_PROVIDER=mimsms
TEXTIFY_FALLBACK_PROVIDER=revesms

# ===== BANGLADESHI PROVIDERS =====

# DhorolaSMS Configuration
DHOROLA_API_KEY=your_api_key
DHOROLA_SENDER_ID=your_sender_id
DHOROLA_BASE_URI=https://api.dhorolasms.net
DHOROLA_TIMEOUT=30
DHOROLA_CONNECT_TIMEOUT=10
DHOROLA_VERIFY_SSL=true

# BulkSMSBD Configuration
BULKSMSBD_API_KEY=your_api_key
BULKSMSBD_SENDER_ID=your_sender_id
BULKSMSBD_BASE_URI=http://bulksmsbd.net
BULKSMSBD_TIMEOUT=30
BULKSMSBD_CONNECT_TIMEOUT=10
BULKSMSBD_VERIFY_SSL=false

# MimSMS Configuration
MIMSMS_USERNAME=your_username
MIMSMS_APIKEY=your_api_key
MIMSMS_SENDER_ID=your_sender_id
MIMSMS_TRANSACTION_TYPE=T
MIMSMS_CAMPAIGN_ID=your_campaign_id
MIMSMS_BASE_URI=https://api.mimsms.com
MIMSMS_TIMEOUT=30
MIMSMS_CONNECT_TIMEOUT=10
MIMSMS_VERIFY_SSL=true

# eSMS Configuration
ESMS_API_TOKEN=your_api_token
ESMS_SENDER_ID=your_sender_id
ESMS_BASE_URI=https://login.esms.com.bd
ESMS_TIMEOUT=30
ESMS_CONNECT_TIMEOUT=10
ESMS_VERIFY_SSL=true

# REVE SMS Configuration
REVESMS_APIKEY=your_api_key
REVESMS_SECRETKEY=your_secret_key
REVESMS_CLIENT_ID=your_client_id
REVESMS_SENDER_ID=your_sender_id
REVESMS_BASE_URI=https://smpp.revesms.com:7790
REVESMS_BALANCE_URI=https://smpp.revesms.com
REVESMS_TIMEOUT=30
REVESMS_CONNECT_TIMEOUT=10
REVESMS_VERIFY_SSL=true

# Alpha SMS Configuration
ALPHASMS_API_KEY=your_api_key
ALPHASMS_SENDER_ID=your_sender_id
ALPHASMS_BASE_URI=https://api.sms.net.bd
ALPHASMS_TIMEOUT=30
ALPHASMS_CONNECT_TIMEOUT=10
ALPHASMS_VERIFY_SSL=true

# ===== INTERNATIONAL PROVIDERS =====

# Twilio Configuration
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM=your_phone_number

# Nexmo (Vonage) Configuration
NEXMO_API_KEY=your_api_key
NEXMO_API_SECRET=your_api_secret
NEXMO_FROM=your_sender_id
NEXMO_CLIENT_REF=your_reference
NEXMO_TIMEOUT=30
NEXMO_CONNECT_TIMEOUT=10
NEXMO_VERIFY_SSL=true

# ===== PACKAGE CONFIGURATION =====

# Activity Tracking
TEXTIFY_ACTIVITY_TRACKING_ENABLED=true
TEXTIFY_ACTIVITY_DRIVER=database

# Logging Configuration
TEXTIFY_LOGGING_ENABLED=true
TEXTIFY_LOG_SUCCESSFUL=true
TEXTIFY_LOG_FAILED=true
TEXTIFY_LOG_CHANNEL=stack

# Queue Configuration
TEXTIFY_QUEUE_ENABLED=true
TEXTIFY_QUEUE_CONNECTION=redis
TEXTIFY_QUEUE_NAME=sms
```

## 🚀 Quick Start

### Basic SMS Sending

```php
use DevWizard\Textify\Facades\Textify;

// Send a simple SMS
Textify::send('01712345678', 'Hello, this is a test message!');

// Send using specific driver
Textify::via('revesms')->send('01712345678', 'Hello from REVE SMS!');
```

### Fluent API

```php
// Chain methods for more control
Textify::to('01712345678')
    ->message('Your OTP is: 123456')
    ->via('mimsms')
    ->send();

// Send to multiple recipients
Textify::to(['01712345678', '01887654321'])
    ->message('Bulk SMS message')
    ->send();
```

### Laravel Notifications

```php
// Create and send SMS notifications
$user->notify(new OrderShippedNotification('ORD-123'));

// Or send to any phone number
Notification::route('textify', '01712345678')
    ->notify(new WelcomeNotification());
```

## 🔔 Laravel Notifications

Laravel Textify provides seamless integration with Laravel's notification system, allowing you to send SMS notifications just like email or database notifications.

### Quick Setup

1. **Add `textify` to your notification channels:**

```php
public function via($notifiable): array
{
    return ['textify']; // or ['mail', 'textify'] for multiple channels
}
```

2. **Implement the `toTextify()` method:**

```php
public function toTextify($notifiable): TextifyMessage
{
    return TextifyMessage::create('Your order has been shipped!');
}
```

3. **Configure phone number resolution in your model:**

```php
public function routeNotificationForTextify($notification): ?string
{
    return $this->phone_number;
}
```

### Complete Notification Example

```php
<?php

namespace App\Notifications;

use DevWizard\Textify\Notifications\TextifyMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderShippedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $orderNumber,
        public readonly string $trackingCode
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['textify'];
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toTextify($notifiable): TextifyMessage
    {
        return TextifyMessage::create(
            message: "🚚 Order #{$this->orderNumber} shipped! Track: {$this->trackingCode}",
            from: 'MyStore',
            driver: 'revesms'
        );
    }

    /**
     * Get the array representation for logging/database
     */
    public function toArray($notifiable): array
    {
        return [
            'order_number' => $this->orderNumber,
            'tracking_code' => $this->trackingCode,
        ];
    }
}
```

### Phone Number Resolution Methods

The notification channel looks for phone numbers in this priority order:

```php
class User extends Authenticatable
{
    use Notifiable;

    // Method 1: Route method (highest priority)
    // Gets notification context, most flexible
    public function routeNotificationForTextify($notification): ?string
    {
        // You can use notification context to determine logic
        if ($notification instanceof UrgentNotification) {
            return $this->emergency_phone ?? $this->phone_number;
        }

        return $this->phone_number;
    }

    // Method 2: Dynamic phone number method
    // Good for custom business logic
    public function getTextifyPhoneNumber(): ?string
    {
        return $this->preferred_sms_number ?? $this->phone_number ?? $this->mobile;
    }

    // Method 3: Automatic attribute detection (lowest priority)
    // Channel automatically checks these attributes:
    // phone_number, phone, mobile, phn, mobile_number, cell
}
```

### Message Format Options

The `toTextify()` method accepts multiple formats:

```php
// 1. TextifyMessage object (recommended for full control)
public function toTextify($notifiable): TextifyMessage
{
    return TextifyMessage::create(
        message: 'Your order has shipped!',
        from: 'MyStore',
        driver: 'revesms',
        metadata: ['order_id' => $this->orderId]
    );
}

// 2. Simple string (quick and easy)
public function toTextify($notifiable): string
{
    return "Order #{$this->orderNumber} has shipped!";
}

// 3. Array format (flexible structure)
public function toTextify($notifiable): array
{
    return [
        'message' => 'Your order has shipped!',
        'from' => 'MyStore',
        'driver' => 'revesms',
    ];
}
```

### Sending Notifications

```php
use App\Notifications\OrderShippedNotification;
use Illuminate\Support\Facades\Notification;

// Send to a single user
$user->notify(new OrderShippedNotification('ORD-123', 'TRK-456'));

// Send to multiple users
$users = User::where('wants_sms', true)->get();
Notification::send($users, new OrderShippedNotification('ORD-123', 'TRK-456'));

// Send to any phone number without a model
Notification::route('textify', '01712345678')
    ->notify(new WelcomeNotification());

// Queue the notification for background processing
$user->notify(
    (new OrderShippedNotification('ORD-123', 'TRK-456'))
        ->delay(now()->addMinutes(5))
);
```

### Advanced Features

#### Provider Selection

```php
public function toTextify($notifiable): TextifyMessage
{
    // Use different providers based on user preferences
    $provider = $notifiable->preferred_sms_provider ?? 'revesms';

    return TextifyMessage::create(
        message: 'Your notification',
        driver: $provider
    );
}
```

#### Conditional Sending

```php
public function via($notifiable): array
{
    $channels = ['database']; // Always log to database

    // Add SMS for users who opted in
    if ($notifiable->sms_notifications_enabled) {
        $channels[] = 'textify';
    }

    return $channels;
}
```

#### Event Integration

```php
use DevWizard\Textify\Events\TextifySent;
use DevWizard\Textify\Events\TextifyFailed;

Event::listen(TextifySent::class, function (TextifySent $event) {
    // Log successful SMS
    Log::info('SMS notification sent', [
        'recipient' => $event->message->getTo(),
        'message_id' => $event->response->getMessageId(),
    ]);
});

Event::listen(TextifyFailed::class, function (TextifyFailed $event) {
    // Handle SMS failures
    Log::error('SMS notification failed', [
        'recipient' => $event->message->getTo(),
        'error' => $event->response->getErrorMessage(),
    ]);
});
```

## 📋 Provider-Specific Usage

### 🇧🇩 Bangladeshi Providers

#### DhorolaSMS

```php
// Basic usage
Textify::via('dhorola')
    ->to('01712345678')
    ->message('Hello from DhorolaSMS!')
    ->send();

// Check balance
$balance = Textify::via('dhorola')->getBalance();
```

#### BulkSMSBD

```php
// Send SMS with custom sender ID
Textify::via('bulksmsbd')
    ->to('01712345678')
    ->from('CustomID')
    ->message('Hello from BulkSMSBD!')
    ->send();

// Check balance
$balance = Textify::via('bulksmsbd')->getBalance();
```

#### MimSMS

```php
// Transactional SMS
Textify::via('mimsms')
    ->to('01712345678')
    ->message('Your OTP: 123456')
    ->send();

// The transaction type is configured in .env (T=Transactional, P=Promotional)
```

#### eSMS

```php
// Enterprise SMS with cost tracking
$response = Textify::via('esms')
    ->to('01712345678')
    ->message('Enterprise message')
    ->send();

// Access cost information
$cost = $response->getCost();
```

#### REVE SMS

```php
// Premium SMS service
Textify::via('revesms')
    ->to('01712345678')
    ->message('Premium SMS via REVE')
    ->send();

// Check account balance
$balance = Textify::via('revesms')->getBalance();
echo "Balance: $balance";
```

#### Alpha SMS

```php
// Alpha SMS with SSL support
Textify::via('alphasms')
    ->to('01712345678')
    ->message('Hello from Alpha SMS!')
    ->send();

// Check balance
$balance = Textify::via('alphasms')->getBalance();
```

### 🌍 International Providers

#### Twilio

```php
// Requires: composer require twilio/sdk
Textify::via('twilio')
    ->to('+1234567890')
    ->message('Hello from Twilio!')
    ->send();
```

#### Nexmo (Vonage)

```php
// Requires: composer require vonage/client
Textify::via('nexmo')
    ->to('+1234567890')
    ->message('Hello from Vonage!')
    ->send();
```

### 🛠️ Development & Testing

#### Log Provider (Development)

```php
// Perfect for development - logs to Laravel logs
Textify::via('log')
    ->to('01712345678')
    ->message('This will be logged')
    ->send();
```

#### Array Provider (Testing)

```php
// Perfect for unit tests - stores in memory
Textify::via('array')
    ->to('01712345678')
    ->message('This will be stored in array')
    ->send();

// Access sent messages in tests
use DevWizard\Textify\Providers\ArrayProvider;
$messages = ArrayProvider::getMessages();
```

### Unified Send Method

The package provides a powerful unified send method that accepts various input formats:

```php
// Array format with different messages
Textify::send([
    ['to' => '01712345678', 'message' => 'Hello John!'],
    ['to' => '01887654321', 'message' => 'Hello Jane!'],
]);

// Same message to multiple numbers
Textify::send(['01712345678', '01887654321'], 'Same message for all');

// Single SMS
Textify::send('01712345678', 'Single SMS message');
```

### Queue Support

```php
// Send SMS in background
Textify::to('01712345678')
    ->message('Queued message')
    ->queue();

// Queue to specific queue
Textify::to('01712345678')
    ->message('Priority message')
    ->queue('high-priority');
```

### Event Handling

```php
use DevWizard\Textify\Events\TextifySent;
use DevWizard\Textify\Events\TextifyFailed;
use DevWizard\Textify\Events\TextifyJobFailed;
use Illuminate\Support\Facades\Event;

// Listen for SMS events
Event::listen(TextifySent::class, function (TextifySent $event) {
    logger('SMS sent successfully', [
        'to' => $event->message->getTo(),
        'provider' => $event->provider,
    ]);
});

Event::listen(TextifyFailed::class, function (TextifyFailed $event) {
    logger('SMS failed', [
        'to' => $event->message->getTo(),
        'error' => $event->exception?->getMessage() ?? $event->response->getErrorMessage(),
    ]);
});

// Listen for queued job failures
Event::listen(TextifyJobFailed::class, function (TextifyJobFailed $event) {
    logger('SMS job failed', [
        'to' => $event->getRecipient(),
        'provider' => $event->getProvider(),
        'error' => $event->getErrorMessage(),
        'metadata' => $event->getMetadata(),
    ]);

    // You could implement retry logic, alerting, etc.
});
```

### Balance Checking

Many Bangladeshi providers support balance checking:

```php
// REVE SMS - Balance check
$balance = Textify::via('revesms')->getBalance();
echo "Balance: $balance";

// DhorolaSMS - Balance check
$balance = Textify::via('dhorola')->getBalance();

// BulkSMSBD - Simple balance check
$balance = Textify::via('bulksmsbd')->getBalance();

// Alpha SMS - Balance verification
$balance = Textify::via('alphasms')->getBalance();

// MimSMS - Account balance
$balance = Textify::via('mimsms')->getBalance();
```

## 📚 API Reference

### Core Methods

#### `send(string|array $to, string $message = null): TextifyResponse`

Send SMS to one or multiple recipients.

```php
// Single recipient
Textify::send('01712345678', 'Hello World!');

// Multiple recipients with same message
Textify::send(['01712345678', '01887654321'], 'Same message');

// Multiple recipients with different messages
Textify::send([
    ['to' => '01712345678', 'message' => 'Hello John!'],
    ['to' => '01887654321', 'message' => 'Hello Jane!'],
]);
```

#### `via(string $driver): self`

Select specific SMS provider.

```php
Textify::via('revesms')->send('01712345678', 'Hello!');
```

#### `driver(string $driver): self`

Alias for `via()` method (Laravel Manager pattern compatibility).

```php
Textify::driver('revesms')->send('01712345678', 'Hello!');
```

#### `to(string|array $recipients): self`

Set recipient(s) using fluent API.

```php
Textify::to('01712345678')->message('Hello!')->send();
Textify::to(['01712345678', '01887654321'])->message('Bulk SMS')->send();
```

#### `message(string $message): self`

Set SMS message using fluent API.

```php
Textify::to('01712345678')->message('Your OTP: 123456')->send();
```

#### `from(string $senderId): self`

Set custom sender ID (if supported by provider).

```php
Textify::via('bulksmsbd')
    ->to('01712345678')
    ->from('MyApp')
    ->message('Hello!')
    ->send();
```

### Provider-Specific Methods

#### `getBalance(): float`

Check account balance (supported providers: revesms, dhorola, bulksmsbd, alphasms, mimsms).

```php
$balance = Textify::via('revesms')->getBalance();
echo "Current balance: $balance";
```

### Queue Methods

#### `queue(?string $queueName = null): mixed`

Send SMS via queue system.

```php
// Send immediately via queue
Textify::to('01712345678')->message('Queued SMS')->queue();

// Send to specific queue
Textify::to('01712345678')
    ->message('Priority SMS')
    ->queue('high-priority');
```

### Response Object

The `TextifyResponse` object provides access to sending results:

```php
$response = Textify::send('01712345678', 'Hello!');

// Check if SMS was sent successfully
if ($response->isSuccessful()) {
    echo "SMS sent! Message ID: " . $response->getMessageId();
} else {
    echo "Failed: " . $response->getErrorMessage();
}

// Available methods
$response->isSuccessful();        // bool - Check if SMS was sent successfully
$response->isFailed();            // bool - Check if SMS failed
$response->getMessageId();        // string|null - Get provider message ID
$response->getStatus();           // string - Get status message
$response->getCost();             // float|null - Get SMS cost (if supported)
$response->getErrorCode();        // string|null - Get error code
$response->getErrorMessage();     // string|null - Get error message
$response->getRawResponse();      // array - Get raw provider response
```

### Management Methods

#### `via(string $driver): self` / `driver(string $driver): self`

Select SMS provider (both methods are identical).

```php
// Using via()
Textify::via('revesms')->send('01712345678', 'Hello!');

// Using driver() (alias)
Textify::driver('revesms')->send('01712345678', 'Hello!');
```

#### `fallback(string $driver): self`

Set fallback provider for current operation.

```php
Textify::via('revesms')
    ->fallback('dhorola')
    ->send('01712345678', 'Message with fallback');
```

#### `getProviders(): array`

Get list of all registered providers.

```php
$providers = Textify::getProviders();
// Returns: ['dhorola', 'bulksmsbd', 'mimsms', 'esms', 'revesms', 'alphasms', ...]
```

#### `hasProvider(string $name): bool`

Check if a provider is registered.

```php
if (Textify::hasProvider('revesms')) {
    // Provider is available
}
```

#### `getProvider(string $name): TextifyProviderInterface`

Get provider instance directly.

```php
$provider = Textify::getProvider('revesms');
$balance = $provider->getBalance();
```

#### `reset(): self`

Clear prepared data from fluent interface.

```php
Textify::to('01712345678')->message('Test')->reset(); // Clears prepared data
```

### Configuration Methods

#### Available Providers

-   `dhorola` - DhorolaSMS
-   `bulksmsbd` - BulkSMSBD
-   `mimsms` - MimSMS
-   `esms` - eSMS
-   `revesms` - REVE SMS
-   `alphasms` - Alpha SMS
-   `twilio` - Twilio (requires SDK)
-   `nexmo` - Nexmo/Vonage (requires SDK)
-   `log` - Log provider (development)
-   `array` - Array provider (testing)

## 🔧 Advanced Usage

### Laravel Notifications Integration

> **📖 For comprehensive notification documentation, see the [Laravel Notifications](#-laravel-notifications) section above.**

Laravel Textify provides seamless integration with Laravel's notification system through the `textify` channel.

#### Setting Up Notification Channel

The `textify` notification channel is automatically registered when you install the package. No additional configuration is required.

#### Creating SMS Notifications

Create a notification class that uses the `textify` channel:

```php
<?php

namespace App\Notifications;

use DevWizard\Textify\Notifications\TextifyMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderShippedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $orderNumber,
        public readonly string $trackingCode
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['textify']; // Add 'textify' to send SMS
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toTextify($notifiable): TextifyMessage
    {
        return TextifyMessage::create(
            message: "Your order #{$this->orderNumber} has been shipped! Tracking: {$this->trackingCode}",
            from: 'MyStore',
            driver: 'revesms' // Optional: specify SMS provider
        );
    }
}
```

#### Configuring Notifiable Models

Add SMS routing to your User model (or any notifiable model). You have multiple options:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    // Option 1: Route method (most specific, gets notification context)
    public function routeNotificationForTextify($notification): ?string
    {
        // You can use notification context to determine the phone number
        return $this->phone_number;
    }

    // Option 2: Dynamic getTextifyPhoneNumber method (custom logic)
    public function getTextifyPhoneNumber(): ?string
    {
        // Custom logic to determine phone number
        // Example: Use different numbers based on user preferences
        return $this->preferred_sms_number ?? $this->phone_number ?? $this->mobile;
    }

    // Option 3: Automatic attribute detection
    // If neither method above is defined, the channel will automatically
    // look for these attributes in this order:
    // phone_number, phone, phn, mobile, cell, mobile_number
}
```

**Priority Order:**

1. `routeNotificationForTextify($notification)` - highest priority
2. `textifyNumber()` - medium priority
3. Attribute detection - lowest priority

**Advanced textifyNumber() Examples:**

```php
class User extends Authenticatable
{
    use Notifiable;

    // Example 1: Use different numbers based on notification type
    public function textifyNumber(): ?string
    {
        // Use work phone during business hours, personal phone otherwise
        $now = now();
        if ($now->isWeekday() && $now->hour >= 9 && $now->hour <= 17) {
            return $this->work_phone ?? $this->phone_number;
        }
        return $this->personal_phone ?? $this->phone_number;
    }

    // Example 2: Format phone number dynamically
    public function textifyNumber(): ?string
    {
        $phone = $this->phone_number;

        // Ensure Bangladeshi format
        if ($phone && !str_starts_with($phone, '880')) {
            $phone = '880' . ltrim($phone, '0+');
        }

        return $phone;
    }

    // Example 3: Use notification preferences
    public function textifyNumber(): ?string
    {
        // Check if user has SMS notifications enabled
        if (!$this->sms_notifications_enabled) {
            return null; // Will prevent SMS from being sent
        }

        return $this->preferred_contact_number ?? $this->phone_number;
    }
}
```

````

#### Sending Notifications

Send SMS notifications like any other Laravel notification:

```php
use App\Notifications\OrderShippedNotification;

// Send to a single user
$user->notify(new OrderShippedNotification('ORD-123', 'TRK-456'));

// Send to multiple users
Notification::send($users, new OrderShippedNotification('ORD-123', 'TRK-456'));

// Queue the notification for background processing
$user->notify((new OrderShippedNotification('ORD-123', 'TRK-456'))->delay(now()->addMinutes(5)));
````

#### Notification Message Formats

The `toTextify()` method supports multiple return formats:

```php
// 1. TextifyMessage object (recommended)
public function toTextify($notifiable): TextifyMessage
{
    return TextifyMessage::create(
        message: 'Your order has shipped!',
        from: 'MyStore',
        driver: 'revesms'
    );
}

// 2. Simple string
public function toTextify($notifiable): string
{
    return 'Your order has shipped!';
}

// 3. Array format
public function toTextify($notifiable): array
{
    return [
        'message' => 'Your order has shipped!',
        'from' => 'MyStore',
        'driver' => 'revesms',
    ];
}
```

#### On-Demand Notifications

Send SMS to any phone number without a model:

```php
use Illuminate\Support\Facades\Notification;

Notification::route('textify', '01712345678')
    ->notify(new OrderShippedNotification('ORD-123', 'TRK-456'));
```

#### Event Integration

SMS notifications integrate with Laravel's event system and Textify's own events:

```php
use DevWizard\Textify\Events\TextifySent;
use DevWizard\Textify\Events\TextifyFailed;

Event::listen(TextifySent::class, function (TextifySent $event) {
    logger('Notification SMS sent', [
        'recipient' => $event->message->getTo(),
        'provider' => $event->provider,
    ]);
});
```

### Custom Providers

Create your own SMS provider by extending the base provider:

```php
use DevWizard\Textify\Providers\BaseProvider;
use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;

class CustomSmsProvider extends BaseProvider
{
    protected array $supportedCountries = ['BD', 'US']; // Supported country codes

    public function getName(): string
    {
        return 'custom';
    }

    protected function getRequiredConfigKeys(): array
    {
        return ['api_key', 'sender_id'];
    }

    protected function validateConfig(): void
    {
        $this->ensureConfigKeys();
    }

    protected function getClientConfig(): array
    {
        return [
            'base_uri' => 'https://api.customsms.com',
            'timeout' => 30,
        ];
    }

    protected function sendRequest(TextifyMessage $message): array
    {
        $response = $this->client->post('/send', [
            'json' => [
                'api_key' => $this->config['api_key'],
                'to' => $message->getTo(),
                'from' => $message->getFrom() ?: $this->config['sender_id'],
                'message' => $message->getMessage(),
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function parseResponse(array $response): TextifyResponse
    {
        if ($response['status'] === 'success') {
            return TextifyResponse::success(
                messageId: $response['message_id'],
                status: 'sent',
                cost: $response['cost'] ?? null
            );
        }

        return TextifyResponse::failed(
            errorCode: $response['error_code'],
            errorMessage: $response['error_message']
        );
    }
}
```

Then register it in your `config/textify.php`:

```php
'providers' => [
    'custom' => [
        'driver' => 'custom',
        'class' => App\Services\CustomSmsProvider::class,
        'api_key' => env('CUSTOM_SMS_API_KEY'),
        'sender_id' => env('CUSTOM_SMS_SENDER_ID'),
    ],
],
```

### Activity Tracking & Analytics

Track SMS activities with detailed logging:

```php
use DevWizard\Textify\Models\TextifyActivity;

// Activities are automatically tracked when enabled in config
$activities = TextifyActivity::latest()->get();

foreach ($activities as $activity) {
    echo "SMS to {$activity->to}: {$activity->status} at {$activity->created_at}";
}

// Filter by status
$failedSms = TextifyActivity::where('status', 'failed')->get();
$successfulSms = TextifyActivity::where('status', 'sent')->get();

// Filter by provider
$reveSms = TextifyActivity::where('provider', 'revesms')->get();

// Filter by date range
$todaySms = TextifyActivity::whereDate('created_at', today())->get();
```

### Event-Driven Architecture

Listen to SMS lifecycle events:

```php
use DevWizard\Textify\Events\{TextifySending, TextifySent, TextifyFailed, TextifyJobFailed};

// In your EventServiceProvider
protected $listen = [
    TextifySending::class => [
        SendingSmsListener::class,
    ],
    TextifySent::class => [
        SmsSuccessListener::class,
    ],
    TextifyFailed::class => [
        SmsFailureListener::class,
    ],
    TextifyJobFailed::class => [
        QueueJobFailureListener::class,
    ],
];

// Example listeners
class SmsSuccessListener
{
    public function handle(TextifySent $event)
    {
        // Log successful SMS
        logger('SMS sent successfully', [
            'to' => $event->message->getTo(),
            'provider' => $event->provider,
            'message_id' => $event->response->getMessageId(),
            'cost' => $event->response->getCost(),
        ]);

        // Update user notification status
        // Send webhook to external service
        // Update analytics dashboard
    }
}

class QueueJobFailureListener
{
    public function handle(TextifyJobFailed $event)
    {
        // Log job failure with detailed metadata
        logger('SMS queue job failed', $event->getMetadata());

        // Implement retry logic
        if ($this->shouldRetry($event)) {
            // Retry with different provider or after delay
            dispatch(new SendTextifyJob($event->getMessage(), 'fallback-provider'))
                ->delay(now()->addMinutes(5));
        }

        // Send alert to administrators
        // Update monitoring dashboard
    }
}
}
```

### Fallback System

Configure fallback drivers in your config file for maximum reliability:

```php
// config/textify.php
'fallback' => env('TEXTIFY_FALLBACK_PROVIDER', 'revesms'),

// Or in your .env file
TEXTIFY_FALLBACK_PROVIDER=revesms

// Multiple fallbacks can be configured by modifying the config file:
'providers' => [
    // Your primary providers...
],

// Custom fallback logic in your application
$primaryProviders = ['mimsms', 'revesms', 'alphasms'];
$fallbackProviders = ['dhorola', 'bulksmsbd', 'esms'];

foreach ($primaryProviders as $provider) {
    try {
        $response = Textify::via($provider)->send($phone, $message);
        if ($response->isSuccessful()) {
            break;
        }
    } catch (Exception $e) {
        // Try next provider
        continue;
    }
}
```

When the primary driver fails, the system will automatically try the fallback drivers in order.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [IQBAL HASAN](https://github.com/devwizardhq)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
