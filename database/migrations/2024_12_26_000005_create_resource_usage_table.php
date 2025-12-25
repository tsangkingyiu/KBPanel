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
        Schema::create('resource_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Resource metrics
            $table->decimal('cpu_percent', 5, 2)->default(0);
            $table->integer('memory_mb')->unsigned()->default(0);
            $table->integer('disk_mb')->unsigned()->default(0);
            $table->bigInteger('bandwidth_bytes')->unsigned()->default(0);
            
            // Container-specific metrics (if project-level)
            $table->string('container_id')->nullable();
            $table->integer('network_rx_bytes')->unsigned()->nullable();
            $table->integer('network_tx_bytes')->unsigned()->nullable();
            
            // Time period
            $table->timestamp('recorded_at');
            $table->enum('period_type', ['realtime', 'hourly', 'daily', 'monthly'])->default('realtime');
            
            // Additional metrics (JSON)
            $table->json('metrics')->nullable();
            // Example: {
            //   "io_read_bytes": 12345,
            //   "io_write_bytes": 67890,
            //   "process_count": 5,
            //   "uptime_seconds": 86400
            // }
            
            $table->timestamps();
            
            // Indexes for efficient querying
            $table->index(['user_id', 'recorded_at']);
            $table->index(['project_id', 'recorded_at']);
            $table->index(['period_type', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_usage');
    }
};
