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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('surname')->nullable();
            $table->string('phone')->unique();
            $table->string('phone_verified_at')->nullable();
            $table->string('phone_code')->nullable();
            $table->timestamp('phone_code_sent_at')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('email_code')->nullable();
            $table->timestamp('email_code_sent_at')->nullable();
            $table->string('password');
            $table->boolean("temp_password")->default(1);
            $table->string('type')->default('user');
            $table->string('avatar')->nullable();
            $table->float('rating')->default(0);
            $table->timestamp('last_online_datetime')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
