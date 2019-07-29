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
        $user = \App\User::first();
        DB::table('oauth_clients')
            ->where('name', 'Password Grant Client')
            ->orWhere('name','ClientCredentials Grant Client')
            ->orWhere('name','AuthorizeCode Grant Client')
            ->orWhere('name','Implicit Grant Client')
            ->delete();
        Artisan::call('passport:client', ['--client' => true, '--name' => 'ClientCredentials Grant Client']);
        Artisan::call('passport:client', ['--password' => true, '--name' => 'Password Grant Client', '--redirect_uri' => url('/')]);
        Artisan::call('passport:client', ['--name' => 'AuthorizeCode Grant Client', '--redirect_uri' => url('/'), '--user_id' => $user->{'id'}]);
        Artisan::call('passport:client', ['--name' => 'Implicit Grant Client', '--redirect_uri' => url('/'), '--user_id' => $user->{'id'}]);
    }
}
