<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("order_filters",function (Blueprint $table){
           $table->boolean("active_push")->default(false)->after("active");
           $table->boolean("active_email")->default(false)->after("active");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("order_filters",function (Blueprint $table){
            $table->dropColumn("active_push");
            $table->dropColumn("active_email");
        });
    }
};
