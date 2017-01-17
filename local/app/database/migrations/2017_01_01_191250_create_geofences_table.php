<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeofencesTable extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('geofences', function(Blueprint $table)
    {
      $table->bigIncrements('id');
      $table->integer('user_id')->unsigned();
      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
      $table->bigInteger('location_group_id')->unsigned()->nullable();
      $table->foreign('location_group_id')->references('id')->on('location_groups');
      $table->string('name', 64);
      $table->text('description')->nullable();
      $table->decimal('lat', 17, 14)->nullable();
      $table->decimal('lng', 18, 15)->nullable();
      $table->integer('radius')->nullable()->unsigned();
      $table->string('country', 24)->nullable();
      $table->string('region', 32)->nullable();
      $table->string('city', 24)->nullable();
      $table->string('address', 250)->nullable();

      $table->string('photo_file_name')->nullable();
      $table->integer('photo_file_size')->nullable();
      $table->string('photo_content_type')->nullable();
      $table->timestamp('photo_updated_at')->nullable();

      $table->mediumText('settings')->nullable();
      $table->boolean('active')->default(true);
      $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'));
      $table->timestamp('updated_at')->nullable();
      $table->integer('created_by')->nullable();
      $table->integer('updated_by')->nullable();
    });

    // Creates the geofence_scenario (Many-to-Many relation) table
    Schema::create('geofence_scenario', function($table)
    {
      $table->bigIncrements('id')->unsigned();
      $table->bigInteger('geofence_id')->unsigned();
      $table->bigInteger('scenario_id')->unsigned();
      $table->foreign('geofence_id')->references('id')->on('geofences')->onDelete('cascade');
      $table->foreign('scenario_id')->references('id')->on('scenarios')->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('geofence_scenario', function(Blueprint $table) {
      $table->dropForeign('geofence_scenario_scenario_id_foreign');
      $table->dropForeign('geofence_scenario_geofence_id_foreign');
    });
    Schema::drop('geofence_scenario');

    Schema::table('geofences', function(Blueprint $table) {
      $table->dropForeign('geofences_user_id_foreign');
      $table->dropForeign('geofences_location_group_id_foreign');
    });
    Schema::drop('geofences');
  }

}
