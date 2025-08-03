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
-   🎯 **Fluent API**: Intuitive and chainable methods for developer-friendly experience
-   📱 **Smart Phone Number Validation**: Automatic formatting and validation for multiple countries
-   🎨 **Event-Driven Architecture**: Listen to SMS lifecycle events (sending, sent, failed)
-   ⚙️ **Highly Configurable**: Flexible configuration with environment variable support
-   🛡️ **Production Ready**: Built with enterprise-grade error handling and logging
-   🔧 **Extensible**: Easy custom provider integration

## 📡 Supported SMS Providers

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

## Configuration

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
DHOROLA_VERIFY_SSL=true

# BulkSMSBD Configuration
BULKSMSBD_API_KEY=your_api_key
BULKSMSBD_SENDER_ID=your_sender_id
BULKSMSBD_BASE_URI=http://bulksmsbd.net
BULKSMSBD_TIMEOUT=30
BULKSMSBD_VERIFY_SSL=false

# MimSMS Configuration
MIMSMS_USERNAME=your_username
MIMSMS_APIKEY=your_api_key
MIMSMS_SENDER_ID=your_sender_id
MIMSMS_TRANSACTION_TYPE=T
MIMSMS_CAMPAIGN_ID=your_campaign_id
MIMSMS_BASE_URI=https://api.mimsms.com
MIMSMS_TIMEOUT=30
MIMSMS_VERIFY_SSL=true

# eSMS Configuration
ESMS_API_TOKEN=your_api_token
ESMS_SENDER_ID=your_sender_id
ESMS_BASE_URI=https://login.esms.com.bd
ESMS_TIMEOUT=30
ESMS_VERIFY_SSL=true

# REVE SMS Configuration
REVESMS_APIKEY=your_api_key
REVESMS_SECRETKEY=your_secret_key
REVESMS_CLIENT_ID=your_client_id
REVESMS_SENDER_ID=your_sender_id
REVESMS_BASE_URI=https://smpp.revesms.com:7790
REVESMS_BALANCE_URI=https://smpp.revesms.com
REVESMS_TIMEOUT=30
REVESMS_VERIFY_SSL=true

# Alpha SMS Configuration
ALPHASMS_API_KEY=your_api_key
ALPHASMS_SENDER_ID=your_sender_id
ALPHASMS_BASE_URI=https://api.sms.net.bd
ALPHASMS_TIMEOUT=30
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
