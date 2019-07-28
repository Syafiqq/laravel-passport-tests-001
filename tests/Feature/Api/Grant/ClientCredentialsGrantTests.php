<?php
/**
 * This <laravel-passport> project created by :
 * Name         : syafiq
 * Date / Time  : 28 July 2019, 4:46 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace Tests\Feature\Api;


use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClientCredentialsGrantTests extends TestCase
{
    /**
     * @var \Illuminate\Database\Eloquent\Model|object|null
     */
    private $client;

    protected function setUp() : void
    {
        parent::setUp();
        $this->client = DB::table('oauth_clients')->where('name', 'ClientCredentials Grant Client')->first();
    }

    protected function tearDown() : void
    {
        DB::table('oauth_access_tokens')->delete();
        parent::tearDown();
    }


    public function test_it_access_token_route_with_no_arguments_provided__bad_request()
    {
        $response = $this->post('/oauth/token');
        self::assertThat($response->status(), self::equalTo(400));
    }

    public function test_it_access_token_route_with_wrong_arguments__unauthorized()
    {
        $response = $this->post('/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'scope' => 'your-scope',
        ]);
        self::assertThat($response->status(), self::equalTo(401));
    }

    public function test_it_access_token_route_with_right_arguments__ok()
    {
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        $body = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->client->{'id'},
            'client_secret' => $this->client->{'secret'},
            'scope' => '*',
        ];

        $response = $this->post('/oauth/token', $body);
        var_dump($body);
        var_dump($response->json());
        self::assertThat($response->status(), self::equalTo(200));
    }
}

?>
