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
            $table->string('account');
            $table->string('amount');
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
        Schema::enableForeignKeyConstraints();
    }
}
