<?php
/**
 * This <laravel-passport> project created by :
 * Name         : syafiq
 * Date / Time  : 28 July 2019, 8:38 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

namespace Tests\Feature\Api\Grant;


use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class AuthorizeCodeGrantTests extends TestCase
{
    public function test_it_generate_csrf_token()
    {
        Session::start();
        $token = csrf_token();

        self::assertThat($token, self::logicalNot(self::isNull()));
        var_dump($token);
        Session::flush();
    }
}

?>
