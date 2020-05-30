<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatsColumnToRepositoriesTable extends Migration {


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('repositories', function (Blueprint $table) {
            $table->text('stats')->nullable()->after('bastion_id_rsa');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('repositories', function (Blueprint $table) {
            $table->dropColumn(['stats']);
        });
    }
}
