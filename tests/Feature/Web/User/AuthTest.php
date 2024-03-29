<?php
/**
 * This <laravel-passport> project created by :
 * Name         : syafiq
 * Date / Time  : 28 July 2019, 7:28 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace Tests\Feature\Web\User;


use App\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthTest extends TestCase
{
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->startSession();
        $this->user = User::where('email', 'user1@mail.com')
            ->first();
        $this->user->{'password'} = 'password';
        self::assertThat($this->user, self::logicalNot(self::isNull()));
    }

    protected function tearDown(): void
    {
        $this->flushSession();
        DB::table('sessions')->delete();
        parent::tearDown();
    }


    public function test_it_can_login()
    {
        $response = $this->post('/login', [
            'email' => $this->user->{'email'},
            'password' => $this->user->{'password'},
            '_token' => csrf_token(),
        ]);
        $response = $this->get('/login');
        // var_dump($response);
        self::assertThat($response->status(), self::equalTo(302));
    }


    public function test_it_can_login_and_logout()
    {
        $response = $this->post('/login', [
            'email' => $this->user->{'email'},
            'password' => $this->user->{'password'},
            '_token' => csrf_token(),
        ]);
        $response = $this->post('/logout', [
            '_token' => csrf_token(),
        ]);        // var_dump($response);
        self::assertThat($response->status(), self::equalTo(302));
    }


    public function test_it_access_authorization_server()
    {
        $response = $this->post('/login', [
            'email' => $this->user->{'email'},
            'password' => $this->user->{'password'},
            '_token' => csrf_token(),
        ]);

        $response = $this->post('/logout', [
            '_token' => csrf_token(),
        ]);
        // var_dump($response);
        self::assertThat($response->status(), self::equalTo(302));
    }
}

?>
