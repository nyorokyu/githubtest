<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsvImportTemporaryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csv_import_temporary', function (Blueprint $table) {
            $table->id();
            $table->integer('fiscal_year')->length(4)->unsigned()->comment('年度');
            $table->string('bl_code')->comment('BLコード');
            $table->string('item_number')->comment('品番');
            $table->string('item_name')->comment('品名');
            $table->integer('quantity')->length(10)->comment('数量');
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
        Schema::dropIfExists('csv_import_temporary');
    }
}
