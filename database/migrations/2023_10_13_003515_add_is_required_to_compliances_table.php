<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('compliances', function (Blueprint $table) {
            $table->integer('is_required')->default(0)->after('remarks');
        });
        Schema::table('user_compliances', function (Blueprint $table) {
            $table->string('document')->nullable()->after('comment');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('compliances', function (Blueprint $table) {
            //
        });
    }
};
