<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsvClientHead extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csv_client_head', function (Blueprint $table) {
            $table->id();
            $table->integer('fiscal_year')->length(4)->unsigned()->comment('年度');
            $table->string('company_name')->comment('企業名');
            $table->integer('receiving_count')->length(10)->unsigned()->comment('入庫台数');
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
        Schema::dropIfExists('csv_client_head');
    }
}
