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
            $table->json('payload')->nullable();
            $table->json('file')->nullable();
            $table->string('email');
            $table->string('subject');
            $table->longText('message');
            $table->text('alt_message')->nullable();
            $table->string('error')->nullable();
            $table->string('type')->nullable();
            $table->integer('status')->default(0)->index();
            $table->integer('attempts')->default(0)->index();
            $table->timestamps();
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
