<?php
/**
 * This <laravel-passport> project created by :
 * Name         : syafiq
 * Date / Time  : 28 July 2019, 8:38 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace Tests\Feature\Api\Grant;


use App\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthorizeCodeGrantTests extends TestCase
{
    /**
     * @var string
     */
    private $token;
    private $user;
    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->startSession();
        $this->setToken();
        $this->setUser();
        $this->setUpClient();
    }

    protected function tearDown(): void
    {
        $this->flushSession();
        $this->tearDownAccessToken();
        $this->tearDownSession();
        parent::tearDown();
    }

    private function setToken()
    {
        $this->token = csrf_token();
    }

    private function setUser()
    {
        $this->user = User::first();
    }

    private function setUpClient()
    {
        $this->client = DB::table('oauth_clients')->where('name', 'AuthorizeCode Grant Client')->first();
    }

    private function tearDownAccessToken()
    {
        DB::table('oauth_access_tokens')->delete();
    }

    private function tearDownSession()
    {
        DB::table('sessions')->delete();
    }

    public function test_it_generate_csrf_token()
    {
        $this->startSession();
        $token = csrf_token();

        self::assertThat($token, self::logicalNot(self::isNull()));
        var_dump($token);
        $this->flushSession();
    }

    public function test_it_redirected_to_login()
    {
        $response = $this->get('/oauth/authorize');
        self::assertThat($response, self::logicalNot(self::isNull()));
        self::assertThat($response->status(), self::equalTo(302));
        $response->assertRedirect('/login');
        var_dump($response);
    }

    public function test_it_not_redirected_to_login()
    {
        $response = $this->actingAs($this->user)->get('/oauth/authorize');
        self::assertThat($response, self::logicalNot(self::isNull()));
        self::assertThat($response->status(), self::logicalNot(self::equalTo(302)));
        var_dump($response);
    }

    public function test_it_access_authorize_route_with_no_arguments_provided__bad_request()
    {
        $response = $this->actingAs($this->user)->get('/oauth/authorize');
        self::assertThat($response->status(), self::equalTo(400));
    }
}

?>
