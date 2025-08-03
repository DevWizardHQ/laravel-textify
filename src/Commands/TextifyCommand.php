<?php

declare(strict_types=1);

namespace DevWizard\Textify\Commands;

use DevWizard\Textify\Facades\Textify;
use Illuminate\Console\Command;

class TextifyCommand extends Command
{
    protected $signature = 'textify:test {--driver=} {--to=} {--message=Test SMS from Laravel Textify}';

    protected $description = 'Test SMS sending with Laravel Textify';

    public function handle(): int
    {
        $driver = $this->option('driver');
        $to = $this->option('to');
        $message = $this->option('message');

        if (! $to) {
            $to = $this->ask('Enter the phone number to send SMS to:');
        }

        if (! $to) {
            $this->error('Phone number is required!');

            return self::FAILURE;
        }

        try {
            $this->info('Sending SMS...');

            if ($driver) {
                $response = Textify::via($driver)->send($to, $message);
            } else {
                $response = Textify::send($to, $message);
            }

            if ($response->isSuccessful()) {
                $this->info('✅ SMS sent successfully!');
                $this->line("Message ID: {$response->getMessageId()}");
                $this->line("Status: {$response->getStatus()}");

                if ($response->getCost()) {
                    $this->line("Cost: {$response->getCost()}");
                }
            } else {
                $this->error('❌ SMS sending failed!');
                $this->line("Error: {$response->getErrorMessage()}");
                $this->line("Error Code: {$response->getErrorCode()}");
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ An error occurred: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
