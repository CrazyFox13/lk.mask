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
        Schema::create('notification_types', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('notification_type_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId("notification_type_id")->constrained()->cascadeOnDelete();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->enum('way', ['push', 'email']);
            $table->boolean('active')->default(0);
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
        Schema::dropIfExists('notification_type_user');
        Schema::dropIfExists('notification_types');
    }
};
