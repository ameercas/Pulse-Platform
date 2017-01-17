<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();

		$this->call('ResellerTableSeeder');
		$this->call('PlanTableSeeder');
		$this->call('UserTableSeeder');
		$this->call('RoleTableSeeder');
		$this->call('PermissionTableSeeder');
		$this->call('AssignedRoleTableSeeder');
		$this->call('AppTypeTableSeeder');
		$this->call('ScenarioIfTableSeeder');
		$this->call('ScenarioThenTableSeeder');
		$this->call('ScenarioDayTableSeeder');
		$this->call('ScenarioTimeTableSeeder');
		$this->call('SiteTypeTableSeeder');
		$this->call('LeadIndustryTableSeeder');

	}

}