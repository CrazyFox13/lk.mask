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
        Schema::create('geo_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('geo_region_id')->nullable()->constrained();
            $table->string("name");
            $table->string("postal_code");
            $table->string("timezone");
            $table->float("lat",12,8);
            $table->float("lng",12,8);
            $table->string("fias_id");
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
        Schema::dropIfExists('geo_cities');
    }
};
