<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('merchant_id');
            $table->decimal('amount');
            $table->string('redirect_to');
            $table->string('transaction', 100)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->morphs('entity');
            $table->timestamps();
        });

        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('transaction_id');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->text('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('merchants', function (Blueprint $table) {
            $table->increments('id');
            $table->string('merchant_id')->unique();
            $table->string('name');
            $table->string('key1');
            $table->string('key2');
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('lang');
            $table->string('currency');
            $table->string('pay_type');
            $table->timestamps();
        });

        Schema::create('merchantables', function(Blueprint $table) {
            $table->unsignedInteger('merchant_id');
            $table->foreign('merchant_id')->references('id')->on('merchants')->onDelete('cascade');
            $table->morphs('merchantable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('transaction_logs');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('merchants');
        Schema::dropIfExists('merchantables');
        Schema::enableForeignKeyConstraints();
    }
}
