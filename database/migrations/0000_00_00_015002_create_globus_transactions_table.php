<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGlobusTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('globus_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('transaction_id')->unique()->index();
            $table->uuid('processing_item_id')->index();

            $table->string('state_code');
            $table->longText('state_code_reason')->nullable();
            $table->string('error_code')->nullable();
            $table->longText('error_code_description')->nullable();
            $table->string('status_code')->nullable();
            $table->longText('status_code_description')->nullable();

            $table->string('reference')->unique();
            $table->string('batch_reference')->nullable();
            $table->string('payment_type');
            $table->string('payment_method');
            $table->string('recipient_bank_account');
            $table->string('recipient_bank_code');
            $table->string('recipient_name');
            $table->char('currency_code', 3);
            $table->double('amount');

            $table->timestamps(6);
            $table->softDeletes('deleted_at', 6);

            // $table->foreign('transaction_id')->references('id')->on('transactions');
            // $table->foreign('processing_item_id')->references('id')->on('processing_items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('globus_transactions');
    }
}
