<?php
/**
 * This <laravel-passport> project created by :
 * Name         : syafiq
 * Date / Time  : 27 July 2019, 3:24 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace Tests\Feature\Api\User;


use App\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthTest extends TestCase
{
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::where('email', 'user1@mail.com')->first();
        $this->user->{'password'} = 'password';
        self::assertThat($this->user, self::logicalNot(self::isNull()));
    }

    protected function tearDown(): void
    {
        DB::table('sessions')->delete();
        parent::tearDown();
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_it_success_get_token_with_right_credential()
    {
        $response = $this->json('POST', 'api/login', [
            'email' => $this->user->{'email'},
            'password' => $this->user->{'password'},
        ]);

        var_dump($response->json());
        self::assertEquals($response->getStatusCode(), 200);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_it_success_access_detail_provided_token()
    {
        $response = $this->json('POST', 'api/login', [
            'email' => $this->user->{'email'},
            'password' => $this->user->{'password'},
        ]);

        self::assertEquals($response->getStatusCode(), 200);
        $response = $this->json('POST', 'api/details', [], [
            'Authorization' => "Bearer ".$response->json('success.token')
        ]);
        var_dump($response->json());
        self::assertEquals($response->getStatusCode(), 200);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_it_fail_get_token_with_wrong_credential()
    {
        $response = $this->json('POST', 'api/login', [
            'email' => $this->user->{'email'},
            'password' => $this->user->{'password'},
        ]);

        var_dump($response->json());
        self::assertEquals($response->getStatusCode(), 401);
    }
}

?>
