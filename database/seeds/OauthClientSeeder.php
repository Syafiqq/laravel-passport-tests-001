<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class OauthClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('oauth_clients')
            ->where('name', 'Password Grant Client')
            ->orWhere('name','ClientCredentials Grant Client')
            ->delete();
        Artisan::call('passport:client', ['--client' => true, '--name' => 'ClientCredentials Grant Client']);
        Artisan::call('passport:client', ['--password' => true, '--name' => 'Password Grant Client']);
    }
}
