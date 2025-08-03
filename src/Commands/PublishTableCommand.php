<?php

declare(strict_types=1);

namespace DevWizard\Textify\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishTableCommand extends Command
{
    protected $signature = 'textify:table';

    protected $description = 'Publish the Textify SMS logs database migration';

    public function handle(): int
    {
        $this->components->info('Publishing Textify migration...');

        $stubPath = __DIR__.'/../../database/migrations/create_textify_table.php.stub';
        $migrationPath = database_path('migrations');

        if (! File::exists($stubPath)) {
            $this->components->error('Migration stub not found.');

            return self::FAILURE;
        }

        // Generate migration filename with timestamp
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_create_textify_activities_table.php";
        $destinationPath = "{$migrationPath}/{$filename}";

        // Check if migration already exists
        $existingMigrations = File::glob("{$migrationPath}/*_create_textify_activities_table.php");
        if (! empty($existingMigrations)) {
            $this->components->warn('Migration already exists: '.basename($existingMigrations[0]));

            if (! $this->confirm('Do you want to create a new migration anyway?')) {
                $this->components->info('Migration publishing cancelled.');

                return self::SUCCESS;
            }
        }

        // Copy and rename the migration
        $migrationContent = File::get($stubPath);
        File::put($destinationPath, $migrationContent);

        $this->components->info("Migration published: {$filename}");
        $this->line('');
        $this->components->info('Next steps:');
        $this->line('1. Run: <fg=yellow>php artisan migrate</fg=yellow>');
        $this->line('2. Update your .env file:');
        $this->line('   <fg=yellow>TEXTIFY_ACTIVITY_TRACKER=database</fg=yellow>');
        $this->line('');
        $this->components->info('You can now track SMS activities in the textify_activities table!');

        return self::SUCCESS;
    }
}
