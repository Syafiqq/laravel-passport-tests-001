<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

/**
 * This <laravel-passport> project created by :
 * Name         : syafiq
 * Date / Time  : 27 July 2019, 11:10 AM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */
class UsersTableSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $users = factory(App\User::class, 3)->make();
        Log::debug(var_export($users, true));
        foreach ($users as &$user){
            $user->save();
        }
    }
}

?>
