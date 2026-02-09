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
        Schema::table('order_documents', function (Blueprint $table) {
            $table->string("mime_type")->nullable();
            $table->integer("file_size")->nullable();
        });
        Schema::table('report_documents', function (Blueprint $table) {
            $table->string("mime_type")->nullable();
            $table->integer("file_size")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_documents', function (Blueprint $table) {
            $table->dropColumn("mime_type");
            $table->dropColumn("file_size");
        });
        Schema::table('report_documents', function (Blueprint $table) {
            $table->dropColumn("mime_type");
            $table->dropColumn("file_size");
        });
    }
};
