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
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class AuthorizeCodeGrantTests extends TestCase
{
    /**
     * @var string
     */
    private $token;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->startSession();
        $this->setToken();
        $this->setUser();
    }

    protected function tearDown(): void
    {
        $this->flushSession();
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

    public function test_it_generate_csrf_token()
    {
        $this->startSession();
        $token = csrf_token();

        self::assertThat($token, self::logicalNot(self::isNull()));
        var_dump($token);
        $this->flushSession();
    }
}

?>
