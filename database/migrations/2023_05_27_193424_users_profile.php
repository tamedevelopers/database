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
     * @param bool $force 
     * [optional] Default is false
     * Force drop all tables or throw an error on Foreign keys
     * 
     * @return mixed
     */
    public function drop($force = false)
    {
        return Schema::dropTable('users_profile', $force);
    }

};
