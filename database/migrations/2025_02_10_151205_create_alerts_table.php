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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->string('parameter');
            $table->string('condition');
            $table->float('value')->nullable();
            $table->float('minValue')->nullable();
            $table->float('maxValue')->nullable();
            $table->timestampTz('expiry');
            $table->boolean('paused')->default(false);
            $table->boolean('triggered')->default(false);
            $table->timestampTz('triggered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
