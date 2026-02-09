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
        Schema::table("order_addresses",function (Blueprint $table){
            $table->foreignId('geo_city_id')->nullable()->after("city_id")->constrained()->nullOnDelete();
            $table->foreignId('geo_region_id')->nullable()->after("region_id")->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("order_addresses",function (Blueprint $table){
            $table->dropConstrainedForeignId("geo_city_id");
            $table->dropConstrainedForeignId("geo_region_id");
        });
    }
};
