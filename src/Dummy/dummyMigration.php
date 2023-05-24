<?php

use builder\Database\Migrations\Schema;
use builder\Database\Migrations\Blueprint;
use builder\Database\Migrations\Migration;


return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return mixed
     */
    public function up()
    {
        Schema::create('dummy_table', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    /**
     * Drop database table
     *
     * @return mixed
     */
    public function drop()
    {
        return Schema::dropTable('dummy_table');
    }

    /**
     * drop database column
     * @param string $column 
     *
     * @return mixed
     */
    public function column(?string $column)
    {
        return Schema::dropColumn('dummy_table', $column);
    }

};
