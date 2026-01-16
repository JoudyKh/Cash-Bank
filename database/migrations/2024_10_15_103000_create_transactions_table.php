<?php

use App\Constants\Constants;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
            ->constrained('users')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->double('amount_sent');
            $table->double('amount_received');
            $table->double('amount_confirmed')->nullable();
            $table->foreignId('from_wallet_id')
            ->constrained('wallets')
            ->cascadeOnUpdate();
            $table->foreignId('to_wallet_id')
            ->constrained('wallets')
            ->cascadeOnUpdate();
            $table->string('from_wallet_number');
            $table->string('to_wallet_number');
            $table->text('note')->nullable();
            $table->string('key', 10)->unique();
            $table->enum('status', array_keys(Constants::TRANSACTION_STATUSES))->default('pending');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
