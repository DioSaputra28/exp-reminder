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
        Schema::table('tracked_items', function (Blueprint $table) {
            $table->string('rack_name')->nullable()->after('reminder_sent_at');
            $table->string('shelf')->nullable()->after('rack_name');
            $table->integer('sequence')->nullable()->after('shelf');
        });
    }

    public function down(): void
    {
        Schema::table('tracked_items', function (Blueprint $table) {
            $table->dropColumn(['rack_name', 'shelf', 'sequence']);
        });
    }
};
