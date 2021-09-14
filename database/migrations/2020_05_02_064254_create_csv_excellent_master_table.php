<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsvExcellentMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csv_excellent_master', function (Blueprint $table) {
            $table->id();
            $table->integer('master_id')->unsigned()->comment('マスタID');
            $table->string('bl_code')->comment('BLコード');
            $table->string('item_number')->nullable()->comment('品番');
            $table->string('item_name')->comment('品名');
            $table->integer('quantity')->length(10)->comment('数量');
            $table->boolean('is_deleted')->default(false);
            $table->integer('created_user')->length(20);
            $table->integer('updated_user')->length(20);
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
        Schema::dropIfExists('csv_excellent_master');
    }
}
