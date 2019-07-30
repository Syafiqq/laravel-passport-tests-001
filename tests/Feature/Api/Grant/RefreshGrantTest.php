<?php
/**
 * This <laravel-passport> project created by :
 * Name         : syafiq
 * Date / Time  : 30 July 2019, 2:47 AM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace Tests\Feature\Api\Grant;


use App\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RefreshGrantTest extends TestCase
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
        DB::table('oauth_refresh_tokens')->delete();
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

    public function access_token_from_client(): array
    {
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        $body = [
            'grant_type' => 'password',
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'username' => $this->user->{'email'},
            'password' => $this->user->{'password'},
            'scope' => 'scope-1 scope-2',
        ];

        $response = $this->post('/oauth/token', $body);
        var_dump($body);
        var_dump($response->json());
        $result = $response->json();
        self::assertThat($result, self::arrayHasKey('token_type'));
        self::assertThat($result, self::arrayHasKey('expires_in'));
        self::assertThat($result, self::arrayHasKey('access_token'));
        self::assertThat($result, self::arrayHasKey('refresh_token'));
        self::assertThat($response->status(), self::equalTo(200));
        $access_token = DB::table('oauth_access_tokens')
            ->first();
        var_dump($access_token);
        self::assertThat($access_token, self::logicalNot(self::isNull()));
        return $result;
    }

    public function test_it_access_refresh_route_with_right_argument__ok()
    {
        $token = $this->access_token_from_client();
        self::assertThat($token, self::logicalNot(self::isNull()));
        $body = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token['refresh_token'],
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'scope' => 'scope-1 scope-2',
        ];
        $response = $this->post('/oauth/token', $body);
        var_dump($body);
        var_dump($response->json());
        $result = $response->json();
        self::assertThat($result, self::arrayHasKey('token_type'));
        self::assertThat($result, self::arrayHasKey('expires_in'));
        self::assertThat($result, self::arrayHasKey('access_token'));
        self::assertThat($result, self::arrayHasKey('refresh_token'));
        self::assertThat($response->status(), self::equalTo(200));
        $access_token = DB::table('oauth_access_tokens')
            ->count();
        var_dump($access_token);
        self::assertThat($access_token, self::equalTo(2));
    }

    /*
    * //Must reduced
    */
    public function test_it_access_refresh_route_with_right_argument_but_different_scope__ok()
    {
        $token = $this->access_token_from_client();
        self::assertThat($token, self::logicalNot(self::isNull()));
        $body = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token['refresh_token'],
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'scope' => 'scope-1',
        ];
        $response = $this->post('/oauth/token', $body);
        var_dump($body);
        var_dump($response->json());
        $result = $response->json();
        self::assertThat($result, self::arrayHasKey('token_type'));
        self::assertThat($result, self::arrayHasKey('expires_in'));
        self::assertThat($result, self::arrayHasKey('access_token'));
        self::assertThat($result, self::arrayHasKey('refresh_token'));
        self::assertThat($response->status(), self::equalTo(200));
        $access_token = DB::table('oauth_access_tokens')
            ->get();
        var_dump($access_token);
        self::assertThat($access_token->count(), self::equalTo(2));
        self::assertThat($access_token->slice(0)->take(1)->first()->{'scopes'}, self::logicalNot(self::equalTo($access_token->slice(1)->take(1)->first()->{'scopes'})));
    }

    public function test_it_access_refresh_route_with_client_twice_and_right_argument__ok()
    {
        $token = $this->access_token_from_client();
        $token = $this->access_token_from_client();
        $access_token = DB::table('oauth_access_tokens')
            ->get();
        var_dump($access_token);
        self::assertThat($access_token->count(), self::equalTo(2));
        self::assertThat($access_token->sum('revoked'), self::equalTo(0));
        self::assertThat($token, self::logicalNot(self::isNull()));
        $body = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token['refresh_token'],
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'scope' => 'scope-1 scope-2',
        ];
        $response = $this->post('/oauth/token', $body);
        var_dump($body);
        var_dump($response->json());
        $result = $response->json();
        self::assertThat($result, self::arrayHasKey('token_type'));
        self::assertThat($result, self::arrayHasKey('expires_in'));
        self::assertThat($result, self::arrayHasKey('access_token'));
        self::assertThat($result, self::arrayHasKey('refresh_token'));
        self::assertThat($response->status(), self::equalTo(200));
        $access_token = DB::table('oauth_access_tokens')
            ->get();
        var_dump($access_token);
        self::assertThat($access_token->count(), self::equalTo(3));
        self::assertThat($access_token->sum('revoked'), self::equalTo(1));
    }

    public function test_it_access_refresh_route_with_wrong_argument__unauthorized()
    {
        $token = $this->access_token_from_client();
        self::assertThat($token, self::logicalNot(self::isNull()));
        $body = [
            'grant_type' => 'refresh_token',
            'refresh_token' => 'the-refresh-token',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'scope' => '',
        ];
        $response = $this->post('/oauth/token', $body);
        self::assertThat($response->status(), self::equalTo(401));
    }

    public function test_it_access_refresh_route_with_right_argument_but_empty_scope__ok()
    {
        $token = $this->access_token_from_client();
        self::assertThat($token, self::logicalNot(self::isNull()));
        $body = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token['refresh_token'],
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'scope' => '',
        ];
        $response = $this->post('/oauth/token', $body);
        var_dump($body);
        var_dump($response->json());
        $result = $response->json();
        self::assertThat($result, self::arrayHasKey('token_type'));
        self::assertThat($result, self::arrayHasKey('expires_in'));
        self::assertThat($result, self::arrayHasKey('access_token'));
        self::assertThat($result, self::arrayHasKey('refresh_token'));
        self::assertThat($response->status(), self::equalTo(200));
        $access_token = DB::table('oauth_access_tokens')
            ->get();
        var_dump($access_token);
        self::assertThat($access_token->count(), self::equalTo(2));
        self::assertThat($access_token->slice(0)->take(1)->first()->{'scopes'}, self::equalTo($access_token->slice(1)->take(1)->first()->{'scopes'}));
    }

    public function test_it_access_refresh_route_with_right_argument_but_no_scope__ok()
    {
        $token = $this->access_token_from_client();
        self::assertThat($token, self::logicalNot(self::isNull()));
        $body = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token['refresh_token'],
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
        ];
        $response = $this->post('/oauth/token', $body);
        var_dump($body);
        var_dump($response->json());
        $result = $response->json();
        self::assertThat($result, self::arrayHasKey('token_type'));
        self::assertThat($result, self::arrayHasKey('expires_in'));
        self::assertThat($result, self::arrayHasKey('access_token'));
        self::assertThat($result, self::arrayHasKey('refresh_token'));
        self::assertThat($response->status(), self::equalTo(200));
        $access_token = DB::table('oauth_access_tokens')
            ->get();
        var_dump($access_token);
        self::assertThat($access_token->count(), self::equalTo(2));
        self::assertThat($access_token->slice(0)->take(1)->first()->{'scopes'}, self::equalTo($access_token->slice(1)->take(1)->first()->{'scopes'}));
    }

    public function test_it_access_refresh_route_with_no_refresh_token__bad_request()
    {
        $token = $this->access_token_from_client();
        self::assertThat($token, self::logicalNot(self::isNull()));
        $body = [
            'grant_type' => 'refresh_token',
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'scope' => 'scope-1 scope-2',
        ];
        $response = $this->post('/oauth/token', $body);
        self::assertThat($response->status(), self::equalTo(400));
    }

    public function test_it_access_refresh_route_with_wrong_refresh_token__unauthorized()
    {
        $token = $this->access_token_from_client();
        self::assertThat($token, self::logicalNot(self::isNull()));
        $body = [
            'grant_type' => 'refresh_token',
            'refresh_token' => 'abc',
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'scope' => 'scope-1 scope-2',
        ];
        $response = $this->post('/oauth/token', $body);
        self::assertThat($response->status(), self::equalTo(401));
    }

    public function test_it_access_refresh_route_with_revoked_refresh_token__unauthorized()
    {
        $token = $this->access_token_from_client();
        DB::table('oauth_refresh_tokens')
            ->update([
                'revoked' => 1
            ]);
        $refresh = DB::table('oauth_refresh_tokens')
            ->first();
        self::assertThat($refresh, self::logicalNot(self::isNull()));
        self::assertThat($refresh->{'revoked'}, self::equalTo(1));
        $body = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token['refresh_token'],
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'scope' => 'scope-1 scope-2',
        ];

        $response = $this->post('/oauth/token', $body);
        var_dump($body);
        var_dump($response->json());
        self::assertThat($response->status(), self::equalTo(401));
        $access_token = DB::table('oauth_access_tokens')
            ->first();
        var_dump($access_token);
        self::assertThat($access_token, self::logicalNot(self::isNull()));
        DB::table('oauth_refresh_tokens')
            ->update([
                'revoked' => 0
            ]);
    }
}

?>
