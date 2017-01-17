<?php

class PermissionTableSeeder extends Seeder {

    public function run()
    {
        DB::table('permissions')->delete();

		// Permissions
        $system_management = Permission::create(array(
            'name' => 'system_management',
            'display_name' => 'System Management'
        ));

        $user_management = Permission::create(array(
            'name' => 'user_management',
            'display_name' => 'User Management'
        ));

        $general_management = Permission::create(array(
            'name' => 'general_management',
            'display_name' => 'General Management'
        ));

        $app_management = Permission::create(array(
            'name' => 'app_management',
            'display_name' => 'App Management'
        ));

        $beacon_management = Permission::create(array(
            'name' => 'beacon_management',
            'display_name' => 'Beacon Management'
        ));

        $site_management = Permission::create(array(
            'name' => 'site_management',
            'display_name' => 'Site Management'
        ));

        $beta_feature1 = Permission::create(array(
            'name' => 'beta_feature1',
            'display_name' => 'Beta Feature 1'
        ));

        $beta_feature2 = Permission::create(array(
            'name' => 'beta_feature2',
            'display_name' => 'Beta Feature 2'
        ));

        $beta_feature3 = Permission::create(array(
            'name' => 'beta_feature3',
            'display_name' => 'Beta Feature 3'
        ));

		// Reseller
		$owner = Role::find(1);
		$owner->perms()->sync(array(
            $system_management->id, 
            $user_management->id,
            $general_management->id,
            $app_management->id,
            $beacon_management->id,
            $site_management->id
        ));

		// Admin
		$admin = Role::find(2);
		$admin->perms()->sync(array(
            $user_management->id,
            $general_management->id,
            $app_management->id,
            $beacon_management->id,
            $site_management->id
        ));

		// Manager
		$manager = Role::find(3);
		$manager->perms()->sync(array(
            $general_management->id,
            $app_management->id,
            $beacon_management->id,
            $site_management->id
        ));

		// General User
		$manager = Role::find(4);
		$manager->perms()->sync(array(
            $app_management->id,
            $beacon_management->id,
            $site_management->id
        ));

		// App User
		$manager = Role::find(5);
		$manager->perms()->sync(array(
            $app_management->id
        ));

		// Beacon User
		$manager = Role::find(6);
		$manager->perms()->sync(array(
            $beacon_management->id
        ));

		// Site User
		$manager = Role::find(7);
		$manager->perms()->sync(array(
            $site_management->id
        ));

		// Beta tester full
		$manager = Role::find(8);
		$manager->perms()->sync(array(
            $system_management->id, 
            $user_management->id,
            $general_management->id,
            $beta_feature1->id,
            $beta_feature2->id,
            $beta_feature3->id
        ));

		// Beta tester 1
		$manager = Role::find(9);
		$manager->perms()->sync(array(
            $user_management->id,
            $general_management->id,
            $beta_feature1->id
        ));

		// Beta tester 2
		$manager = Role::find(10);
		$manager->perms()->sync(array(
            $user_management->id,
            $general_management->id,
            $beta_feature2->id
        ));

		// Beta tester 3
		$manager = Role::find(11);
		$manager->perms()->sync(array(
            $user_management->id,
            $general_management->id,
            $beta_feature3->id
        ));
    }
}