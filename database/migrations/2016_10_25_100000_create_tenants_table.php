<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->increments('id');
            
            $table->string('name', 100);
            $table->string('subdomain', 100)->index()->unique();
            $table->string('dbhost', 100);
            $table->string('dbdatabase', 100);
            $table->string('dbusername', 100);
            $table->string('dbpassword', 100);

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
        Schema::drop('tenants');
    }
}
