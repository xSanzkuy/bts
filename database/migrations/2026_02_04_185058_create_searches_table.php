<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('searches', function (Blueprint $table) {
            $table->id();
            $table->string('radio_type', 20);
            $table->integer('mcc');
            $table->integer('mnc');
            $table->integer('lac');
            $table->bigInteger('cid');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('accuracy')->nullable();
            $table->text('address')->nullable();
            $table->string('status', 20);
            $table->text('error_message')->nullable();
            $table->json('raw_response')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            
            $table->index(['mcc', 'mnc']);
            $table->index('radio_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('searches');
    }
};