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
        Schema::table("chats", function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after("report_id")->constrained()->nullOnDelete();
        });

        Schema::table("chat_user", function (Blueprint $table) {
            $table->timestamp("muted_at")->nullable();
            $table->timestamp("blocked_at")->nullable();
        });

        Schema::table("messages", function (Blueprint $table) {
            $table->foreignId('reply_message_id')->after("author_id")->nullable()->constrained()->references("id")->on("messages")->nullOnDelete();
            $table->timestamp("sent_at")->nullable();
            $table->timestamp("delivered_at")->nullable();
            $table->timestamp("read_at")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("messages", function (Blueprint $table) {
            $table->dropConstrainedForeignId('reply_message_id');
            $table->dropColumn(["sent_at","delivered_at","read_at"]);
        });

        Schema::table("chat_user", function (Blueprint $table) {
            $table->dropColumn(["muted_at","blocked_at"]);
        });

        Schema::table("chats", function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
        });
    }
};
