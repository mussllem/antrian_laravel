<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counters', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // Nama loket, mis. "Loket 1"
            $table->unsignedInteger('number');       // Nomor loket untuk tampilan & suara
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Layanan apa saja yang bisa ditangani sebuah loket (many-to-many, loket dinamis).
        Schema::create('counter_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->unique(['counter_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counter_service');
        Schema::dropIfExists('counters');
    }
};
