<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('creator')->nullable();
            $table->unsignedInteger('assign_to')->nullable();
            $table->string('title', 150)->nullable();
            $table->text('description')->nullable();
            $table->text('value')->nullable();
            $table->string('source', 150)->nullable();
            $table->string('contact_person', 150)->nullable();
            $table->string('contact_email', 150)->nullable();
            $table->string('contact_phone', 150)->nullable();
            $table->string('contact_organization', 150)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->nullable();
            $table->string('feedback')->nullable();
            $table->timestamps();

            $table->index(['assign_to', 'creator']);
            $table->foreign('assign_to')->references('id')->on('users');
            $table->foreign('creator')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leads');
    }
}
