<?php
/**
 * This <laravel-passport> project created by :
 * Name         : syafiq
 * Date / Time  : 28 July 2019, 8:08 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace Tests\Feature\Api\Grant;


use App\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PasswordGrantTests extends TestCase
{
    /**
     * @var \Illuminate\Database\Eloquent\Model|object|null
     */
    private $client;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpClient();
        $this->setUpUser();
    }

    protected function tearDown(): void
    {
        DB::table('oauth_access_tokens')->delete();
        parent::tearDown();
    }

    private function setUpClient()
    {
        $this->client = DB::table('oauth_clients')->where('name', 'Password Grant Client')->first();
    }

    private function setUpUser()
    {
        $this->user = User::first();
    }

    public function test_it_access_token_route_with_no_arguments_provided__bad_request()
    {
        $response = $this->post('/oauth/token');
        self::assertThat($response->status(), self::equalTo(400));
    }

    public function test_it_access_token_route_with_wrong_arguments__unauthorized()
    {
        $response = $this->post('/oauth/token', [
            'grant_type' => 'password',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'username' => 'taylor@laravel.com',
            'password' => 'my-password',
            'scope' => '',
        ]);
        self::assertThat($response->status(), self::equalTo(401));
    }
}

?>
