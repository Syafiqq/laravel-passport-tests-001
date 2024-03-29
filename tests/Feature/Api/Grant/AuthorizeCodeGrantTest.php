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

class AuthorizeCodeGrantTest extends TestCase
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
        $this->tearDownAuthCodes();
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
        $this->client = DB::table('oauth_clients')
            ->where('name', 'AuthorizeCode Grant Client')
            ->first();
    }

    private function tearDownAccessToken()
    {
        DB::table('oauth_access_tokens')->delete();
    }

    private function tearDownAuthCodes()
    {
        DB::table('oauth_auth_codes')->delete();
    }

    private function tearDownSession()
    {
        DB::table('sessions')->delete();
    }

    private function getCompleteAuthorization(): array
    {
        $query = http_build_query([
            'response_type' => 'code',
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
        parse_str(parse_url($location, PHP_URL_QUERY), $array);
        $access_token = DB::table('oauth_auth_codes')
            ->first();
        var_dump($access_token);
        self::assertThat($access_token,self::logicalNot(self::isNull()));
        return $array;
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

    public function test_it_access_authorize_route_with_wrong_arguments__unauthorized()
    {
        $query = http_build_query([
            'response_type' => 'code',
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
            'response_type' => 'code',
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
            'response_type' => 'code',
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
            'response_type' => 'code',
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
            'response_type' => 'code',
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
            'response_type' => 'code',
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
            'response_type' => 'code',
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
        parse_str(parse_url($location, PHP_URL_QUERY), $array);
        var_dump($array);
        self::assertThat($response->status(), self::equalTo(302));
        self::assertThat($array, self::arrayHasKey('code'));
        self::assertThat($array, self::arrayHasKey('state'));
        $access_token = DB::table('oauth_auth_codes')
            ->first();
        var_dump($access_token);
        self::assertThat($access_token, self::logicalNot(self::isNull()));
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
        self::assertThat($_client->{'user_id'}, self::equalTo(2));
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        $query = http_build_query([
            'response_type' => 'code',
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
        parse_str(parse_url($location, PHP_URL_QUERY), $array);
        var_dump($array);
        self::assertThat($response->status(), self::equalTo(302));
        self::assertThat($array, self::arrayHasKey('code'));
        self::assertThat($array, self::arrayHasKey('state'));
        $access_token = DB::table('oauth_auth_codes')
            ->first();
        var_dump($access_token);
        self::assertThat($access_token, self::logicalNot(self::isNull()));
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

    public function test_it_retrieve_code_with_no_authorization_request__ok__redirect()
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

    public function test_it_access_token_route_with_no_arguments_provided__bad_request()
    {
        $response = $this->post('/oauth/token');
        self::assertThat($response->status(), self::equalTo(400));
    }

    public function test_it_access_token_route_with_wrong_arguments__unauthorized()
    {
        $response = $this->post('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'redirect_uri' => 'http://example.com/callback',
            'code' => 'code',
        ]);
        self::assertThat($response->status(), self::equalTo(401));
    }

    public function test_it_access_token_route_with_right_arguments__ok()
    {
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        $request = $this->getCompleteAuthorization();
        $body = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'redirect_uri' => $this->client->{'redirect'},
            'code' => $request['code'],
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

    public function test_it_access_token_route_with_wrong_code__bad_request()
    {
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        $this->getCompleteAuthorization();
        $body = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'redirect_uri' => $this->client->{'redirect'},
            'code' => 'this_is_wrong_code',
        ];

        $response = $this->post('/oauth/token', $body);
        var_dump($body);
        var_dump($response->json());
        self::assertThat($response->status(), self::equalTo(400));
        self::assertThat($response->json('hint'), self::equalTo('Cannot decrypt the authorization code'));
    }

    public function test_it_access_token_route_with_no_code__bad_request()
    {
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        $this->getCompleteAuthorization();
        $body = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'redirect_uri' => $this->client->{'redirect'},
        ];

        $response = $this->post('/oauth/token', $body);
        var_dump($body);
        var_dump($response->json());
        self::assertThat($response->status(), self::equalTo(400));
        self::assertThat($response->json('hint'), self::equalTo('Check the `code` parameter'));
    }

    public function test_it_access_token_route_with_empty_code__bad_request()
    {
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        $this->getCompleteAuthorization();
        $body = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'redirect_uri' => $this->client->{'redirect'},
            'code' => '',
        ];

        $response = $this->post('/oauth/token', $body);
        var_dump($body);
        var_dump($response->json());
        self::assertThat($response->status(), self::equalTo(400));
        self::assertThat($response->json('hint'), self::equalTo('Check the `code` parameter'));
    }

    public function test_it_access_token_route_with_revoked_auth_codes__bad_request()
    {
        $request = $this->getCompleteAuthorization();
        DB::table('oauth_auth_codes')
            ->where('user_id', $this->client->{'user_id'})
            ->where('client_id', $this->client->{'id'})
            ->update([
                'revoked' => 1
            ]);
        $code = DB::table('oauth_auth_codes')->first();
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        self::assertThat($code->{'revoked'}, self::equalTo(1));
        $body = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'redirect_uri' => $this->client->{'redirect'},
            'code' => $request['code'],
        ];

        $response = $this->post('/oauth/token', $body);
        var_dump($body);
        var_dump($response->json());
        self::assertThat($response->json('hint'), self::equalTo('Authorization code has been revoked'));
        self::assertThat($response->status(), self::equalTo(400));
        $access_token = DB::table('oauth_access_tokens')
            ->first();
        var_dump($access_token);
        self::assertThat($access_token, self::isNull());
        DB::table('oauth_auth_codes')
            ->where('user_id', $this->client->{'user_id'})
            ->where('client_id', $this->client->{'id'})
            ->update([
                'revoked' => 0
            ]);
    }

    public function test_it_access_token_route_with_revoked_client__unauthorized()
    {
        $request = $this->getCompleteAuthorization();
        DB::table('oauth_clients')
            ->where('id', $this->client->{'id'})
            ->update([
                'revoked' => 1
            ]);
        $this->setUpClient();
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        self::assertThat($this->client->{'revoked'}, self::equalTo(1));
        $body = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'redirect_uri' => $this->client->{'redirect'},
            'code' => $request['code'],
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
