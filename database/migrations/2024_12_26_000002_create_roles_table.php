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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // 'admin', 'user'
            $table->string('display_name');
            $table->text('description')->nullable();
            
            // JSON field for granular permissions
            $table->json('permissions')->nullable();
            // Example: {
            //   "projects": ["create", "read", "update", "delete"],
            //   "users": ["read", "update"],
            //   "system": ["view_all_resources", "manage_ssl"],
            //   "databases": ["create", "manage_own"]
            // }
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
