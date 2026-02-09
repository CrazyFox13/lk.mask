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
        Schema::create('push_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->text('text');
            $table->dateTime('send_at')->nullable();
            $table->float('progress')->default(0);
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('push_notification_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('push_notification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('sent_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('push_notification_user');
        Schema::dropIfExists('push_notifications');
    }
};
