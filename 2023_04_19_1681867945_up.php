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
        Schema::create('up', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')
                ->references('tb_user')
                ->on('user_id')
                ->onDelete('cascade')
                ->onUpdate('no action');
            $table->string('username')->unique()->nullable();
            $table->string('member_id', 20)->unique()->nullable();
            $table->string('email', 200)->nullable()->unique();
            $table->string('country', 5)->nullable()->index();
            $table->enum('gender', [0, 1, 2])->default(0);
            $table->timestamp('dob')->nullable()->index();
            $table->decimal('max_amount', 10, 2)->default(0.00);
            $table->text('product_items');
            $table->bigInteger('clicks')->default(0)->index();
            $table->bigIncrements('mare')->index();
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
        Schema::dropTable('up');
    }

    /**
     * drop database column
     * @param string $column 
     *
     * @return void
     */
    public function column(?string $column)
    {
        Schema::dropColumn('up', $column);
    }

};
