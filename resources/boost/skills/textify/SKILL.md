---
name: textify
description: Send SMS via multiple providers with a unified API, queue support, Laravel notifications, fallback routing, and activity tracking.
---

# Textify — Unified SMS Sending

## When to use this skill

Activate this skill when:
- Sending SMS messages (OTP, alerts, notifications, marketing)
- Integrating SMS into Laravel's notification system
- Building features that require phone verification or OTP
- Setting up multi-provider SMS with fallback
- Queuing SMS for background delivery
- Tracking SMS delivery history

## Strict Rules

### Sending SMS Rules

1. **ALWAYS use the `Textify` facade** — never instantiate providers directly:
   ```php
   use DevWizard\Textify\Facades\Textify;

   // CORRECT
   $response = Textify::send('01712345678', 'Your OTP is 123456');

   // WRONG — bypasses lifecycle, events, tracking
   $provider = new DhorolaSmsProvider($config);
   $provider->send($message);
   ```

2. **ALWAYS check the response** after sending:
   ```php
   $response = Textify::send($phone, $message);

   if ($response->isSuccessful()) {
       $messageId = $response->getMessageId();
       $cost = $response->getCost();
   } else {
       $error = $response->getErrorMessage();
       $code = $response->getErrorCode();
       Log::error("SMS failed: {$error}", ['code' => $code, 'to' => $phone]);
   }
   ```

3. **Use the fluent builder** for complex sends. Methods like `via()`, `to()`, `message()`, and `from()` return a clone; `fallback()` mutates the current instance:
   ```php
   // With specific provider
   $response = Textify::via('twilio')
       ->to('+1234567890')
       ->message('Hello from Twilio!')
       ->from('MyApp')
       ->send();

   // With fallback
   $response = Textify::via('dhorola')
       ->fallback('bulksmsbd')
       ->to('01712345678')
       ->message('Critical alert!')
       ->sendWithFallback();
   ```

4. **For multiple recipients, pass an array to `to()`**:
   ```php
   $responses = Textify::to(['01712345678', '01812345678', '01912345678'])
       ->message('System maintenance at 2 AM.')
       ->send();
   // Returns array of TextifyResponse — one per recipient
   ```

5. **ALWAYS queue non-critical SMS** to avoid blocking HTTP requests:
   ```php
   // CORRECT — queued, non-blocking
   Textify::to($user->phone)
       ->message('Your weekly report is ready.')
       ->queue();

   // With specific queue name
   Textify::to($user->phone)
       ->message('Your weekly report is ready.')
       ->queue('sms');

   // Only send synchronously for critical, time-sensitive messages (OTP)
   $response = Textify::send($phone, "Your OTP is {$otp}");
   ```

6. **ALWAYS use `fallback()` for critical messages** (OTP, password reset, alerts). Use `sendWithFallback()` to enable automatic fallback routing:
   ```php
   $response = Textify::via('dhorola')
       ->fallback('bulksmsbd')
       ->to($phone)
       ->message("Your verification code is {$code}")
       ->sendWithFallback();
   ```

7. **NEVER hardcode provider names in application code** when possible. Use the config default:
   ```php
   // CORRECT — uses TEXTIFY_PROVIDER from .env
   Textify::send($phone, $message);

   // OK — when you explicitly need a specific provider for this message
   Textify::via('twilio')->send($phone, $message);

   // WRONG — hardcoded everywhere, hard to change
   Textify::via('dhorola')->send($phone, $message); // in every controller
   ```

### Notification Channel Rules

8. **ALWAYS use the `textify` notification channel** for user-facing SMS (order updates, reminders, alerts):
   ```php
   use Illuminate\Notifications\Notification;
   use DevWizard\Textify\Notifications\TextifyMessage;

   class OrderShipped extends Notification
   {
       public function __construct(private Order $order) {}

       public function via($notifiable): array
       {
           return ['textify'];
       }

       public function toTextify($notifiable): TextifyMessage
       {
           return TextifyMessage::create(
               __('Your order #:id has been shipped!', ['id' => $this->order->id])
           );
       }
   }
   ```

9. **ALWAYS implement `routeNotificationForTextify()`** on your notifiable model for explicit phone number routing:
   ```php
   class User extends Authenticatable
   {
       use Notifiable;

       public function routeNotificationForTextify($notification): string
       {
           return $this->phone_number;
       }
   }
   ```

   The channel auto-detects these attributes if the method is not defined: `phone_number`, `phone`, `mobile`, `phn`, `mobile_number`, `cell`. But explicit routing is preferred.

10. **To specify a provider per notification**, use `->driver()` on the message:
    ```php
    public function toTextify($notifiable): TextifyMessage
    {
        return TextifyMessage::create(__('Your OTP is :code', ['code' => $this->code]))
            ->from('MyApp')
            ->driver('twilio');  // Use Twilio for this specific notification
    }
    ```

11. **Send notifications the standard Laravel way**:
    ```php
    // Single user
    $user->notify(new OrderShipped($order));

    // Multiple users
    Notification::send($users, new OrderShipped($order));
    ```

### Custom Provider Rules

12. **ALWAYS extend `TextifyProvider`** (simplified base) for custom providers:
    ```php
    use DevWizard\Textify\Providers\TextifyProvider;
    use DevWizard\Textify\DTOs\TextifyMessage;
    use DevWizard\Textify\DTOs\TextifyResponse;

    class MySmsProvider extends TextifyProvider
    {
        public function getProviderName(): string
        {
            return 'mysms';
        }

        protected function getRequiredConfigKeys(): array
        {
            return ['api_key', 'sender_id'];
        }

        protected function sendSms(TextifyMessage $message): array
        {
            $response = Http::post('https://api.mysms.com/send', [
                'api_key' => $this->config['api_key'],
                'to' => $message->getTo(),
                'message' => $message->getMessage(),
                'from' => $message->getFrom() ?? $this->config['sender_id'],
            ]);

            return $response->json();
        }

        protected function parseApiResponse(array $response): TextifyResponse
        {
            if ($response['status'] === 'success') {
                return TextifyResponse::success(
                    messageId: $response['id'],
                    cost: $response['cost'] ?? null,
                    status: 'sent',
                    rawResponse: $response,
                );
            }

            return TextifyResponse::failed(
                errorMessage: $response['error'] ?? 'Unknown error',
                errorCode: $response['code'] ?? 'UNKNOWN',
                rawResponse: $response,
            );
        }
    }
    ```

13. **Register custom providers in config**, not in service providers:
    ```php
    // config/textify.php → providers
    'mysms' => [
        'driver' => 'mysms',
        'class' => \App\Services\MySmsProvider::class,
        'api_key' => env('MYSMS_API_KEY'),
        'sender_id' => env('MYSMS_SENDER_ID'),
    ],
    ```

14. **ALWAYS use `TextifyResponse::success()` and `TextifyResponse::failed()`** static factories in custom providers. Never construct `TextifyResponse` directly.

### Environment & Testing Rules

15. **ALWAYS set `TEXTIFY_PROVIDER` per environment** in `.env`:
    ```env
    # .env (development)
    TEXTIFY_PROVIDER=log

    # .env (testing)
    TEXTIFY_PROVIDER=array

    # .env (production)
    TEXTIFY_PROVIDER=dhorola
    TEXTIFY_FALLBACK_PROVIDER=bulksmsbd
    ```

16. **ALWAYS use the `array` driver in tests**, then assert against stored messages:
    ```php
    use DevWizard\Textify\Providers\ArrayProvider;

    // In test setup or phpunit.xml
    // TEXTIFY_PROVIDER=array

    public function test_otp_is_sent(): void
    {
        ArrayProvider::clearMessages();

        // Trigger the action that sends SMS
        $this->post('/send-otp', ['phone' => '01712345678']);

        $messages = ArrayProvider::getMessages();
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('OTP', $messages[0]['message']);
        $this->assertEquals('01712345678', $messages[0]['to']);
    }

    protected function tearDown(): void
    {
        ArrayProvider::clearMessages();
        parent::tearDown();
    }
    ```

17. **NEVER send real SMS in tests**. The `array` driver stores messages in memory without making HTTP calls.

### Event & Tracking Rules

18. **Listen to SMS events** for logging, metrics, or side effects:
    ```php
    use DevWizard\Textify\Events\TextifySending;
    use DevWizard\Textify\Events\TextifySent;
    use DevWizard\Textify\Events\TextifyFailed;

    // In a listener or EventServiceProvider
    Event::listen(TextifySent::class, function (TextifySent $event) {
        // $event->message — TextifyMessage DTO
        // $event->response — TextifyResponse DTO
        // $event->provider — string provider name
    });

    Event::listen(TextifyFailed::class, function (TextifyFailed $event) {
        // $event->exception — ?\Throwable
        // Alert ops team, retry logic, etc.
    });
    ```

19. **For production monitoring, enable database activity tracking**:
    ```php
    // config/textify.php
    'activity_tracking' => [
        'enabled' => true,
        'driver' => 'database',
    ],
    ```
    ```bash
    php artisan textify:table
    php artisan migrate
    ```

20. **Query activity tracking via the `TextifyActivity` model**:
    ```php
    use DevWizard\Textify\Models\TextifyActivity;

    // Today's stats
    $sent = TextifyActivity::successful()->today()->count();
    $failed = TextifyActivity::failed()->today()->count();

    // By provider
    $twilioFailed = TextifyActivity::failed()->byProvider('twilio')->lastDays(7)->get();

    // By recipient
    $history = TextifyActivity::byRecipient('01712345678')->latest()->get();
    ```

## Complete Implementation Patterns

### Pattern: OTP Verification

```php
// Controller
class OtpController extends Controller
{
    public function send(Request $request)
    {
        $request->validate(['phone' => 'required|string']);

        $otp = random_int(100000, 999999);
        cache()->put("otp:{$request->phone}", $otp, now()->addMinutes(5));
        cache()->put("otp_attempts:{$request->phone}", 0, now()->addMinutes(5));

        $response = Textify::via(config('textify.default'))
            ->fallback(config('textify.fallback'))
            ->to($request->phone)
            ->message(__('Your verification code is :code. Valid for 5 minutes.', ['code' => $otp]))
            ->sendWithFallback();

        if ($response->isFailed()) {
            return back()->withErrors(['phone' => __('Failed to send OTP. Please try again.')]);
        }

        return redirect()->route('otp.verify');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|digits:6',
        ]);

        // Rate limit: max 5 attempts per phone number
        $attemptsKey = "otp_attempts:{$request->phone}";
        $attempts = (int) cache()->get($attemptsKey, 0);

        if ($attempts >= 5) {
            cache()->forget("otp:{$request->phone}");
            cache()->forget($attemptsKey);

            return back()->withErrors(['otp' => __('Too many attempts. Please request a new OTP.')]);
        }

        cache()->increment($attemptsKey);

        $cached = cache()->pull("otp:{$request->phone}");

        if (!$cached || (int) $request->otp !== $cached) {
            // Put back so subsequent attempts can still match (pull removes it)
            if ($cached) {
                cache()->put("otp:{$request->phone}", $cached, now()->addMinutes(5));
            }

            return back()->withErrors(['otp' => __('Invalid or expired OTP.')]);
        }

        cache()->forget($attemptsKey);

        // OTP verified — proceed with action
    }
}
```

### Pattern: Order Notification via Notification Channel

```php
// app/Notifications/OrderStatusChanged.php
class OrderStatusChanged extends Notification
{
    public function __construct(
        private Order $order,
        private string $status,
    ) {}

    public function via($notifiable): array
    {
        return ['textify', 'mail'];
    }

    public function toTextify($notifiable): TextifyMessage
    {
        $message = match ($this->status) {
            'confirmed' => __('Order #:id confirmed! We are preparing it.', ['id' => $this->order->id]),
            'shipped' => __('Order #:id shipped! Track: :url', ['id' => $this->order->id, 'url' => $this->order->tracking_url]),
            'delivered' => __('Order #:id delivered! Thank you for shopping.', ['id' => $this->order->id]),
        };

        return TextifyMessage::create($message)->from('MyStore');
    }
}

// Usage in controller/service
$order->customer->notify(new OrderStatusChanged($order, 'shipped'));
```

### Pattern: Bulk SMS in a Queued Job

```php
// app/Jobs/SendPromotionalSms.php
class SendPromotionalSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $message,
        private array $phoneNumbers,
    ) {}

    public function handle(): void
    {
        foreach (array_chunk($this->phoneNumbers, 50) as $batch) {
            Textify::to($batch)
                ->message($this->message)
                ->queue('sms-bulk');
        }
    }
}

// Dispatch
SendPromotionalSms::dispatch('Flash sale! 50% off everything today.', $phones);
```

## Provider Reference

| Provider | Driver Key | Region | Required Config |
|----------|-----------|--------|-----------------|
| Dhorola | `dhorola` | BD | `api_key`, `sender_id` |
| BulkSMS BD | `bulksmsbd` | BD | `api_key`, `sender_id` |
| MIM SMS | `mimsms` | BD | `username`, `apikey` |
| eSMS | `esms` | BD | `api_token`, `sender_id` |
| Reve SMS | `revesms` | BD | `apikey`, `secretkey`, `client_id` |
| Alpha SMS | `alphasms` | BD | `api_key` |
| Twilio | `twilio` | Global | `account_sid`, `auth_token`, `from` |
| Nexmo/Vonage | `nexmo` | Global | `api_key`, `api_secret`, `from` |
| Log | `log` | Dev | — (writes to Laravel log) |
| Array | `array` | Test | — (stores in memory) |

**Phone number formatting**: Bangladeshi providers auto-format to their required format (`01XXXXXXXXX` or `8801XXXXXXXXX`). Global providers use `+E.164`. You do NOT need to pre-format numbers.

## Response Object Reference (`TextifyResponse`)

| Method | Returns | Description |
|--------|---------|-------------|
| `isSuccessful()` | `bool` | Was the send successful |
| `isFailed()` | `bool` | Did the send fail |
| `getMessageId()` | `?string` | Provider's message ID |
| `getErrorMessage()` | `?string` | Human-readable error |
| `getErrorCode()` | `?string` | Machine-readable error code |
| `getCost()` | `?float` | Cost of the message |
| `getStatus()` | `?string` | Delivery status string |
| `getRawResponse()` | `array` | Full provider response |

## Artisan Commands

```bash
php artisan textify:test --to=01712345678 --message="Test"  # Send test SMS
php artisan textify:test --driver=twilio                     # Test specific provider
php artisan textify:table                                    # Publish activity migration
```

## Common Anti-Patterns to Avoid

| Anti-Pattern | Correct Pattern |
|---|---|
| Instantiating providers directly | Use `Textify::` facade or `Textify::via('name')` |
| Sending SMS synchronously in web requests for non-critical messages | Use `->queue()` for non-critical SMS |
| No fallback for OTP/critical messages | Use `->fallback('provider')->sendWithFallback()` |
| Hardcoding `Textify::via('dhorola')` everywhere | Set `TEXTIFY_PROVIDER` in `.env`, use default |
| Running real SMS in tests | Set `TEXTIFY_PROVIDER=array` in test env |
| Not checking `$response->isSuccessful()` | ALWAYS check response and handle failures |
| Manual phone number formatting | Providers auto-format — pass raw numbers |
| Using `Textify::send()` in Notification classes | Use `toTextify()` returning `TextifyMessage` |
| Not clearing `ArrayProvider::clearMessages()` between tests | ALWAYS clear in `tearDown()` |
