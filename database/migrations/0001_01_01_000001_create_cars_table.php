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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Basic Info
            $table->string('title');
            $table->string('make');
            $table->string('model');
            $table->integer('year');
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
        
            // Detailed Specifications
            $table->decimal('mileage', 8, 2)->nullable()->comment('In km/l or km/kg');
            $table->string('mileage_unit')->nullable()->comment('kmpl, kmpkg');
            $table->string('fuel_type')->nullable()->comment('Petrol, Diesel, Electric, CNG, LPG');
            $table->string('transmission')->nullable()->comment('Manual, Automatic');
            $table->string('owner_number')->nullable()->comment('First, Second, Third, Fourth');
            $table->string('color')->nullable();
            $table->string('location')->nullable();
            $table->string('body_type')->nullable()->comment('Sedan, SUV, Hatchback, etc.');
            $table->integer('registration_year')->nullable();
            $table->date('insurance_validity')->nullable();
            $table->string('engine_cc')->nullable();
            $table->string('variant')->nullable();
        
            // Features
            $table->boolean('power_windows')->nullable();
            $table->boolean('abs')->nullable();
            $table->boolean('airbags')->nullable();
            $table->boolean('sunroof')->nullable();
            $table->boolean('navigation')->nullable();
            $table->boolean('rear_camera')->nullable();
            $table->boolean('leather_seats')->nullable();
        
            // Status
            $table->enum('status', ['draft', 'pending', 'approved', 'sold'])->default('draft');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
