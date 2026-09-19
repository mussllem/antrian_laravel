<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counter_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('number');       // Nomor urut harian per layanan
            $table->string('code');                  // Kode tampil, mis. "A012"
            $table->enum('status', ['waiting', 'called', 'serving', 'done', 'skipped'])
                  ->default('waiting');
            $table->date('queue_date');              // Tanggal antrian (reset harian)
            $table->timestamp('called_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['queue_date', 'service_id', 'status']);
            $table->unique(['service_id', 'queue_date', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
