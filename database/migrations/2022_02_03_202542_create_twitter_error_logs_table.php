<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwitterErrorLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twitter_error_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tweet_id')->unsigned()->index()->comment("The tweet the error corresponds to.");
            $table->longText('error')->comment("The error message returned from the Twitter API.");
            $table->timestamps();
            $table->foreign('tweet_id')->references('id')->on('tweets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('twitter_error_logs');
    }
}
