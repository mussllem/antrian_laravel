<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('counters', function (Blueprint $table) {
            // Siapa (operator) yang sedang menempati loket ini. NULL = loket bebas.
            $table->foreignId('occupied_by')->nullable()->after('is_active')
                  ->constrained('users')->nullOnDelete();
            $table->timestamp('occupied_at')->nullable()->after('occupied_by');
        });
    }

    public function down(): void
    {
        Schema::table('counters', function (Blueprint $table) {
            $table->dropConstrainedForeignId('occupied_by');
            $table->dropColumn('occupied_at');
        });
    }
};
