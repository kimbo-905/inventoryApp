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
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->enum('type', ['matériel', 'fourniture', 'matière']);
            $table->string('service_demandeur');
            $table->string('nom_demandeur');
            $table->text('motif')->nullable();
            $table->enum('statut', ['brouillon', 'soumis', 'validé_chef', 'validé_direction', 'rejeté'])->default('brouillon');
            $table->string('fichier_joint')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
