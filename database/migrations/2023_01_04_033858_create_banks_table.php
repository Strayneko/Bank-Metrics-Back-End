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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->tinyInteger('loaning_percentage');
            $table->tinyInteger('max_age');
            $table->tinyInteger('min_age')->default(18);
            // 0. single
            // 1. married
            // 2. both
            $table->tinyInteger('marital_status');
            // 0. WNI
            // 1. WNA
            // 2. both
            $table->tinyInteger('nationality');
            // 0. half-time
            // 1. full-time
            // 2. both
            $table->tinyInteger('employment');
            $table->softDeletes();
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
        Schema::dropIfExists('banks');
    }
};
