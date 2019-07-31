<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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
        // @formatter:off
        $raw = [
            ['name' => 'user1', 'email' => 'user1@mail.com'],
            ['name' => 'user2', 'email' => 'user2@mail.com'],
            ['name' => 'user3', 'email' => 'user3@mail.com'],
            ['name' => 'user4', 'email' => 'user4@mail.com'],
        ];
        // @formatter:on

        DB::table('users')
            ->whereIn('email', Arr::pluck($raw, 'email'))
            ->delete();

        $users = factory(App\User::class, 4)->make();
        for ($i = -1, $is = count($users); ++$i < $is;)
        {
            /** @var \App\User $user */
            $user = $users[$i];
            $user->{'name'} = $raw[$i]['name'];
            $user->{'email'} = $raw[$i]['email'];
            $user->save();
        }
    }
}

?>
