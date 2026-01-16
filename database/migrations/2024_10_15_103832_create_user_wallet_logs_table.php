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
        Schema::create('user_wallet_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_wallet_id')
            ->constrained('user_wallets')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->double('amount');
            $table->enum('type', array_keys(Constants::TRANSACTION_TYPES));
            $table->foreignId('transaction_id')
            ->constrained('transactions')
            ->cascadeOnUpdate();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_wallet_logs');
    }
};
