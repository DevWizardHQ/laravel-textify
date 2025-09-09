<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default SMS Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default SMS "provider" that will be used on
    | requests. You may set this to any of the providers defined in the
    | "providers" array below.
    |
    | Supported: "log", "array", "dhorola", "bulksmsbd", "mimsms", "esms",
    | "revesms", "alphasms", "twilio", "nexmo",
    |
    */

    'default' => env('TEXTIFY_PROVIDER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Fallback SMS Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the fallback SMS "provider" that will be used when
    | the primary provider fails. Set to null to disable fallback.
    |
    */

    'fallback' => env('TEXTIFY_FALLBACK_PROVIDER'),

    /*
    |--------------------------------------------------------------------------
    | SMS Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the SMS "providers" for your application
    | plus their respective settings. Several examples have been configured
    | for you and you are free to add your own as needed.
    |
    | Timeout Configuration:
    | - timeout: Maximum time (in seconds) to wait for a response from the API
    | - connect_timeout: Maximum time (in seconds) to wait for connection establishment
    |
    | To add a custom provider, create a class extending TextifyProvider and
    | add it to this array:
    |
    | 'my-custom' => [
    |     'driver' => 'my-custom',
    |     'class' => App\Services\MySmsProvider::class,
    |     'api_key' => env('MY_CUSTOM_API_KEY'),
    |     'api_secret' => env('MY_CUSTOM_API_SECRET'),
    |     'timeout' => env('MY_CUSTOM_TIMEOUT', 30),
    |     'connect_timeout' => env('MY_CUSTOM_CONNECT_TIMEOUT', 10),
    |     // other configuration...
    | ],
    |
    | See docs/CUSTOM_PROVIDERS.md for detailed instructions.
    |
    */

    'providers' => [
        'log' => [
            'driver' => 'log',
            'channel' => env('TEXTIFY_LOG_CHANNEL', 'single'),
        ],

        'array' => [
            'driver' => 'array',
        ],

        'dhorola' => [
            'driver' => 'dhorola',
            'api_key' => env('DHOROLA_API_KEY'),
            'sender_id' => env('DHOROLA_SENDER_ID'),
            'base_uri' => env('DHOROLA_BASE_URI', 'https://api.dhorolasms.net'),
            'timeout' => env('DHOROLA_TIMEOUT', 30),
            'connect_timeout' => env('DHOROLA_CONNECT_TIMEOUT', 10),
            'verify_ssl' => env('DHOROLA_VERIFY_SSL', false),
        ],

        'bulksmsbd' => [
            'driver' => 'bulksmsbd',
            'api_key' => env('BULKSMSBD_API_KEY'),
            'sender_id' => env('BULKSMSBD_SENDER_ID'),
            'base_uri' => env('BULKSMSBD_BASE_URI', 'http://bulksmsbd.net'),
            'timeout' => env('BULKSMSBD_TIMEOUT', 30),
            'connect_timeout' => env('BULKSMSBD_CONNECT_TIMEOUT', 10),
            'verify_ssl' => env('BULKSMSBD_VERIFY_SSL', false),
        ],

        'mimsms' => [
            'driver' => 'mimsms',
            'username' => env('MIMSMS_USERNAME'),
            'apikey' => env('MIMSMS_APIKEY'),
            'sender_id' => env('MIMSMS_SENDER_ID'),
            'transaction_type' => env('MIMSMS_TRANSACTION_TYPE', 'T'), // T=Transactional, P=Promotional, D=Dynamic
            'campaign_id' => env('MIMSMS_CAMPAIGN_ID'),
            'base_uri' => env('MIMSMS_BASE_URI', 'https://api.mimsms.com'),
            'timeout' => env('MIMSMS_TIMEOUT', 30),
            'connect_timeout' => env('MIMSMS_CONNECT_TIMEOUT', 10),
            'verify_ssl' => env('MIMSMS_VERIFY_SSL', false),
        ],

        'esms' => [
            'driver' => 'esms',
            'api_token' => env('ESMS_API_TOKEN'),
            'sender_id' => env('ESMS_SENDER_ID'),
            'base_uri' => env('ESMS_BASE_URI', 'https://login.esms.com.bd'),
            'timeout' => env('ESMS_TIMEOUT', 30),
            'connect_timeout' => env('ESMS_CONNECT_TIMEOUT', 10),
            'verify_ssl' => env('ESMS_VERIFY_SSL', false),
        ],

        'revesms' => [
            'driver' => 'revesms',
            'apikey' => env('REVESMS_APIKEY'),
            'secretkey' => env('REVESMS_SECRETKEY'),
            'client_id' => env('REVESMS_CLIENT_ID'),
            'sender_id' => env('REVESMS_SENDER_ID'),
            'base_uri' => env('REVESMS_BASE_URI', 'https://smpp.revesms.com:7790'),
            'balance_uri' => env('REVESMS_BALANCE_URI', 'https://smpp.revesms.com'),
            'timeout' => env('REVESMS_TIMEOUT', 30),
            'connect_timeout' => env('REVESMS_CONNECT_TIMEOUT', 10),
            'verify_ssl' => env('REVESMS_VERIFY_SSL', false),
        ],

        'alphasms' => [
            'driver' => 'alphasms',
            'api_key' => env('ALPHASMS_API_KEY'),
            'sender_id' => env('ALPHASMS_SENDER_ID'),
            'base_uri' => env('ALPHASMS_BASE_URI', 'https://api.sms.net.bd'),
            'timeout' => env('ALPHASMS_TIMEOUT', 30),
            'connect_timeout' => env('ALPHASMS_CONNECT_TIMEOUT', 10),
            'verify_ssl' => env('ALPHASMS_VERIFY_SSL', false),
        ],

        // Global SMS Providers

        'twilio' => [
            'driver' => 'twilio',
            'account_sid' => env('TWILIO_ACCOUNT_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'from' => env('TWILIO_FROM'),
        ],

        'nexmo' => [
            'driver' => 'nexmo',
            'api_key' => env('NEXMO_API_KEY'),
            'api_secret' => env('NEXMO_API_SECRET'),
            'from' => env('NEXMO_FROM', 'Vonage APIs'),
            'client_ref' => env('NEXMO_CLIENT_REF'), // Optional: Custom reference for tracking
            'timeout' => env('NEXMO_TIMEOUT', 30),
            'connect_timeout' => env('NEXMO_CONNECT_TIMEOUT', 10),
            'verify_ssl' => env('NEXMO_VERIFY_SSL', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Activity Tracking
    |--------------------------------------------------------------------------
    |
    | Configure how SMS activities are tracked for audit, reporting, and analytics.
    | This is separate from logging which is used for debugging.
    |
    | Supported trackers: "database", "file", "null"
    | - database: Store activities in textify_activities table (recommended for production)
    | - file: Store activities in JSON format in storage/logs/textify-activities.log
    | - null: Disable activity tracking
    |
    */

    'activity_tracking' => [
        'enabled' => env('TEXTIFY_ACTIVITY_TRACKING_ENABLED', false),
        'driver' => env('TEXTIFY_ACTIVITY_DRIVER', 'database'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Logging (Debug)
    |--------------------------------------------------------------------------
    |
    | Configure logging for debugging SMS issues. This uses Laravel's logging system.
    | Activities (above) are for audit/reporting, logging is for debugging.
    |
    */

    'logging' => [
        'enabled' => env('TEXTIFY_LOGGING_ENABLED', true),
        'log_successful' => env('TEXTIFY_LOG_SUCCESSFUL', false),
        'log_failed' => env('TEXTIFY_LOG_FAILED', true),
        'log_channel' => env('TEXTIFY_LOG_CHANNEL', 'stack'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    |
    | Configure event dispatching for SMS lifecycle events
    |
    */

    'events' => [
        'enabled' => env('TEXTIFY_EVENTS_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Validation
    |--------------------------------------------------------------------------
    |
    | Configure validation rules for SMS messages before sending.
    | Uses Laravel-style validation rules for consistency.
    |
    */

    'validation' => [
        'message' => [
            // Set to false to allow empty messages
            'required' => env('TEXTIFY_MESSAGE_REQUIRED', true),

            // Minimum message length
            'min' => env('TEXTIFY_MESSAGE_MIN_LENGTH', 1),

            // Maximum message length (null = no limit)
            'max' => env('TEXTIFY_MESSAGE_MAX_LENGTH', null),
        ],
    ],
];
