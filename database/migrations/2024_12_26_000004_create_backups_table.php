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
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Backup metadata
            $table->string('filename');
            $table->string('storage_path');
            $table->enum('backup_type', ['full', 'database', 'files'])->default('full');
            
            // Size tracking
            $table->bigInteger('size_bytes')->unsigned();
            $table->integer('size_mb')->unsigned()->virtualAs('ROUND(size_bytes / 1024 / 1024, 2)');
            
            // Status and verification
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->boolean('verified')->default(false);
            $table->text('error_message')->nullable();
            
            // Retention
            $table->timestamp('expires_at')->nullable();
            $table->boolean('auto_generated')->default(false);
            
            // Backup metadata (JSON)
            $table->json('metadata')->nullable();
            // Example: {
            //   "tables_count": 15,
            //   "files_count": 234,
            //   "compression": "gzip",
            //   "laravel_version": "12.0",
            //   "php_version": "8.2"
            // }
            
            $table->timestamps();
            
            // Indexes
            $table->index(['project_id', 'created_at']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
