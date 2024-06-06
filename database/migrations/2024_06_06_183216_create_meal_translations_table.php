<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMealTranslationsTable extends Migration
{
    public function up()
    {
        Schema::create('meal_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meal_id');
            $table->string('locale');
            $table->string('title');
            $table->text('description');
            $table->timestamps();

            $table->unique(['meal_id', 'locale']);
            $table->foreign('meal_id')->references('id')->on('meals')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('meal_translations');
    }
}
