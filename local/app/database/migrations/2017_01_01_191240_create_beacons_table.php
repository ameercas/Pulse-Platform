<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBeaconsTable extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
  Schema::create('scenario_boards', function(Blueprint $table)
  {
    $table->bigIncrements('id');
    $table->integer('user_id')->unsigned();
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->string('name', 64);
    $table->string('timezone', 32)->default('UTC');
    $table->text('settings')->nullable();

    // Image
    $table->string('photo_file_name')->nullable();
    $table->integer('photo_file_size')->nullable();
    $table->string('photo_content_type')->nullable();
    $table->timestamp('photo_updated_at')->nullable();

    $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'));
$table->timestamp('updated_at')->nullable();
    $table->integer('created_by')->nullable();
    $table->integer('updated_by')->nullable();
  });

  // Creates the app_scenario_board (Many-to-Many relation) table
  Schema::create('app_scenario_board', function($table)
  {
    $table->bigIncrements('id')->unsigned();
    $table->bigInteger('app_id')->unsigned();
    $table->foreign('app_id')->references('id')->on('apps')->onDelete('cascade');
    $table->bigInteger('scenario_board_id')->unsigned();
    $table->foreign('scenario_board_id')->references('id')->on('scenario_boards')->onDelete('cascade');
  });

  // Creates the site_scenario_board (Many-to-Many relation) table
  Schema::create('site_scenario_board', function($table)
  {
    $table->bigIncrements('id')->unsigned();
    $table->bigInteger('site_id')->unsigned();
    $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
    $table->bigInteger('scenario_board_id')->unsigned();
    $table->foreign('scenario_board_id')->references('id')->on('scenario_boards')->onDelete('cascade');
  });

  Schema::create('scenario_if', function(Blueprint $table)
  {
    $table->increments('id');
    $table->integer('sort')->unsigned();
    $table->string('name', 64);
    $table->boolean('active')->default(true);
  });

  Schema::create('scenario_then', function(Blueprint $table)
  {
    $table->increments('id');
    $table->integer('sort')->unsigned();
    $table->string('name', 64);
    $table->boolean('active')->default(true);
  });

  Schema::create('scenario_day', function(Blueprint $table)
  {
    $table->increments('id');
    $table->integer('sort')->unsigned();
    $table->string('name', 64);
    $table->boolean('active')->default(true);
  });

  Schema::create('scenario_time', function(Blueprint $table)
  {
    $table->increments('id');
    $table->integer('sort')->unsigned();
    $table->string('name', 64);
    $table->boolean('active')->default(true);
  });

  Schema::create('location_groups', function($table)
  {
    $table->bigIncrements('id')->unsigned();
    $table->integer('user_id')->unsigned();
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->string('name', 64);
    $table->text('settings')->nullable();
  });

  Schema::create('scenarios', function(Blueprint $table)
  {
    $table->bigIncrements('id');
    $table->bigInteger('scenario_board_id')->unsigned();
    $table->foreign('scenario_board_id')->references('id')->on('scenario_boards')->onDelete('cascade');
    $table->integer('scenario_if_id')->unsigned()->default(1);
    $table->foreign('scenario_if_id')->references('id')->on('scenario_if')->onDelete('cascade');
    $table->integer('scenario_then_id')->unsigned()->nullable();
    $table->foreign('scenario_then_id')->references('id')->on('scenario_then')->onDelete('cascade');
    $table->integer('scenario_day_id')->unsigned()->default(1);
    $table->foreign('scenario_day_id')->references('id')->on('scenario_day')->onDelete('cascade');
    $table->integer('scenario_time_id')->unsigned()->default(1);
    $table->foreign('scenario_time_id')->references('id')->on('scenario_time')->onDelete('cascade');  
    $table->time('time_start')->nullable();
    $table->time('time_end')->nullable();
    $table->date('date_start')->nullable();
    $table->date('date_end')->nullable();
    $table->integer('frequency')->unsigned()->default(0);
    $table->integer('delay')->unsigned()->default(0);
    $table->text('notification')->nullable();
    $table->mediumText('settings')->nullable();
    $table->boolean('active')->default(true);
    $table->text('show_image')->nullable();
    $table->mediumText('template')->nullable();
    $table->text('open_url')->nullable();
    $table->bigInteger('show_app')->unsigned()->nullable();
    $table->bigInteger('show_app_page')->unsigned()->nullable();
    $table->bigInteger('show_site')->unsigned()->nullable();
    $table->text('play_sound')->nullable();
    $table->text('play_video')->nullable();
    $table->integer('add_points')->unsigned()->nullable();
    $table->integer('substract_points')->unsigned()->nullable();

    // Image
    $table->string('image_file_name')->nullable();
    $table->integer('image_file_size')->nullable();
    $table->string('image_content_type')->nullable();
    $table->timestamp('image_updated_at')->nullable();

    $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'));
$table->timestamp('updated_at')->nullable();
    $table->integer('created_by')->nullable();
    $table->integer('updated_by')->nullable();
  });

  Schema::create('beacons', function(Blueprint $table)
  {
    $table->bigIncrements('id');
    $table->integer('user_id')->unsigned();
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->bigInteger('location_group_id')->unsigned()->nullable();
    $table->foreign('location_group_id')->references('id')->on('location_groups');
    $table->string('name', 64);
    $table->text('description')->nullable();
    $table->string('uuid', 42)->nullable();
    $table->bigInteger('major')->nullable()->unsigned();
    $table->bigInteger('minor')->nullable()->unsigned();
    $table->decimal('lat', 17, 14)->nullable();
    $table->decimal('lng', 18, 15)->nullable();

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

  // Creates the beacon_scenario (Many-to-Many relation) table
  Schema::create('beacon_scenario', function($table)
  {
    $table->bigIncrements('id')->unsigned();
    $table->bigInteger('beacon_id')->unsigned();
    $table->bigInteger('scenario_id')->unsigned();
    $table->foreign('beacon_id')->references('id')->on('beacons')->onDelete('cascade');
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
  Schema::table('beacon_scenario', function(Blueprint $table) {
    $table->dropForeign('beacon_scenario_scenario_id_foreign');
    $table->dropForeign('beacon_scenario_beacon_id_foreign');
  });
  Schema::drop('beacon_scenario');

  Schema::table('beacons', function(Blueprint $table) {
    $table->dropForeign('beacons_user_id_foreign');
    $table->dropForeign('beacons_location_group_id_foreign');
  });
  Schema::drop('beacons');

  Schema::table('location_groups', function(Blueprint $table) {
    $table->dropForeign('location_groups_user_id_foreign');
  });
  Schema::drop('location_groups');

  Schema::table('scenarios', function(Blueprint $table) {
    $table->dropForeign('scenarios_scenario_board_id_foreign');
    $table->dropForeign('scenarios_scenario_time_id_foreign');
    $table->dropForeign('scenarios_scenario_day_id_foreign');
    $table->dropForeign('scenarios_scenario_if_id_foreign');
    $table->dropForeign('scenarios_scenario_then_id_foreign');
  });
  Schema::drop('scenarios');

  Schema::table('app_scenario_board', function(Blueprint $table) {
    $table->dropForeign('app_scenario_board_scenario_board_id_foreign');
    $table->dropForeign('app_scenario_board_app_id_foreign');
  });
  Schema::drop('app_scenario_board');

  Schema::table('scenario_boards', function(Blueprint $table) {
    $table->dropForeign('scenario_boards_user_id_foreign');
  });
  Schema::drop('scenario_boards');

  Schema::drop('scenario_if');

  Schema::drop('scenario_then');

  Schema::drop('scenario_day');

  Schema::drop('scenario_time');
  }

}
