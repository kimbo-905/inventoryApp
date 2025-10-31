<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->enum('type_utilisation', ['stock', 'conso_directe'])->default('stock');
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_request_items', function (Blueprint $table) {
            //
        });
    }
};
