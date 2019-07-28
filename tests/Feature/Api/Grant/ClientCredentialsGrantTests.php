<?php
/**
 * This <laravel-passport> project created by :
 * Name         : syafiq
 * Date / Time  : 28 July 2019, 4:46 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace Tests\Feature\Api;


use Tests\TestCase;

class ClientCredentialsGrantTests extends TestCase
{
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
}

?>