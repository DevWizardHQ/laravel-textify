<?php

declare(strict_types=1);

namespace DevWizard\Textify\Channels;

use DevWizard\Textify\Contracts\TextifyManagerInterface;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Exceptions\TextifyException;
use Illuminate\Notifications\Notification;

/**
 * Textify Notification Channel
 *
 * This channel allows Laravel notifications to be sent as SMS messages through
 * the Textify package, integrating with all supported SMS providers.
 *
 * Usage:
 * - Add 'textify' to the via() method in your notification
 * - Implement toTextify() method in your notification
 * - Ensure the notifiable has one of these methods/attributes for phone number:
 *   1. routeNotificationForTextify() method (most specific)
 *   2. getTextifyPhoneNumber() method (custom dynamic method)
 *   3. Common attributes: phone_number, phone, phn, mobile, cell, mobile_number
 */
class TextifyChannel
{
    public function __construct(
        protected TextifyManagerInterface $textify
    ) {}

    /**
     * Send the given notification
     *
     * @param  mixed  $notifiable  The notifiable entity (User, etc.)
     * @param  \Illuminate\Notifications\Notification  $notification  The notification instance
     * @return \DevWizard\Textify\DTOs\TextifyResponse|null
     *
     * @throws \DevWizard\Textify\Exceptions\TextifyException
     */
    public function send($notifiable, Notification $notification): ?TextifyResponse
    {
        // Get the SMS data from the notification first to avoid unnecessary phone number lookup
        if (!method_exists($notification, 'toTextify')) {
            throw new TextifyException(
                'Notification must implement toTextify() method to use the Textify channel.'
            );
        }

        /** @var mixed $textifyData */
        $textifyData = $notification->toTextify($notifiable);

        if (!$textifyData) {
            return null;
        }

        // Get the phone number from the notifiable
        $phoneNumber = $this->getPhoneNumber($notifiable, $notification);

        if (!$phoneNumber) {
            throw new TextifyException(
                'Unable to determine phone number for notification. ' .
                    'Implement routeNotificationForTextify() or getTextifyPhoneNumber() method, or ensure phone_number attribute exists.'
            );
        }

        // Prepare the SMS data
        $message = $this->getMessage($textifyData);
        if (!$message || trim($message) === '') {
            throw new TextifyException('SMS message is required for Textify notifications.');
        }

        $from = $this->getFrom($textifyData);
        $driver = $this->getDriver($textifyData);

        // Send the SMS
        $textifyInstance = $this->textify;

        if ($driver) {
            $textifyInstance = $textifyInstance->via($driver);
        } elseif (app()->environment('testing')) {
            // For testing, ensure we use the array driver if no driver is specified
            $textifyInstance = $textifyInstance->via('array');
        }

        if ($from) {
            $textifyInstance = $textifyInstance->from($from);
        }

        return $textifyInstance->send($phoneNumber, $message);
    }

    /**
     * Get the phone number for the notifiable
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string|null
     */
    protected function getPhoneNumber($notifiable, Notification $notification): ?string
    {
        // First try the route notification method (most specific)
        if (method_exists($notifiable, 'routeNotificationForTextify')) {
            $phoneNumber = $notifiable->routeNotificationForTextify($notification);
            if (!empty($phoneNumber)) {
                return $phoneNumber;
            }
        }

        // Then try the custom getTextifyPhoneNumber method
        if (method_exists($notifiable, 'getTextifyPhoneNumber')) {
            $phoneNumber = $notifiable->getTextifyPhoneNumber();
            if (!empty($phoneNumber)) {
                return $phoneNumber;
            }
        }

        // Then try common phone number attributes (ordered by priority)
        $phoneAttributes = ['phone_number', 'phone', 'mobile', 'phn', 'mobile_number', 'cell'];

        foreach ($phoneAttributes as $attribute) {
            if (isset($notifiable->{$attribute}) && !empty($notifiable->{$attribute})) {
                return $notifiable->{$attribute};
            }
        }

        return null;
    }

    /**
     * Get the message from the notification data
     *
     * @param  mixed  $textifyData
     * @return string|null
     */
    protected function getMessage($textifyData): ?string
    {
        if (is_string($textifyData)) {
            return $textifyData;
        }

        if (is_array($textifyData)) {
            return $textifyData['message'] ?? $textifyData['body'] ?? $textifyData['content'] ?? null;
        }

        if (is_object($textifyData) && method_exists($textifyData, 'getMessage')) {
            return $textifyData->getMessage();
        }

        if (is_object($textifyData) && isset($textifyData->message)) {
            return $textifyData->message;
        }

        return null;
    }

    /**
     * Get the sender ID from the notification data
     *
     * @param  mixed  $textifyData
     * @return string|null
     */
    protected function getFrom($textifyData): ?string
    {
        if (is_array($textifyData)) {
            return $textifyData['from'] ?? $textifyData['sender'] ?? $textifyData['sender_id'] ?? null;
        }

        if (is_object($textifyData)) {
            if (method_exists($textifyData, 'getFrom')) {
                return $textifyData->getFrom();
            }

            return $textifyData->from ?? $textifyData->sender ?? $textifyData->sender_id ?? null;
        }

        return null;
    }

    /**
     * Get the SMS driver/provider from the notification data
     *
     * @param  mixed  $textifyData
     * @return string|null
     */
    protected function getDriver($textifyData): ?string
    {
        if (is_array($textifyData)) {
            return $textifyData['driver'] ?? $textifyData['provider'] ?? $textifyData['via'] ?? null;
        }

        if (is_object($textifyData)) {
            if (method_exists($textifyData, 'getDriver')) {
                return $textifyData->getDriver();
            }

            return $textifyData->driver ?? $textifyData->provider ?? $textifyData->via ?? null;
        }

        return null;
    }
}
