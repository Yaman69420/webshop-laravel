<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number')->nullable()->unique()->after('id');
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_session_id');
            $table->foreignId('user_id')->nullable()->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('subtotal_in_cents')->default(0)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['order_number', 'stripe_payment_intent_id']);
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('subtotal_in_cents');
        });
    }
};
