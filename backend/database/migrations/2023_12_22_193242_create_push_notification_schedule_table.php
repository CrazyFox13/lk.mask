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
        Schema::create('push_notification_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('push_notification_id')->constrained()->cascadeOnDelete();
            $table->dateTime("time");
            $table->enum("status",["waiting","running","completed"])->default("waiting");
            $table->timestamps();
        });

        Schema::table("push_notification_user", function (Blueprint $table) {
            $table->foreignId('schedule_id')->nullable()
                ->constrained()
                ->references("id")->on("push_notification_schedules")
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("push_notification_user", function (Blueprint $table) {
            $table->dropForeign('push_notification_user_schedule_id_foreign');
            $table->dropColumn('schedule_id');
        });

        Schema::dropIfExists('push_notification_schedules');
    }
};
