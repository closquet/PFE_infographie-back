<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAllergenIngredientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('allergen_ingredient', function (Blueprint $table) {
            $table->bigInteger('allergen_id')->unsigned()->index();
            $table->bigInteger('ingredient_id')->unsigned()->index();
            $table->timestamps();
            $table->foreign('allergen_id')->references('id')->on('allergens')->onDelete('cascade');
            $table->foreign('ingredient_id')->references('id')->on('ingredients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('allergen_ingredient');
    }
}
