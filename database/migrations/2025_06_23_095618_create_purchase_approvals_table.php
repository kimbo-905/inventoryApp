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
    Schema::create('purchase_approvals', function (Blueprint $table) {
        $table->id();
        $table->foreignId('purchase_request_id')->constrained()->onDelete('cascade');
        $table->enum('étape', ['chef_service', 'direction', 'QHSE','admin']);
        $table->foreignId('validé_par')->constrained('users');
        $table->timestamp('date_validation');
        $table->text('commentaire')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_approvals');
    }
};
