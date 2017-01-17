<?php

class ScenarioDayTableSeeder extends Seeder {

    public function run()
    {
        DB::table('scenario_day')->delete();

        \Beacon\Model\ScenarioDay::create(array(
            'sort' => 10,
            'name' => 'every_day'
        ));

        \Beacon\Model\ScenarioDay::create(array(
            'sort' => 20,
            'name' => 'between_two_dates'
        ));

        \Beacon\Model\ScenarioDay::create(array(
            'sort' => 30,
            'name' => 'saturday_and_sunday'
        ));

        \Beacon\Model\ScenarioDay::create(array(
            'sort' => 40,
            'name' => 'friday_and_saturday'
        ));

        \Beacon\Model\ScenarioDay::create(array(
            'sort' => 50,
            'name' => 'monday_to_friday'
        ));

        \Beacon\Model\ScenarioDay::create(array(
            'sort' => 60,
            'name' => 'sunday_to_thursday'
        ));

        \Beacon\Model\ScenarioDay::create(array(
            'sort' => 70,
            'name' => 'monday'
        ));

        \Beacon\Model\ScenarioDay::create(array(
            'sort' => 80,
            'name' => 'tuesday'
        ));

        \Beacon\Model\ScenarioDay::create(array(
            'sort' => 90,
            'name' => 'wednesday'
        ));

        \Beacon\Model\ScenarioDay::create(array(
            'sort' => 100,
            'name' => 'thursday'
        ));

        \Beacon\Model\ScenarioDay::create(array(
            'sort' => 110,
            'name' => 'friday'
        ));

        \Beacon\Model\ScenarioDay::create(array(
            'sort' => 120,
            'name' => 'saturday'
        ));

        \Beacon\Model\ScenarioDay::create(array(
            'sort' => 130,
            'name' => 'sunday'
        ));

    }
}