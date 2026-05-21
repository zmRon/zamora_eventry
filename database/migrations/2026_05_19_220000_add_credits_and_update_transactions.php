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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'credits')) {
                $table->decimal('credits', 10, 2)->default(0.00)->after('status');
            }
        });

        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // topup, purchase, earning, payout, adjustment, refund, refund_deduction
            $table->decimal('amount', 10, 2);
            $table->decimal('running_balance', 10, 2);
            $table->foreignId('registration_id')->nullable()->constrained('registrations')->onDelete('cascade');
            $table->foreignId('event_id')->nullable()->constrained('events')->onDelete('set null');
            $table->string('payment_method')->nullable();
            $table->string('status')->default('success'); // success, pending, failed
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'credits')) {
                $table->dropColumn('credits');
            }
        });
    }
};
