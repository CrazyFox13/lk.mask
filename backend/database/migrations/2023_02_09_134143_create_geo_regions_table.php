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
        Schema::create('geo_regions', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("name_with_type");
            $table->string("type");
            $table->string("federal_district");
            $table->string("postal_code")->nullable();
            $table->string("timezone");
            $table->string("geoname_id");
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
        Schema::dropIfExists('geo_regions');
    }
};
