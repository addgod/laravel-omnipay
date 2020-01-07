<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('omnipay_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('merchant_id');
            $table->decimal('amount');
            $table->string('redirect_to');
            $table->string('transaction', 100)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->nullableMorphs('entity');
            $table->timestamps();
        });

        Schema::create('omnipay_transaction_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('transaction_id');
            $table->foreign('transaction_id')->references('id')->on('omnipay_transactions')->onDelete('cascade');
            $table->text('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('omnipay_merchants', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('gateway');
            $table->text('config');
            $table->timestamps();
        });

        Schema::create('merchantables', function (Blueprint $table) {
            $table->unsignedInteger('merchant_id');
            $table->foreign('merchant_id')->references('id')->on('omnipay_merchants')->onDelete('cascade');
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
        Schema::dropIfExists('omnipay_transaction_logs');
        Schema::dropIfExists('omnipay_transactions');
        Schema::dropIfExists('omnipay_merchants');
        Schema::dropIfExists('merchantables');
        Schema::enableForeignKeyConstraints();
    }
}
