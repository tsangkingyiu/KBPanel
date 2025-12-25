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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('domain_name')->unique();
            
            // Domain type: primary, alias, staging
            $table->enum('type', ['primary', 'alias', 'staging'])->default('primary');
            
            // DNS verification
            $table->boolean('dns_verified')->default(false);
            $table->timestamp('dns_verified_at')->nullable();
            
            // SSL status
            $table->boolean('ssl_enabled')->default(false);
            $table->foreignId('ssl_certificate_id')->nullable()->constrained('ssl_certificates')->onDelete('set null');
            
            // Web server configuration
            $table->enum('web_server', ['apache', 'nginx'])->default('apache');
            $table->string('config_file_path')->nullable();
            
            // Redirect settings
            $table->boolean('redirect_to_https')->default(true);
            $table->boolean('redirect_to_www')->default(false);
            
            // Status
            $table->enum('status', ['active', 'pending', 'suspended'])->default('pending');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
