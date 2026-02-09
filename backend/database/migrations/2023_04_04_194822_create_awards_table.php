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
        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->string("icon");
            $table->string("name");
            $table->text("description")->nullable();
            $table->timestamps();
        });

        Schema::create('award_company', function (Blueprint $table) {
            $table->id();
            $table->foreignId("award_id")->constrained()->cascadeOnDelete();
            $table->foreignId("company_id")->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('award_company');
        Schema::dropIfExists('awards');
    }
};
