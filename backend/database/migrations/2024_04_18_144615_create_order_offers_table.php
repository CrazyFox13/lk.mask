<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum("status", ["waiting", "accepted", "declined"])->default("waiting");
            $table->float("amount_account_vat")->nullable();
            $table->float("amount_account")->nullable();
            $table->float("amount_cash")->nullable();
            $table->date("date_start")->nullable();
            $table->text("comment")->nullable();
            $table->timestamps();
            $table->timestamp("viewed_at")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_offers');
    }
};
