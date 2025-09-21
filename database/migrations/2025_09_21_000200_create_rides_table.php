<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rider_id');
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->decimal('pickup_lat', 10, 7);
            $table->decimal('pickup_lng', 10, 7);
            $table->string('pickup_address', 1024)->nullable();
            $table->decimal('dropoff_lat', 10, 7);
            $table->decimal('dropoff_lng', 10, 7);
            $table->string('dropoff_address', 1024)->nullable();
            $table->decimal('distance_km', 8, 3)->nullable();
            $table->decimal('estimated_fare', 8, 2)->nullable();
            $table->decimal('final_fare', 8, 2)->nullable();
            $table->enum('status', ['requested','assigned','driver_arriving','started','completed','canceled'])->default('requested');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->enum('canceled_by', ['rider','driver','system'])->nullable();
            $table->enum('payment_method', ['cash'])->default('cash');
            $table->enum('payment_status', ['unpaid','paid'])->default('unpaid');
            $table->longText('polyline')->nullable();
            $table->timestamps();

            $table->index('rider_id');
            $table->index('driver_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
