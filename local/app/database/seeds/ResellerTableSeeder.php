<?php

class ResellerTableSeeder extends Seeder {

  public function run()
  {
    DB::table('resellers')->delete();

    $url_parts = parse_url(URL::current());

    \App\Model\Reseller::create(array(
      'domain' => $url_parts['host']
    ));

  }
}