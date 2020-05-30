<?php

use Illuminate\Database\Migrations\Migration;

class EncryptPathsOfABackup extends Migration {


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::table('backups')->get()->each(function ($item) {
            try {
                if (isset($item->paths)) {
                    // if already encrypted decryption will not fail
                    // and we don't need to do anything with the record
                    decrypt($item->paths);
                }
            } catch (Throwable $decryptFailed) {
                try {
                    DB::table('backups')->where('id', $item->id)->update([
                        'paths' => encrypt($item->paths),
                    ]);
                } catch (Throwable $encryptFailed) {
                    report($encryptFailed);
                }
            }
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::table('backups')->get()->each(function ($item) {
            try {
                DB::table('backups')->where('id', $item->id)->update([
                    'paths' => decrypt($item->paths),
                ]);
            } catch (Throwable $decryptFailed) {
                report($decryptFailed);
            }
        });
    }
}
