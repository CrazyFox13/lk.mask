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
        Schema::create('adv_banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId("advertiser_id")->constrained()->cascadeOnDelete();
            $table->foreignId("adv_place_id")->constrained()->cascadeOnDelete();
            $table->boolean("is_active")->default(false);
            $table->string("title");
            $table->string("tooltip")->nullable();
            $table->dateTime("start_date")->nullable();
            $table->dateTime("end_date")->nullable();
            $table->string("img_url");
            $table->string("endpoint_url");
            $table->text("comment")->nullable();
            $table->timestamps();
        });

        Schema::create("adv_banner_vehicle_type", function (Blueprint $table) {
            $table->id();
            $table->foreignId("adv_banner_id")->constrained()->cascadeOnDelete();
            $table->foreignId("vehicle_type_id")->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adv_banner_vehicle_type');
        Schema::dropIfExists('adv_banners');
    }
};
