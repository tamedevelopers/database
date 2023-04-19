<?php

use UltimateOrmDatabase\Migration\Schema;
use UltimateOrmDatabase\Migration\Blueprint;
use UltimateOrmDatabase\Migration\Migration;


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
