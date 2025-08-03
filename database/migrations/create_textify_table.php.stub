<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('textify_activities', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->nullable()->index();
            $table->string('provider')->index();
            $table->string('to');
            $table->string('from')->nullable();
            $table->text('message');
            $table->string('status')->index();
            $table->boolean('success')->default(false)->index();
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->decimal('cost', 8, 4)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['provider', 'status']);
            $table->index(['to', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('textify_activities');
    }
};
