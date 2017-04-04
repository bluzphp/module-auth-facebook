<?php
/**
 * Created by PhpStorm.
 * User: yuklia
 * Date: 07.05.15
 * Time: 11:40
 */

namespace Application\Tests\Auth;

use Application\Auth\AuthProvider;
use Application\Auth\Row;
use Application\Tests\ControllerTestCase;
use Bluz\Application\Exception\RedirectException;
use Bluz\Proxy\Auth;
use Bluz\Proxy\Db;
use Bluz\Proxy\Messages;

/**
 * @group   module-auth-facebook
 * @package Application\Tests\Auth
 */
class AuthFacebookProviderTest extends ControllerTestCase
{
    protected function setUp()
    {
        parent::setUp();

        Db::insert('users')->setArray(
            [
                'id' => 11,
                'login' => 'Donatello',
                'email' => 'donatello@turtles.org',
                'status' => 'pending'
            ]
        )->execute();

        Db::insert('users')->setArray(
            [
                'id' => 12,
                'login' => 'Bill',
                'email' => 'bill@turtles.org',
                'status' => 'active'
            ]
        )->execute();

        Auth::setIdentity(new \Application\Users\Row());
    }

    protected function tearDown()
    {
        Db::delete('users')->where('id IN (?)', [11, 12])->execute();
        Messages::popAll();
    }

    /**
     * @expectedException \Bluz\Auth\AuthException
     */
    public function testUserStatusNotActive()
    {
        $provider = new AuthProvider('Facebook');
        $authRow = new Row();
        $authRow->userId = 11;
        $provider->alreadyRegisteredLogic($authRow);
    }

    public function testUserStatusActive()
    {
        $provider = new AuthProvider('Facebook');
        $authRow = new Row();
        $authRow->userId = 12;
        try {
            $provider->alreadyRegisteredLogic($authRow);
        } catch (RedirectException $e) {
        }
        self::assertNotNull(Auth::getIdentity());

    }

    public function testOptions()
    {
        $provider = new AuthProvider('Facebook');
        self::assertNotEmpty($provider->getOptions());
    }

    public function testAvailableProviders()
    {
        $provider = new AuthProvider('Facebook');
        self::assertContains("Facebook", $provider->getAvailableProviders());
    }
}
