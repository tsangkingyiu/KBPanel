<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('cascade');
            
            // SMTP Configuration
            $table->string('smtp_host');
            $table->integer('smtp_port')->default(587);
            $table->string('smtp_username');
            $table->text('smtp_password'); // Should be encrypted
            $table->enum('smtp_encryption', ['tls', 'ssl', 'none'])->default('tls');
            
            // Email settings
            $table->string('from_address');
            $table->string('from_name')->nullable();
            
            // Mailer type
            $table->enum('mailer', ['smtp', 'sendmail', 'mailgun', 'ses', 'postmark'])->default('smtp');
            
            // Status and testing
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('last_tested_at')->nullable();
            $table->text('test_result')->nullable();
            
            // Usage limits (anti-spam)
            $table->integer('daily_limit')->default(100);
            $table->integer('sent_today')->default(0);
            $table->date('limit_reset_date')->useCurrent();
            
            // Additional configuration (JSON)
            $table->json('additional_config')->nullable();
            // Example: {
            //   "api_key": "...",
            //   "domain": "mail.example.com",
            //   "dkim_enabled": true
            // }
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_configs');
    }
};
