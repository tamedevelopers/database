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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('phone', 15)->index()->nullable();
            $table->string('password')->nullable();
            $table->string('language', 4)->default('en');
            $table->decimal('wallet ', 15, 2)->default(0.00);
            $table->enum('role_id', [0, 1, 2])->default(0);
            $table->enum('is_active', [0, 1])->default(1)->index();
            $table->enum('activity_notification', [0, 1])->default(1)->index();
            $table->enum('email_notification', [0, 1])->default(1)->index();
            $table->enum('sms_notification', [0, 1])->default(1)->index();
            $table->string('access_token')->unique()->nullable();
            $table->string('remember_token', 100)->index()->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
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
        return Schema::dropTable('users', $force);
    }

};
