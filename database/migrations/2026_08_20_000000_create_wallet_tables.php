<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'user_id')) {
                $table->string('user_id')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('users', 'balance')) {
                $table->decimal('balance', 15, 2)->default(0)->after('password');
            }
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('transaction_id')->primary();
            $table->string('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->enum('type', ['top_up', 'payment', 'transfer_in', 'transfer_out'])->default('payment');
            $table->decimal('amount', 15, 2);
            $table->string('description')->nullable();
            $table->decimal('balance_before', 15, 2)->nullable();
            $table->decimal('balance_after', 15, 2);
            $table->enum('status', ['success', 'failed', 'pending'])->default('success');
            $table->timestamps();
        });

        Schema::create('transfers', function (Blueprint $table) {
            $table->uuid('transfer_id')->primary();
            $table->string('sender_id');
            $table->string('receiver_id');
            $table->decimal('amount', 15, 2);
            $table->string('description')->nullable();
            $table->enum('status', ['queued', 'completed', 'failed'])->default('queued');
            $table->timestamps();

            $table->foreign('sender_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('receiver_id')->references('user_id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
        Schema::dropIfExists('transactions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'phone_number', 'balance']);
        });
    }
};
