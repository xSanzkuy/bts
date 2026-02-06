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
        Schema::create('bts_lookups', function (Blueprint $table) {
            $table->id();
            $table->string('radio', 10); // gsm, umts, lte, nr
            $table->integer('mcc'); // Mobile Country Code
            $table->integer('mnc'); // Mobile Network Code
            $table->integer('lac'); // Location Area Code / TAC
            $table->bigInteger('cid'); // Cell ID
            
            // Location Results
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('accuracy')->nullable(); // in meters
            $table->string('address')->nullable();
            
            // Additional Info
            $table->integer('range')->nullable(); // coverage range
            $table->json('raw_response')->nullable(); // store full API response
            
            // Metadata
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->text('error_message')->nullable();
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['mcc', 'mnc']);
            $table->index(['radio', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bts_lookups');
    }
};