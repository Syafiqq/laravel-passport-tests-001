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

    public function test_it_generate_access_token_with_user_id_token_explicit_explicit__ok()
    {
        DB::table('oauth_clients')
            ->where('id', $this->client->{'id'})
            ->update([
                'user_id' => $this->user->{'id'}
            ]);
        $this->setUpClient();
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        self::assertThat($this->client->{'user_id'}, self::equalTo(1));
        $access_token = $this->user->createToken('token')->accessToken;
        self::assertThat($access_token, self::logicalNot(self::isNull()));
        $personal = DB::table('oauth_access_tokens')->get();
        self::assertThat($personal->count(), self::equalTo(1));
        DB::table('oauth_clients')
            ->where('id', $this->client->{'id'})
            ->update([
                'user_id' => null
            ]);
        $this->setUpClient();
        self::assertThat($this->client, self::logicalNot(self::isNull()));
        self::assertThat($this->client->{'user_id'}, self::isNull());
    }

    public function test_it_generate_access_token_with_no_both_personal_token_explicit__ok()
    {
        $personal_access = DB::table('oauth_personal_access_clients')
            ->where('client_id', $this->client->{'id'})
            ->first();
        DB::table('oauth_clients')
            ->where('id', $this->client->{'id'})
            ->delete();
        DB::table('oauth_personal_access_clients')
            ->where('client_id', $this->client->{'id'})
            ->delete();
        $_personal_access = DB::table('oauth_personal_access_clients')
            ->where('client_id', $this->client->{'id'})
            ->first();
        $_client = DB::table('oauth_clients')
            ->where('id', $this->client->{'id'})
            ->first();
        self::assertThat($_personal_access, self::isNull());
        self::assertThat($_client, self::isNull());
        $gate = false;
        try{
            $this->user->createToken('token')->accessToken;
        }
        catch (\RuntimeException $e){
            $gate = true;
        }
        self::assertThat($gate, self::isTrue());
        DB::table('oauth_clients')->insert(json_decode(json_encode($this->client), true));
        DB::table('oauth_personal_access_clients')->insert(json_decode(json_encode($personal_access), true));
        $_personal_access = DB::table('oauth_personal_access_clients')
            ->where('client_id', $this->client->{'id'})
            ->first();
        $_client = DB::table('oauth_clients')
            ->where('id', $this->client->{'id'})
            ->first();
        self::assertThat($_personal_access, self::logicalNot(self::isNull()));
        self::assertThat($_client, self::logicalNot(self::isNull()));
    }

    public function test_it_generate_access_token_with_no_first_personal_token_explicit__ok()
    {
        $personal_access = DB::table('oauth_personal_access_clients')
            ->where('client_id', $this->client->{'id'})
            ->first();
        DB::table('oauth_personal_access_clients')
            ->where('client_id', $this->client->{'id'})
            ->delete();
        $_personal_access = DB::table('oauth_personal_access_clients')
            ->where('client_id', $this->client->{'id'})
            ->first();
        $_client = DB::table('oauth_clients')
            ->where('id', $this->client->{'id'})
            ->first();
        self::assertThat($_personal_access, self::isNull());
        self::assertThat($_client, self::logicalNot(self::isNull()));
        $gate = false;
        try{
            $this->user->createToken('token')->accessToken;
        }
        catch (\RuntimeException $e){
            $gate = true;
        }
        self::assertThat($gate, self::isTrue());
        DB::table('oauth_personal_access_clients')->insert(json_decode(json_encode($personal_access), true));
        $_personal_access = DB::table('oauth_personal_access_clients')
            ->where('client_id', $this->client->{'id'})
            ->first();
        $_client = DB::table('oauth_clients')
            ->where('id', $this->client->{'id'})
            ->first();
        self::assertThat($_personal_access, self::logicalNot(self::isNull()));
        self::assertThat($_client, self::logicalNot(self::isNull()));
    }

    public function test_it_generate_access_token_with_no_last_personal_token_explicit__ok()
    {
        $personal_access = DB::table('oauth_personal_access_clients')
            ->where('client_id', $this->client->{'id'})
            ->first();
        DB::table('oauth_clients')
            ->where('id', $this->client->{'id'})
            ->delete();
        $_personal_access = DB::table('oauth_personal_access_clients')
            ->where('client_id', $this->client->{'id'})
            ->first();
        $_client = DB::table('oauth_clients')
            ->where('id', $this->client->{'id'})
            ->first();
        self::assertThat($_personal_access, self::logicalNot(self::isNull()));
        self::assertThat($_client, self::isNull());
        $gate = false;
        try{
            $this->user->createToken('token')->accessToken;
        }
        catch (\ErrorException $e){
            $gate = true;
        }
        self::assertThat($gate, self::isTrue());
        DB::table('oauth_clients')->insert(json_decode(json_encode($this->client), true));
        $_personal_access = DB::table('oauth_personal_access_clients')
            ->where('client_id', $this->client->{'id'})
            ->first();
        $_client = DB::table('oauth_clients')
            ->where('id', $this->client->{'id'})
            ->first();
        self::assertThat($_personal_access, self::logicalNot(self::isNull()));
        self::assertThat($_client, self::logicalNot(self::isNull()));
    }
}
