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
        Schema::create('payment_units', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->timestamps();
        });

        Schema::table("orders", function (Blueprint $table) {
            $table->foreignId('payment_unit_id')->nullable()->after("amount_type")->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("orders", function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_unit_id');
        });

        Schema::dropIfExists('payment_units');
    }
};
