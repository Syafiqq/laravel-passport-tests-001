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

class PasswordGrantTest extends TestCase
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
        $this->client = DB::table('oauth_clients')
            ->where('name', 'Password Grant Client')
            ->first();
    }

    private function setUpUser()
    {
        $this->user = User::where('email', 'user1@mail.com')
            ->first();
        $this->user->{'password'} = 'password';
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

    public function test_it_access_token_route_with_right_arguments__ok()
    {
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        $body = [
            'grant_type' => 'password',
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'username' => $this->user->{'email'},
            'password' => $this->user->{'password'},
            'scope' => '*',
        ];

        $response = $this->post('/oauth/token', $body);
        var_dump($body);
        var_dump($response->json());
        self::assertThat($response->status(), self::equalTo(200));
        $access_token = DB::table('oauth_access_tokens')
            ->first();
        var_dump($access_token);
        self::assertThat($access_token, self::logicalNot(self::isNull()));
    }

    public function test_it_access_token_route_with_right_arguments_twice__ok()
    {
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        $body = [
            'grant_type' => 'password',
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'username' => $this->user->{'email'},
            'password' => $this->user->{'password'},
            'scope' => '*',
        ];

        $response = $this->post('/oauth/token', $body);
        $response = $this->post('/oauth/token', $body);
        var_dump($body);
        var_dump($response->json());
        self::assertThat($response->status(), self::equalTo(200));
        $access_token = DB::table('oauth_access_tokens')
            ->get();
        var_dump($access_token);
        self::assertThat($access_token->count(), self::equalTo(2));
        self::assertThat($access_token->sum('revoked'), self::equalTo(0));
    }

    public function test_it_access_token_route_with_wrong_scope__bad_request()
    {
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        $body = [
            'grant_type' => 'password',
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'username' => $this->user->{'email'},
            'password' => $this->user->{'password'},
            'scope' => 'this_is_wrong_scope',
        ];

        $response = $this->post('/oauth/token', $body);
        var_dump($body);
        var_dump($response->json());
        self::assertThat($response->status(), self::equalTo(400));
        $access_token = DB::table('oauth_access_tokens')
            ->first();
        var_dump($access_token);
        self::assertThat($access_token, self::isNull());
    }

    public function test_it_access_token_route_with_no_scope__ok()
    {
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        $body = [
            'grant_type' => 'password',
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'username' => $this->user->{'email'},
            'password' => $this->user->{'password'},
        ];

        $response = $this->post('/oauth/token', $body);
        var_dump($body);
        var_dump($response->json());
        self::assertThat($response->status(), self::equalTo(200));
        $access_token = DB::table('oauth_access_tokens')
            ->first();
        var_dump($access_token);
        self::assertThat($access_token, self::logicalNot(self::isNull()));
    }

    public function test_it_access_token_route_with_empty_scope__ok()
    {
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        $body = [
            'grant_type' => 'password',
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'username' => $this->user->{'email'},
            'password' => $this->user->{'password'},
            'scope' => '',
        ];

        $response = $this->post('/oauth/token', $body);
        var_dump($body);
        var_dump($response->json());
        self::assertThat($response->status(), self::equalTo(200));
        $access_token = DB::table('oauth_access_tokens')
            ->first();
        var_dump($access_token);
        self::assertThat($access_token, self::logicalNot(self::isNull()));
    }

    public function test_it_access_token_route_with_revoked_client__unauthorized()
    {
        DB::table('oauth_clients')
            ->where('id', $this->client->{'id'})
            ->update([
                'revoked' => 1
            ]);
        $this->setUpClient();
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        self::assertThat($this->client->{'revoked'}, self::equalTo(1));
        $body = [
            'grant_type' => 'password',
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'username' => $this->user->{'email'},
            'password' => $this->user->{'password'},
            'scope' => '*',
        ];

        $response = $this->post('/oauth/token', $body);
        var_dump($body);
        var_dump($response->json());
        self::assertThat($response->status(), self::equalTo(401));
        $access_token = DB::table('oauth_access_tokens')
            ->first();
        var_dump($access_token);
        self::assertThat($access_token, self::isNull());
        DB::table('oauth_clients')
            ->where('id', $this->client->{'id'})
            ->update([
                'revoked' => 0
            ]);
    }
}

?>
