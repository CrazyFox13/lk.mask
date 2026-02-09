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
        Schema::create('order_offer_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_offer_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // 'print_form' или 'signed_document'
            $table->string('url');
            $table->string('file_name')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('mime_type')->nullable();
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
        Schema::dropIfExists('order_offer_documents');
    }
};
