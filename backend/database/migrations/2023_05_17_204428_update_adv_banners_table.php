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
        Schema::table("adv_banners", function (Blueprint $table) {
            $table->integer("views")->default(0);
            $table->integer("clicks")->default(0);
        });

        Schema::create('adv_banner_user_session', function (Blueprint $table) {
            $table->id();
            $table->foreignId("adv_banner_id")->constrained()->cascadeOnDelete();
            $table->foreignId("user_session_id")->constrained()->cascadeOnDelete();
            $table->dateTime("clicked_at")->nullable();
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
        Schema::table("adv_banners", function (Blueprint $table) {
            $table->dropColumn("views");
            $table->dropColumn("clicks");
        });

        Schema::drop('adv_banner_user_session');
    }
};
