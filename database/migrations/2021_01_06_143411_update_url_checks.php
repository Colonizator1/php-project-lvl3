<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUrlChecks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('url_checks', function (Blueprint $table) {
            $table->enum('status', ['success', 'failed', 'pending'])->default('success');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('url_checks', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
