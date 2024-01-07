<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      // now  $this->call(RolesAndPermissionsSeeder::class);
         $this->call(CountriesTableSeeder::class);
      //now  $this->call(AdminSeeder::class);
         $this->call(TimeZoneSeeder::class);
         $this->call(DriverNeededDocumentSeeder::class);
         $this->call(CarMakeAndModelSeeder::class);
    // now    $this->call(SettingsSeeder::class);
         $this->call(CancellationReasonSeeder::class);
         $this->call(ComplaintTitleSeeder::class);
      // now now    $this->call(FrontpageHeaderTableSeeder::class);
         $this->call(GoodsTypeSeeder::class);
         $this->call(MailTemplateSeeder::class);


    }
}
