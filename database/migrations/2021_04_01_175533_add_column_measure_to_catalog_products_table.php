<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnMeasureToCatalogProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('catalog_products', function (Blueprint $table) {
            $table->string('measure')->default('')->after('on_request')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('catalog_products', function (Blueprint $table) {
            $table->dropColumn(['measure']);
        });
    }
}
