<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBackupsTable extends Migration {


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('backups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('repository_id');
            $table->string('borg_id');
            $table->string('name');
            $table->string('comment')->nullable();
            $table->string('hostname');
            $table->string('username');
            $table->float('duration', 16, 6);
            $table->text('limits');
            $table->timestamp('start')->nullable();
            $table->timestamp('end')->nullable();
            $table->text('stats');
            $table->text('paths');
            $table->timestamps();

            $table->foreign('repository_id')->references('id')->on('repositories')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('backups');
    }
}
