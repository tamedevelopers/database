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
        Schema::create('users_profile', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete('cascade');
            $table->string('avatar')->nullable();
            $table->string('per_page', 4)->default(50);
            $table->string('country', 100)->nullable();
            $table->string('country_iso', 5)->nullable();
            $table->string('country_iso2', 5)->nullable();
            $table->string('dialing_code', 5)->nullable();
            $table->set('likes', ['gaming', 'food', 'movies', 'eating', 'swimming'])->nullable();
            $table->enum('gender', [0, 1, 2])->default(0);
            $table->timestamp('dob')->nullable()->index();
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
        return Schema::dropTable('users_profile');
    }

    /**
     * drop database column
     * @param string $column 
     *
     * @return mixed
     */
    public function column(?string $column)
    {
        return Schema::dropColumn('users_profile', $column);
    }

};
