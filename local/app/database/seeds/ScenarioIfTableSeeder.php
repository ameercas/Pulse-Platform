<?php

class ScenarioIfTableSeeder extends Seeder {

    public function run()
    {
        DB::table('scenario_if')->delete();

        \Beacon\Model\ScenarioIf::create(array(
            'sort' => 10,
            'name' => 'enters_region_of'
        ));

        \Beacon\Model\ScenarioIf::create(array(
            'sort' => 20,
            'name' => 'exits_region_of'
        ));

        \Beacon\Model\ScenarioIf::create(array(
            'sort' => 30,
            'name' => 'is_far_from'
        ));

        \Beacon\Model\ScenarioIf::create(array(
            'sort' => 40,
            'name' => 'is_near'
        ));

        \Beacon\Model\ScenarioIf::create(array(
            'sort' => 50,
            'name' => 'is_very_near'
        ));
    }
}