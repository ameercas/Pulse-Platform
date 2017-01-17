<?php

class RoleTableSeeder extends Seeder {

    public function run()
    {
        DB::table('roles')->delete();

        $role_reseller = Role::create(array(
            'name' => 'Reseller'
        ));

        Role::create(array(
            'name' => 'Admin'
        ));

        Role::create(array(
            'name' => 'Manager'
        ));

        Role::create(array(
            'name' => 'General User'
        ));

        Role::create(array(
            'name' => 'App User'
        ));

        Role::create(array(
            'name' => 'Beacon User'
        ));

        Role::create(array(
            'name' => 'Site User'
        ));

        Role::create(array(
            'name' => 'Beta tester full'
        ));

        Role::create(array(
            'name' => 'Beta tester 1'
        ));

        Role::create(array(
            'name' => 'Beta tester 2'
        ));

        Role::create(array(
            'name' => 'Beta tester 3'
        ));
    }
}