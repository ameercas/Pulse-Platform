<?php

class ScenarioTimeTableSeeder extends Seeder {

    public function run()
    {
        DB::table('scenario_time')->delete();

        \Beacon\Model\ScenarioTime::create(array(
            'sort' => 10,
            'name' => 'all_the_time'
        ));

        \Beacon\Model\ScenarioTime::create(array(
            'sort' => 20,
            'name' => 'between_two_times'
        ));

    }
}