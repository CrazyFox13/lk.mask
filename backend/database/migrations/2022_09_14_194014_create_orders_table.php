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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->on('users')->references('id');
            $table->foreignId('vehicle_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->integer('vehicles_count')->default(1);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('finish_date')->nullable();
            $table->string('amount_type')->nullable();
            $table->float('amount_account_vat')->default(0);
            $table->float('amount_account')->default(0);
            $table->float('amount_cash')->default(0);
            $table->boolean('amount_by_agreement')->default(0);
            $table->text('description')->nullable();
            $table->integer('views_count')->default(0);
            $table->string('moderation_status')->default('draft');
            $table->text('moderation_text')->nullable();
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
        Schema::dropIfExists('orders');
    }
};
