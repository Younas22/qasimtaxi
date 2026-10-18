<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_enabled')->default(false);
            $table->enum('mode', ['sandbox', 'live'])->default('sandbox');

            // Encrypted credential pairs. Stripe: Publishable/Secret key.
            // PayPal: Client ID/Secret. Kept as separate sandbox/live pairs
            // so switching modes never discards the other environment's keys.
            $table->text('sandbox_key_1')->nullable();
            $table->text('sandbox_key_2')->nullable();
            $table->text('live_key_1')->nullable();
            $table->text('live_key_2')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
