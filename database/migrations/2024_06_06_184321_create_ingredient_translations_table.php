<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIngredientTranslationsTable extends Migration
{
    public function up()
    {
        Schema::create('ingredient_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ingredient_id');
            $table->string('locale');
            $table->string('title');
            $table->timestamps();

            $table->unique(['ingredient_id', 'locale']);
            $table->foreign('ingredient_id')->references('id')->on('ingredients')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ingredient_translations');
    }
}
