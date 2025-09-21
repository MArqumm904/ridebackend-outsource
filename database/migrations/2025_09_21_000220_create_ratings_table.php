<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ride_id');
            $table->unsignedBigInteger('rider_id');
            $table->unsignedBigInteger('driver_id');
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->string('comment', 1024)->nullable();
            $table->timestamps();

            $table->index(['ride_id','rider_id','driver_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
