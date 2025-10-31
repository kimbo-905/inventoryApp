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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); // e.g., created, updated, deleted
            $table->string('table_name'); // e.g., 'products'
            $table->unsignedBigInteger('record_id'); // the affected record's ID
            $table->text('description')->nullable(); // optional details
            $table->ipAddress('ip_address')->nullable(); // optional IP
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
