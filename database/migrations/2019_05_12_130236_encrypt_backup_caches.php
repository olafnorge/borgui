<?php

use Illuminate\Database\Migrations\Migration;

class EncryptBackupCaches extends Migration {


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (str_is('database', config('cache.default'))) {
            DB::table('cache')->where('key', 'LIKE', sprintf('%s%%', config('cache.prefix')))->delete();
        }
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        // migration can not be reverted
    }
}

