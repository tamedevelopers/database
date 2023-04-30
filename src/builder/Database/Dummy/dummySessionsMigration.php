<?php

use builder\Database\Migrations\Schema;
use builder\Database\Migrations\Blueprint;
use builder\Database\Migrations\Migration;


return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dummy_table', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index()->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Drop database table
     *
     * @return void
     */
    public function drop()
    {
        Schema::dropTable('dummy_table');
    }

    /**
     * drop database column
     * @param string $column 
     *
     * @return void
     */
    public function column(?string $column)
    {
        Schema::dropColumn('dummy_table', $column);
    }

};
