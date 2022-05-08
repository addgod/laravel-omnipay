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
        if (Schema::hasTable('transactions') && Schema::hasTable('transaction_logs') && Schema::hasTable('merchants')) {
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // There is no coming back anymore from here.
    }
}
