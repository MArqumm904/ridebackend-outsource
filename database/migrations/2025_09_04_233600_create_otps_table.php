<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // optional, OTP before user is created
            $table->string('recipient'); // email ya phone
            $table->enum('type', ['email', 'phone']);
            $table->string('code'); // OTP code (6 digits)
            $table->timestamp('expires_at'); // OTP expiry time
            $table->boolean('is_used')->default(false); // track if OTP already used
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
