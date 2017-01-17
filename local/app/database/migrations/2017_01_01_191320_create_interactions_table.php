<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInteractionsTable extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
  Schema::create('interactions', function(Blueprint $table)
    {
      $table->bigIncrements('id');
      $table->integer('user_id')->unsigned();
      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
      $table->bigInteger('app_id')->unsigned()->nullable();
      $table->foreign('app_id')->references('id')->on('apps')->onDelete('set null');
      $table->bigInteger('site_id')->unsigned()->nullable();
      $table->foreign('site_id')->references('id')->on('sites')->onDelete('set null');

      $table->bigInteger('scenario_board_id')->unsigned()->nullable();
      $table->foreign('scenario_board_id')->references('id')->on('scenario_boards')->onDelete('set null');
      $table->bigInteger('scenario_id')->unsigned()->nullable();
      $table->foreign('scenario_id')->references('id')->on('scenarios')->onDelete('set null');
      $table->bigInteger('geofence_id')->unsigned()->nullable();
      $table->foreign('geofence_id')->references('id')->on('geofences')->onDelete('set null');
      $table->string('geofence', 64)->nullable();
      $table->bigInteger('beacon_id')->unsigned()->nullable();
      $table->foreign('beacon_id')->references('id')->on('beacons')->onDelete('set null');
      $table->string('beacon', 64)->nullable();
      $table->string('state', 32)->nullable();

      $table->string('url', 255)->nullable();
      $table->string('device_uuid', 36);
      $table->string('model', 64)->nullable();
      $table->string('platform', 32)->nullable();
      $table->decimal('lat', 17, 14)->nullable();
      $table->decimal('lng', 18, 15)->nullable();
      $table->timestamp('created_at')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::drop('interactions');
    Schema::drop('location_interactions');
  }

}
