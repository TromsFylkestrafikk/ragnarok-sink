<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RagnarokSinkTables extends Migration
{
    public function up(): void
    {
        Schema::create('ragnarok_files', function (Blueprint $table) {
            $table->increments('id');
            $table->char('sink_id', 64)->comment('Sink that owns this file');
            $table->char('name', 128)->comment('Name of file relative to disk');
            $table->unsignedBigInteger('size')->default(0)->comment('File size in bytes');
            $table->char('checksum', 128)->nullable()->comment('Md5 sum of file');
            $table->timestamps();
            $table->unique(['sink_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ragnarok_files');
    }
}
