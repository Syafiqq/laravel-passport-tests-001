<?php


namespace Tests\Feature\Api\Grant;


use App\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PersonalAccessGrantTest extends TestCase
{
    /**
     * @var \Illuminate\Database\Eloquent\Model|object|null
     */
    private $client;
    /**
     * @var User
     */
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
            ->where('name', 'PersonalAccess Grant Client')
            ->first();
    }

    private function setUpUser()
    {
        $this->user = User::where('email', 'user1@mail.com')
            ->first();
        $this->user->{'password'} = 'password';
    }

    public function test_it_generate_access_token_with_non_user_id_token_explicit__ok()
    {
        $access_token = $this->user->createToken('token')->accessToken;
        self::assertThat($access_token, self::logicalNot(self::isNull()));
        $personal = DB::table('oauth_access_tokens')->get();
        self::assertThat($personal->count(), self::equalTo(1));
    }
}
