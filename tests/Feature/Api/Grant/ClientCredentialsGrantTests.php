<?php
/**
 * This <laravel-passport> project created by :
 * Name         : syafiq
 * Date / Time  : 28 July 2019, 4:46 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace Tests\Feature\Api;


use App\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClientCredentialsGrantTests extends TestCase
{
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first();
        self::assertThat($this->user, self::logicalNot(self::isNull()));
    }

    protected function tearDown(): void
    {
        DB::table('sessions')->delete();
        parent::tearDown();
    }


    public function test_it_can_login()
    {
        $response = $this->post('/login', [
            'email' => $this->user->{'email'},
            'password' => 'password'
        ]);
        $response = $this->get('/login');
        // var_dump($response);
        self::assertThat($response->status(), self::equalTo(302));
    }


    public function test_it_can_login_and_logout()
    {
        $response = $this->post('/login', [
            'email' => $this->user->{'email'},
            'password' => 'password'
        ]);
        $response = $this->post('/logout');
        // var_dump($response);
        self::assertThat($response->status(), self::equalTo(302));
    }


    public function test_it_access_authorization_server()
    {
        $response = $this->post('/login', [
            'email' => $this->user->{'email'},
            'password' => 'password'
        ]);

        $response = $this->post('/logout');
        // var_dump($response);
        self::assertThat($response->status(), self::equalTo(302));
    }
}

?>
