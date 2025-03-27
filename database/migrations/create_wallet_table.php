<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('owner'); // change to morphs() if you are using bigInteger or id for your primary key
            $table->string('name')->unique();
            $table->string('slug')->unique()->index();
            $table->bigInteger('raw_balance')->default(0);
            $table->integer('decimal_places')->default(config('pitaka.wallet_table.default_decimal_places'));
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('owner_id')->references('id')->on('users')->cascadeOnDelete();

        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('wallet_id')->index();
            $table->nullableUuidMorphs('transaction'); // change to morphs() if you are using bigInteger or id for your primary key
            $table->bigInteger('amount')->default(0);
            $table->bigInteger('running_balance')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('transaction_id')->references('id')->on('transaction')->nullOnDelete();
            $table->foreign('wallet_id')->references('id')->on('wallet')->cascadeOnDelete();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('wallet_transactions');
    }
};
