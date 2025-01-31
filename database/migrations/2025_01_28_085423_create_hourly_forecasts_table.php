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
        Schema::create('hourly_forecasts', function (Blueprint $table) {
            $table->id(); // SERIAL PRIMARY KEY
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade'); // INT REFERENCES location(id)
            $table->bigInteger('time_epoch'); // BIGINT NOT NULL
            $table->timestamp('time'); // TIMESTAMP NOT NULL
            $table->float('temp_c')->nullable(); // FLOAT (nullable)
            $table->float('temp_f')->nullable(); // FLOAT (nullable)
            $table->integer('is_day')->nullable(); // INT (nullable)
            $table->text('condition_text')->nullable(); // TEXT (nullable)
            $table->text('condition_icon')->nullable(); // TEXT (nullable)
            $table->integer('condition_code')->nullable(); // INT (nullable)
            $table->float('wind_mph')->nullable(); // FLOAT (nullable)
            $table->float('wind_kph')->nullable(); // FLOAT (nullable)
            $table->integer('wind_degree')->nullable(); // INT (nullable)
            $table->string('wind_dir')->nullable(); // TEXT (nullable)
            $table->float('pressure_mb')->nullable(); // FLOAT (nullable)
            $table->float('pressure_in')->nullable(); // FLOAT (nullable)
            $table->float('precip_mm')->nullable(); // FLOAT (nullable)
            $table->float('precip_in')->nullable(); // FLOAT (nullable)
            $table->float('snow_cm')->nullable(); // FLOAT (nullable)
            $table->float('humidity')->nullable(); // FLOAT (nullable)
            $table->float('cloud')->nullable(); // FLOAT (nullable)
            $table->float('feelslike_c')->nullable(); // FLOAT (nullable)
            $table->float('feelslike_f')->nullable(); // FLOAT (nullable)
            $table->float('windchill_c')->nullable(); // FLOAT (nullable)
            $table->float('windchill_f')->nullable(); // FLOAT (nullable)
            $table->float('heatindex_c')->nullable(); // FLOAT (nullable)
            $table->float('heatindex_f')->nullable(); // FLOAT (nullable)
            $table->float('dewpoint_c')->nullable(); // FLOAT (nullable)
            $table->float('dewpoint_f')->nullable(); // FLOAT (nullable)
            $table->integer('will_it_rain')->nullable(); // INT (nullable)
            $table->integer('chance_of_rain')->nullable(); // INT (nullable)
            $table->integer('will_it_snow')->nullable(); // INT (nullable)
            $table->integer('chance_of_snow')->nullable(); // INT (nullable)
            $table->float('vis_km')->nullable(); // FLOAT (nullable)
            $table->float('vis_miles')->nullable(); // FLOAT (nullable)
            $table->float('gust_mph')->nullable(); // FLOAT (nullable)
            $table->float('gust_kph')->nullable(); // FLOAT (nullable)
            $table->float('uv')->nullable(); // FLOAT (nullable)
            $table->timestamps(); // Optional: Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hourly_forecasts');
    }
};
