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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('inn')->nullable();
            $table->string('title')->nullable();
            $table->string('full_title')->nullable();
            $table->string('ogrn')->nullable();
            $table->string('kpp')->nullable();
            $table->string('okpo')->nullable();
            $table->text('legal_address')->nullable();
            $table->text('address')->nullable();
            $table->string('director')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->string('moderation_status')->default('draft');
            $table->text('moderation_message')->nullable();
            $table->float('rating')->default(0);
            $table->timestamp('last_online_datetime')->nullable();
            $table->timestamps();
        });

        Schema::table('users',function (Blueprint $table){
           $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
           $table->string('company_role')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users',function (Blueprint $table){
            $table->dropConstrainedForeignId('company_id');
            $table->dropColumn('company_role');
        });
        Schema::dropIfExists('companies');
    }
};
