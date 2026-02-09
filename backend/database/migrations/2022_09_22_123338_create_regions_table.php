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
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->after('city_id')->constrained()->nullOnDelete();
        });

        Schema::table('order_addresses', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->after('city_id')->constrained()->nullOnDelete();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('region_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('region_id');
        });
        Schema::table('order_addresses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('region_id');
        });

        Schema::dropIfExists('regions');
    }
};
