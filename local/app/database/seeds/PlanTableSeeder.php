<?php

class PlanTableSeeder extends Seeder {

    public function run()
    {
        DB::table('plans')->delete();

        \App\Model\Plan::create(array(
            'reseller_id' => 1,
            'name' => 'Free',
            'sort' => 10,
            'undeletable' => 1,
            'settings' => '{"order_url":"","upgrade_url":"","product_id":"","interactions":"100","max_boards":"1","max_scenarios":"2","disk_space":"1","max_apps":"1","max_sites":"1","max_beacons":"1","max_geofences":"1","domain":"0","download":false,"team":false,"widgets":["about-us","call-us","contact-us","content"],"monthly":"0","annual":"0","currency":"USD","featured":false}'
        ));

		\App\Model\Plan::create(array(
            'reseller_id' => 1,
            'name' => 'Pro',
            'sort' => 20,
            'settings' => '{"order_url":"","upgrade_url":"","product_id":"","interactions":"1000","max_boards":"3","max_scenarios":"3","disk_space":"5","max_apps":"3","max_sites":"3","max_beacons":"3","max_geofences":"3","domain":"1","download":false,"team":false,"widgets":["about-us","call-us","catalogs","contact-us","content","coupons","e-commerce","email-us","events","facebook","flickr","forms","home-screen","instagram","loyalty-cards","map","photos","rss","soundcloud","twitter","vcard","video","web-page","youtube"],"monthly":"12","annual":"10","currency":"USD","featured":false}'
        ));

		\App\Model\Plan::create(array(
            'reseller_id' => 1,
            'name' => 'Marketer',
            'sort' => 30,
            'settings' => '{"order_url":"","upgrade_url":"","product_id":"","interactions":"3000","max_boards":"6","max_scenarios":"6","disk_space":"10","max_apps":"9","max_sites":"9","max_beacons":"9","max_geofences":"9","domain":"1","download":false,"team":false,"widgets":["about-us","call-us","catalogs","contact-us","content","coupons","e-commerce","email-us","events","facebook","flickr","forms","home-screen","instagram","loyalty-cards","map","photos","rss","soundcloud","twitter","vcard","video","web-page","youtube"],"monthly":"29","annual":"26","currency":"USD","featured":false}'
        ));

		\App\Model\Plan::create(array(
            'reseller_id' => 1,
            'name' => 'Business',
            'sort' => 40,
            'settings' => '{"order_url":"","upgrade_url":"","product_id":"","interactions":"10000","max_boards":"10","max_scenarios":"10","disk_space":"100","max_apps":"20","max_sites":"20","max_beacons":"20","max_geofences":"20","domain":"1","download":false,"team":false,"widgets":["about-us","call-us","catalogs","contact-us","content","coupons","e-commerce","email-us","events","facebook","flickr","forms","home-screen","instagram","loyalty-cards","map","photos","rss","soundcloud","twitter","vcard","video","web-page","youtube"],"monthly":"49","annual":"45","currency":"USD","featured":false}'
        ));

    }
}