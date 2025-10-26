# Changelog

All notable changes to `laravel-textify` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## v1.3.1 - 2025-10-26

### What's Changed

* build(deps): Bump stefanzweifel/git-auto-commit-action from 6 to 7 by @dependabot[bot] in https://github.com/DevWizardHQ/laravel-textify/pull/7

**Full Changelog**: https://github.com/DevWizardHQ/laravel-textify/compare/v1.3.0...v1.3.1

## v1.3.0 - 2025-09-09

### What's Added

- **Message Validation System**: Comprehensive message validation with configurable rules
  - Laravel-style validation configuration (`required`, `min`, `max`)
  - Support for nullable messages via `TEXTIFY_MESSAGE_REQUIRED=false`
  - Configurable minimum and maximum message length
  - Proper validation error messages
  

### What's Enhanced

- **Error Handling**: Improved error mapping and messages for all Bangladeshi SMS providers
  - ReveSmsProvider: Enhanced error code mapping including error 114 handling
  - AlphaSmsProvider: Better error message descriptions
  - EsmsProvider: Improved error handling and status mapping
  - DhorolaSmsProvider: Enhanced error code explanations
  - MimSmsProvider: Better API response parsing
  - BulkSmsBdProvider: Comprehensive error code mapping
  

### What's Fixed

- **ReveSMS Error 114**: "Content not provided" error now prevented by pre-send validation
- **Empty Message Handling**: Consistent behavior across all providers
- **Whitespace Messages**: Properly validates and rejects whitespace-only messages

### What's Technical

- Added `validateMessageContent()` method to BaseProvider
- Enhanced BaseProvider with proper phone number validation methods
- Comprehensive test suite with 6 test cases
- PHPStan validation passed
- Backward compatible configuration

**Full Changelog**: https://github.com/DevWizardHQ/laravel-textify/compare/v1.2.0...v1.3.0

## v1.2.0 - 2025-09-07

### What's Changed

#### 🚀 New Features

* **feat: add connect_timeout configuration option**
  - Added `connect_timeout` option to all SMS provider configurations
  - Updated `BaseProvider` to use `connect_timeout` in HTTP client config
  - Added comprehensive test coverage for `connect_timeout` functionality
  - Updated provider configuration documentation
  - Default `connect_timeout` is 10 seconds, configurable per provider
  

#### ⚡ Enhanced Provider Configuration

* **Timeout Configuration:** Providers now support two distinct timeout settings:
  - `timeout`: Maximum time (in seconds) to wait for a response from the API (default: 30s)
  - `connect_timeout`: Maximum time (in seconds) to wait for connection establishment (default: 10s)
  

#### 📋 Updated Providers

All SMS providers now support the new `connect_timeout` configuration:

- Dhorola SMS: `DHOROLA_CONNECT_TIMEOUT` (default: 10s)
- BulkSMSBD: `BULKSMSBD_CONNECT_TIMEOUT` (default: 10s)
- MimSMS: `MIMSMS_CONNECT_TIMEOUT` (default: 10s)
- eSMS: `ESMS_CONNECT_TIMEOUT` (default: 10s)
- REVE SMS: `REVESMS_CONNECT_TIMEOUT` (default: 10s)
- Alpha SMS: `ALPHASMS_CONNECT_TIMEOUT` (default: 10s)
- Nexmo: `NEXMO_CONNECT_TIMEOUT` (default: 10s)

#### 🧪 Testing

* Added `ConnectTimeoutTest` with comprehensive test coverage
* Tests verify proper default values and configuration handling
* All existing tests continue to pass

### Breaking Changes

None. This is a backward-compatible feature addition.

### Migration Guide

To use the new connect_timeout feature, add the appropriate environment variable to your `.env` file:

```env
# Example for Dhorola SMS provider
DHOROLA_CONNECT_TIMEOUT=15

# Or for any other provider
BULKSMSBD_CONNECT_TIMEOUT=8
MIMSMS_CONNECT_TIMEOUT=12



```
**Full Changelog**: https://github.com/DevWizardHQ/laravel-textify/compare/v1.1.2...v1.2.0

## v1.1.2 - 2025-08-26

### What's Changed

* build(deps): Bump actions/checkout from 4 to 5 by @dependabot[bot] in https://github.com/DevWizardHQ/laravel-textify/pull/2
* fix: set SSL verification to false by default to prevent connection timeouts (#3) by @iqbalhasandev in https://github.com/DevWizardHQ/laravel-textify/pull/4

### New Contributors

* @iqbalhasandev made their first contribution in https://github.com/DevWizardHQ/laravel-textify/pull/4

**Full Changelog**: https://github.com/DevWizardHQ/laravel-textify/compare/v1.1.1...v1.1.2

## v1.1.1 - 2025-08-04

### What we change:

- Refactor TextifyMessage class to use 'self' instead of 'static' for return types and update phpstan baseline configuration

**Full Changelog**: https://github.com/DevWizardHQ/laravel-textify/compare/v1.1.0...v1.1.1

## v1.1.0 - 2025-08-04

feat: add Laravel notification channel with comprehensive SMS integration

🔔 MAJOR FEATURE: Laravel Notifications Integration

This commit introduces complete Laravel notification system integration, allowing developers to send SMS notifications using the 'textify' channel alongside mail, database, and other Laravel notification channels.

### New Features Added:

#### 1. TextifyChannel (src/Channels/TextifyChannel.php)

- Full Laravel notification channel implementation
  
- Smart phone number resolution with 3-tier priority system:
  
  1. routeNotificationForTextify() method (notification context aware)
  2. getTextifyPhoneNumber() method (custom business logic)
  3. Automatic attribute detection (phone_number, phone, mobile, phn, cell, mobile_number)
  
- Support for multiple message formats (TextifyMessage object, string, array)
  
- Provider/driver selection per notification
  
- Custom sender ID support
  
- Comprehensive error handling and validation
  

#### 2. TextifyMessage DTO (src/Notifications/TextifyMessage.php)

- Immutable data structure for SMS notifications
- Fluent API for easy message construction
- Support for message, sender ID, driver/provider, and metadata
- Factory method pattern with TextifyMessage::create()
- Seamless integration with notification channel

#### 3. Service Provider Integration (src/TextifyServiceProvider.php)

- Automatic notification channel registration
- Integration with Laravel's ChannelManager
- No additional configuration required - works out of the box

#### 4. Comprehensive Test Suite (tests/NotificationChannelTest.php)

- 9 test cases covering all notification scenarios
- Phone number resolution priority testing
- Multiple message format validation
- Error handling verification
- Direct channel usage testing
- Edge case coverage

#### 5. Usage Examples (examples/NotificationExamples.php)

- 5 real-world notification examples
- Order notifications, OTP, marketing, emergency alerts
- User model integration examples
- Advanced notification patterns
- Best practices and conventions

#### 6. Enhanced Documentation (README.md)

- Complete Laravel Notifications section with table of contents
- Step-by-step setup guide
- Phone number resolution methods documentation
- Message format examples
- Event integration patterns
- Advanced usage scenarios
- Configuration examples

**Full Changelog**: https://github.com/DevWizardHQ/laravel-textify/compare/v1.0.1...v1.1.0

## V1.0.1 - 2025-08-03

### 🐛 Bug Fixes

- **Fixed Critical Queue Bug**: Fixed `queue()` method incorrectly handling multiple contacts by only processing the first contact (`[0]`)
- **Improved Queue Functionality**: Now properly handles arrays of contacts, creating separate jobs for each recipient
- **Fixed CI Compatibility**: Removed `describe()` blocks from tests for better CI environment compatibility

### ✨ New Features

- **Added TextifyJobFailed Event**: New event dispatched when queued SMS jobs fail, providing better error tracking
- **Enhanced Queue Error Handling**: Improved error logging and event dispatching for failed queue jobs

### 🔧 Configuration Cleanup

- **Removed Unused Config**: Cleaned up configuration file by removing unused sections (`queue`, `validation`, `rate_limiting`)
- **Updated Activity Tracking Default**: Changed `TEXTIFY_ACTIVITY_TRACKING_ENABLED` default to `false` for opt-in behavior
- **Streamlined Config**: Configuration now only includes implemented features for better clarity

### 📚 Documentation Updates

- **Updated README**: Fixed activity tracking default value documentation
- **Enhanced Event Documentation**: Added documentation for new `TextifyJobFailed` event

**Full Changelog**: https://github.com/DevWizardHQ/laravel-textify/compare/v1.0.0...v1.0.1

## [1.0.1] - 2025-08-03

### 🐛 Bug Fixes

- **Fixed Critical Queue Bug**: Fixed `queue()` method incorrectly handling multiple contacts by only processing the first contact (`[0]`)
- **Improved Queue Functionality**: Now properly handles arrays of contacts, creating separate jobs for each recipient
- **Fixed CI Compatibility**: Removed `describe()` blocks from tests for better CI environment compatibility

### ✨ New Features

- **Added TextifyJobFailed Event**: New event dispatched when queued SMS jobs fail, providing better error tracking
- **Enhanced Queue Error Handling**: Improved error logging and event dispatching for failed queue jobs

### 🔧 Configuration Cleanup

- **Removed Unused Config**: Cleaned up configuration file by removing unused sections (`queue`, `validation`, `rate_limiting`)
- **Updated Activity Tracking Default**: Changed `TEXTIFY_ACTIVITY_TRACKING_ENABLED` default to `false` for opt-in behavior
- **Streamlined Config**: Configuration now only includes implemented features for better clarity

### 📚 Documentation Updates

- **Updated README**: Fixed activity tracking default value documentation
- **Enhanced Event Documentation**: Added documentation for new `TextifyJobFailed` event

## [1.0.0] - 2025-08-03

### 🎉 Initial Release

Laravel Textify v1.0.0 is a comprehensive, enterprise-ready SMS package for Laravel applications with support for multiple SMS providers, automatic fallback, queue integration, and extensive monitoring capabilities.

### ✨ Core Features

#### 📱 Multi-Provider SMS Support

- **8+ SMS Providers** with unified API
- **6 Bangladeshi Providers**: DhorolaSMS, BulkSMSBD, MimSMS, eSMS, REVE SMS, Alpha SMS
- **2 International Providers**: Twilio, Nexmo/Vonage (with optional SDK dependencies)
- **2 Development Providers**: Log (development), Array (testing)

#### 🚀 Developer Experience

- **Fluent API**: Chainable methods for intuitive SMS composition
- **Unified Send Interface**: Multiple input formats support
- **Laravel Integration**: Service provider, facades, auto-discovery
- **Type Safety**: Full PHP 8.3+ type declarations with comprehensive DocBlocks

#### 🔄 Reliability & Performance

- **Automatic Fallback System**: Seamless failover between providers
- **Laravel Queue Integration**: Background SMS processing for high-volume sending
- **Connection Pooling**: Efficient HTTP client management
- **Retry Mechanisms**: Configurable retry logic for failed requests

#### 📊 Monitoring & Analytics

- **Activity Tracking**: Database and file-based SMS activity logging
- **Balance Checking**: Real-time account balance for supported providers
- **Event System**: Laravel events for SMS lifecycle (TextifySending, TextifySent, TextifyFailed)
- **Comprehensive Logging**: Debug and audit logging with configurable channels

#### 🛡️ Security & Validation

- **Phone Number Validation**: Country-specific validation and formatting
- **Input Sanitization**: Comprehensive input validation and sanitization
- **SSL/TLS Support**: Secure API communications with verification options
- **API Key Management**: Secure credential handling via environment variables

### 🏗️ Technical Architecture

#### Core Components

- **BaseProvider**: Abstract base class for all SMS providers
- **TextifyManager**: Central manager for provider orchestration
- **TextifyMessage DTO**: Structured message representation
- **TextifyResponse DTO**: Standardized response handling
- **ActivityTracker**: Comprehensive activity tracking system

#### Laravel Integration

- **Service Provider**: `TextifyServiceProvider` with automatic discovery
  
- **Facade**: `Textify` facade for easy access
  
- **Artisan Commands**:
  
  - `textify:table` - Publish database migration for activity tracking
  - `textify:test` - Test SMS configuration and provider connectivity
  
- **Configuration Publishing**: Customizable config via `vendor:publish`
  

#### Provider Architecture

- **Interface-Driven Design**: `TextifyProviderInterface` for consistency
- **Factory Pattern**: `ActivityTrackerFactory`, `TextifyLoggerFactory`
- **Custom Provider Support**: Easy extension system for adding new providers
- **Provider Health Monitoring**: Built-in health checks and status monitoring

### 📋 Provider Details

#### 🇧🇩 Bangladeshi Providers

| Provider       | Features             | Authentication               | Special Features                   |
| -------------- | -------------------- | ---------------------------- | ---------------------------------- |
| **DhorolaSMS** | GET API, SSL support | API Key                      | Status tracking, balance check     |
| **BulkSMSBD**  | GET/POST API         | API Key                      | Bulk sending, plain text responses |
| **MimSMS**     | Campaign support     | Username + API Key           | Transactional/Promotional modes    |
| **eSMS**       | Enterprise API       | Bearer Token                 | Cost tracking, enterprise features |
| **REVE SMS**   | Premium gateway      | API Key + Secret + Client ID | Multi-endpoint, balance check      |
| **Alpha SMS**  | Flexible API         | API Key                      | Dual format support, balance check |

#### 🌍 International Providers

| Provider   | Features                                   | Requirements             | Installation                     |
| ---------- | ------------------------------------------ | ------------------------ | -------------------------------- |
| **Twilio** | Global leader, webhooks, advanced features | Account SID + Auth Token | `composer require twilio/sdk`    |
| **Nexmo**  | International coverage, analytics          | API Key + Secret         | `composer require vonage/client` |

### ⚙️ Configuration & Setup

#### Environment Variables

- **50+ Configuration Options** across all providers
- **Provider-Specific Settings**: Individual configuration for each provider
- **SSL/Timeout Settings**: Configurable security and performance options
- **Queue Configuration**: Background processing settings
- **Activity Tracking**: Comprehensive tracking options

#### Database Support

- **Migration Support**: Optional database migration for activity tracking
- **Eloquent Model**: `TextifyActivity` model for SMS analytics
- **Query Builder**: Rich querying capabilities for SMS data

### 🧪 Testing & Quality

#### Testing Infrastructure

- **Comprehensive Test Suite**: Unit, integration, and architecture tests
- **Provider Mocking**: Reliable testing without API calls
- **Array Provider**: In-memory testing without external dependencies
- **Test Coverage**: Extensive coverage for all components

#### Code Quality

- **PHPStan Level 9**: Maximum static analysis coverage
- **Laravel Pint**: Automatic code formatting
- **Architecture Testing**: Ensures clean architecture principles
- **PSR Compliance**: Follows PHP standards and Laravel conventions

### 🔧 Advanced Features

#### Queue Integration

- **Job Classes**: `SendTextifyJob` for background processing
- **Configurable Queues**: Custom queue connections and names
- **Delayed Sending**: Schedule SMS for future delivery
- **Bulk Processing**: Efficient handling of large SMS batches

#### Event System

- **Lifecycle Events**: Complete SMS lifecycle tracking
- **Custom Listeners**: Easy integration with existing application events
- **Webhook Support**: Built-in webhook handling capabilities
- **Analytics Integration**: Easy integration with analytics platforms

#### Extensibility

- **Custom Providers**: Step-by-step guide for adding new providers
- **Plugin Architecture**: Extensible design for additional features
- **Middleware Support**: Request/response middleware capabilities
- **Hook System**: Pre/post-send hooks for custom logic

### 📊 Logging & Monitoring

#### Activity Tracking Options

- **Database Storage**: Persistent storage with Eloquent models
- **File Storage**: JSON-based file logging
- **Null Storage**: Disable tracking for performance-critical applications

#### Logging Capabilities

- **Success/Failure Logging**: Configurable logging for different outcomes
- **Provider-Specific Logs**: Detailed logs for each provider
- **Laravel Log Integration**: Uses Laravel's logging infrastructure
- **Custom Log Channels**: Configurable log channels for different environments

### 🚀 Performance Optimizations

#### Efficiency Features

- **Connection Reuse**: HTTP client connection pooling
- **Batch Processing**: Efficient bulk SMS handling
- **Memory Management**: Optimized memory usage for large operations
- **Caching Support**: Built-in caching for provider configurations

#### Scalability

- **Queue Support**: Horizontal scaling via queue workers
- **Provider Load Balancing**: Distribute load across multiple providers
- **Rate Limiting**: Built-in rate limiting to respect provider limits
- **Monitoring Integration**: Ready for APM and monitoring tools

### 📚 Documentation

#### Comprehensive Documentation

- **README**: Detailed usage examples and configuration guides
- **API Reference**: Complete method documentation
- **Provider Guides**: Setup instructions for each provider
- **Best Practices**: Production deployment recommendations
- **Troubleshooting**: Common issues and solutions

### 🔄 Future-Ready

#### Planned Features

- **Webhook Handling**: Incoming SMS and delivery receipt processing
- **Template System**: SMS template management
- **A/B Testing**: Built-in A/B testing for SMS campaigns
- **Analytics Dashboard**: Web interface for SMS analytics
- **Multi-tenancy**: Support for multi-tenant applications

### 📈 Compatibility

#### Requirements

- **PHP**: 8.3 or higher
- **Laravel**: 10.x, 11.x, 12.x
- **Dependencies**: GuzzleHTTP 7.x, Spatie Laravel Package Tools

#### Optional Dependencies

- **Twilio SDK**: For advanced Twilio features
- **Vonage SDK**: For Nexmo/Vonage advanced features
- **Queue Drivers**: Redis, Database, or other Laravel-supported queues


---

This initial release establishes Laravel Textify as a comprehensive, production-ready SMS solution for Laravel applications with enterprise-grade features, extensive provider support, and a focus on developer experience and reliability.
