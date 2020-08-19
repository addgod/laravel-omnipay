<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTableNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_logs', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
        });

        Schema::table('merchantables', function (Blueprint $table) {
            $table->dropForeign(['merchant_id']);
        });

        Schema::rename('transactions', 'omnipay_transactions');
        Schema::rename('transaction_logs', 'omnipay_transaction_logs');
        Schema::rename('merchants', 'omnipay_merchants');

        Schema::table('omnipay_transaction_logs', function (Blueprint $table) {
            $table->foreign('transaction_id')->references('id')->on('omnipay_transactions')->onDelete('cascade');
        });

        Schema::table('merchantables', function (Blueprint $table) {
            $table->foreign('merchant_id')->references('id')->on('omnipay_merchants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('omnipay_transaction_logs', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
        });

        Schema::table('merchantables', function (Blueprint $table) {
            $table->dropForeign(['merchant_id']);
        });

        Schema::rename('omnipay_transactions', 'transactions');
        Schema::rename('omnipay_transaction_logs', 'transactions_logs');
        Schema::rename('omnipay_merchants', 'merchants');

        Schema::table('transaction_logs', function (Blueprint $table) {
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
        });

        Schema::table('merchantables', function (Blueprint $table) {
            $table->foreign('merchant_id')->references('id')->on('merchants')->onDelete('cascade');
        });
    }
}
