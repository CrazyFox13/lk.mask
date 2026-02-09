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
        Schema::create("vehicle_categories",function (Blueprint $table){
            $table->id();
            $table->string("title");
            $table->string("image")->nullable();
            $table->string("color")->nullable();
            $table->boolean("show_in_menu")->default(false);
            $table->timestamps();
        });

        Schema::table("vehicle_groups",function (Blueprint $table){
            $table->foreignId("vehicle_category_id")->nullable()->constrained()->nullOnDelete();
            $table->string("image")->nullable();
            $table->string("color")->nullable();
            $table->boolean("show_in_menu")->default(false);
        });
        Schema::table("vehicle_types",function (Blueprint $table){
            $table->string("image")->nullable();
            $table->string("color")->nullable();
            $table->boolean("show_in_menu")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("vehicle_types",function (Blueprint $table){
           $table->dropColumn(["image","color","show_in_menu"]);
        });
        Schema::table("vehicle_groups",function (Blueprint $table){
            $table->dropConstrainedForeignId("vehicle_category_id");
           $table->dropColumn(["image","color","show_in_menu"]);
        });
        Schema::dropIfExists("vehicle_categories");
    }
};
