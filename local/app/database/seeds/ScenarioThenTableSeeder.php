<?php

class ScenarioThenTableSeeder extends Seeder {

    public function run()
    {
        DB::table('scenario_then')->delete();

        \Beacon\Model\ScenarioThen::create(array(
            'sort' => 10,
            'name' => 'only_for_analytics',
            'active' => false
        ));

        \Beacon\Model\ScenarioThen::create(array(
            'sort' => 20,
            'name' => 'show_image'
        ));

        \Beacon\Model\ScenarioThen::create(array(
            'sort' => 30,
            'name' => 'show_template',
        ));

        \Beacon\Model\ScenarioThen::create(array(
            'sort' => 40,
            'name' => 'open_url'
        ));

        \Beacon\Model\ScenarioThen::create(array(
            'sort' => 50,
            'name' => 'play_video',
            'active' => false
        ));

        \Beacon\Model\ScenarioThen::create(array(
            'sort' => 60,
            'name' => 'play_sound',
            'active' => false
        ));

        \Beacon\Model\ScenarioThen::create(array(
            'sort' => 70,
            'name' => 'offer_discount',
            'active' => false
        ));

        \Beacon\Model\ScenarioThen::create(array(
            'sort' => 80,
            'name' => 'show_coupon',
            'active' => false
        ));

        \Beacon\Model\ScenarioThen::create(array(
            'sort' => 90,
            'name' => 'show_survey',
            'active' => false
        ));

        \Beacon\Model\ScenarioThen::create(array(
            'sort' => 100,
            'name' => 'reward_points',
            'active' => false
        ));

        \Beacon\Model\ScenarioThen::create(array(
            'sort' => 110,
            'name' => 'withdraw_points',
            'active' => false
        ));

        \Beacon\Model\ScenarioThen::create(array(
            'sort' => 12,
            'name' => 'show_app',
            'active' => true
        ));

        \Beacon\Model\ScenarioThen::create(array(
            'sort' => 14,
            'name' => 'show_site',
            'active' => true
        ));

    }
}