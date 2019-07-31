<?php
/**
 * This <laravel-passport> project created by :
 * Name         : syafiq
 * Date / Time  : 28 July 2019, 8:40 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace Tests\Feature\Api\Grant;


use App\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ImplicitGrantTest extends TestCase
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
        $this->user = User::where('email', 'user1@mail.com')
            ->first();
        $this->user->{'password'} = 'password';
    }

    private function setUpClient()
    {
        $this->client = DB::table('oauth_clients')->where('name', 'Implicit Grant Client')
            ->first();
    }

    private function tearDownAccessToken()
    {
        DB::table('oauth_access_tokens')->delete();
    }

    private function tearDownSession()
    {
        DB::table('sessions')->delete();
    }

    public function test_it_access_authorize_route_with_no_arguments_provided__bad_request()
    {
        $response = $this->actingAs($this->user)->get('/oauth/authorize');
        self::assertThat($response->status(), self::equalTo(400));
    }

    public function test_it_access_authorize_route_with_wrong_arguments__unauthorized()
    {
        $query = http_build_query([
            'response_type' => 'token',
            'client_id' => 'client-id',
            'redirect_uri' => 'redirect-uri',
            'scope' => 'scope',
            'state' => 'token',
        ]);
        $response = $this->actingAs($this->user)->get('/oauth/authorize?' . $query);
        self::assertThat($response->status(), self::equalTo(401));
    }

    public function test_it_access_authorize_route_with_right_arguments__ok()
    {
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        $query = http_build_query([
            'response_type' => 'token',
            'client_id' => $this->client->{'id'},
            'redirect_uri' => $this->client->{'redirect'},
            'scope' => '*',
            'state' => $this->token,
        ]);
        $response = $this->actingAs($this->user)->get('/oauth/authorize?' . $query);
        var_dump($query);
        var_dump($response);
        self::assertThat($response->status(), self::equalTo(200));
    }

    public function test_it_access_authorize_route_with_wrong_scope__found_but_error()
    {
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        $query = http_build_query([
            'response_type' => 'token',
            'client_id' => $this->client->{'id'},
            'redirect_uri' => $this->client->{'redirect'},
            'scope' => 'this_is_wrong_scope',
            'state' => $this->token,
        ]);
        $response = $this->actingAs($this->user)->get('/oauth/authorize?' . $query);
        var_dump($query);
        var_dump($response);
        self::assertThat($response->status(), self::equalTo(302));
    }

    public function test_it_access_authorize_route_with_no_scope__ok()
    {
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        $query = http_build_query([
            'response_type' => 'token',
            'client_id' => $this->client->{'id'},
            'redirect_uri' => $this->client->{'redirect'},
            'state' => $this->token,
        ]);
        $response = $this->actingAs($this->user)->get('/oauth/authorize?' . $query);
        var_dump($query);
        var_dump($response);
        self::assertThat($response->status(), self::equalTo(200));
    }

    public function test_it_access_authorize_route_with_empty_scope__ok()
    {
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        $query = http_build_query([
            'response_type' => 'token',
            'client_id' => $this->client->{'id'},
            'redirect_uri' => $this->client->{'redirect'},
            'scope' => '',
            'state' => $this->token,
        ]);
        $response = $this->actingAs($this->user)->get('/oauth/authorize?' . $query);
        var_dump($query);
        var_dump($response);
        self::assertThat($response->status(), self::equalTo(200));
    }

    public function test_it_access_authorize_route_with_revoked_client__unauthorized()
    {
        DB::table('oauth_clients')
            ->where('id', $this->client->{'id'})
            ->update([
                'revoked' => 1
            ]);
        $this->setUpClient();
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        self::assertThat($this->client->{'revoked'}, self::equalTo(1));
        $query = http_build_query([
            'response_type' => 'token',
            'client_id' => $this->client->{'id'},
            'redirect_uri' => $this->client->{'redirect'},
            'scope' => '*',
            'state' => $this->token,
        ]);

        $response = $this->actingAs($this->user)->get('/oauth/authorize?' . $query);
        var_dump($query);
        var_dump($response);
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

    public function test_it_retrieve_code_with_right_argument__ok__redirect()
    {
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        $query = http_build_query([
            'response_type' => 'token',
            'client_id' => $this->client->{'id'},
            'redirect_uri' => $this->client->{'redirect'},
            'scope' => '*',
            'state' => $this->token,
        ]);
        $this->actingAs($this->user)->get('/oauth/authorize?' . $query);
        $response = $this->actingAs($this->user)->post('/oauth/authorize', [
            '_token' => csrf_token(),
            'state' => $this->token,
            'client_id' => $this->client->{'id'},
        ]);
        $location = $response->headers->get('Location');
        parse_str(parse_url($location, PHP_URL_FRAGMENT), $array);
        var_dump($array);
        self::assertThat($response->status(), self::equalTo(302));
        self::assertThat($array, self::arrayHasKey('token_type'));
        self::assertThat($array, self::arrayHasKey('expires_in'));
        self::assertThat($array, self::arrayHasKey('access_token'));
        self::assertThat($array, self::arrayHasKey('state'));
    }

    public function test_it_retrieve_code_with_right_argument_but_wrong_user_id__ok__redirect()
    {
        DB::table('oauth_clients')
            ->where('id', $this->client->{'id'})
            ->update([
                'user_id' => 2
            ]);
        $_client = DB::table('oauth_clients')
            ->where('id', $this->client->{'id'})
            ->first();
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        $query = http_build_query([
            'response_type' => 'token',
            'client_id' => $this->client->{'id'},
            'redirect_uri' => $this->client->{'redirect'},
            'scope' => '*',
            'state' => $this->token,
        ]);
        $this->actingAs($this->user)->get('/oauth/authorize?' . $query);
        $response = $this->actingAs($this->user)->post('/oauth/authorize', [
            '_token' => csrf_token(),
            'state' => $this->token,
            'client_id' => $this->client->{'id'},
        ]);
        $location = $response->headers->get('Location');
        parse_str(parse_url($location, PHP_URL_FRAGMENT), $array);
        var_dump($array);
        self::assertThat($response->status(), self::equalTo(302));
        self::assertThat($array, self::arrayHasKey('token_type'));
        self::assertThat($array, self::arrayHasKey('expires_in'));
        self::assertThat($array, self::arrayHasKey('access_token'));
        self::assertThat($array, self::arrayHasKey('state'));
        DB::table('oauth_clients')
            ->where('id', $this->client->{'id'})
            ->update([
                'user_id' => 1
            ]);
        $_client = DB::table('oauth_clients')
            ->where('id', $this->client->{'id'})
            ->first();
        self::assertThat($_client->{'user_id'}, self::equalTo(1));
    }

    public function test_it_retrieve_code_with_no_authorization_request__ok__internal_server_error()
    {
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        $response = $this->actingAs($this->user)->post('/oauth/authorize', [
            '_token' => csrf_token(),
            'state' => $this->token,
            'client_id' => $this->client->{'id'},
        ]);
        var_dump($response);
        self::assertThat($response->status(), self::equalTo(500));
        self::assertThat($response->content(), self::equalTo('Authorization request was not present in the session.'));
    }
}

?>
