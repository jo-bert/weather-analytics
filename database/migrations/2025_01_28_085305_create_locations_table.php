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
        Schema::create('locations', function (Blueprint $table) {
            $table->id(); // SERIAL PRIMARY KEY
            $table->string('name'); // TEXT NOT NULL
            $table->string('region')->nullable(); // TEXT (nullable)
            $table->string('country'); // TEXT NOT NULL
            $table->float('lat'); // FLOAT NOT NULL
            $table->float('lon'); // FLOAT NOT NULL
            $table->string('tz_id'); // TEXT NOT NULL
            $table->timestamps(); // Optional: Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
