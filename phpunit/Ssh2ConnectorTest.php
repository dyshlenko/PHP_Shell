<?php
/**
 * Description of Ssh2ConnectorTest
 *
 * @author din70 <igor.dyshlenko@gmail.com>
 * @requires function ssh2_connect
 */
use din70\Tools\ShellAccess\Ssh2Connector;
use PHPUnit\Framework\TestCase;

require_once '../din70/Tools/ShellAccess/ShellConnectorInterface.php';
require_once '../din70/Tools/ShellAccess/LogWrapper.php';
require_once '../din70/Tools/ShellAccess/Ssh2Connector.php';
require_once './commonData.php';

class Ssh2ConnectorTest extends TestCase
{
    /**
     * @group Shell
     * @covers Ssh2Connector::__construct
     * @expectedException RuntimeException
     */
    public function testException1()
    {
        $connector = new Ssh2Connector(HOSTNAME, 1, null);
    }

    /**
     * @group Shell
     * @covers Ssh2Connector::__construct
     * @covers Ssh2Connector::login
     * @expectedException RuntimeException
     */
    public function testException2()
    {
        $connector = new Ssh2Connector(HOSTNAME, HOSTPORT, null);

        $this->assertTrue($connector->isConnected(), 'isConnected must be true');
        $this->assertFalse($connector->isLoggedIn(), 'isLoggedIn must be false');

        $connector->login(USERNAME, 'IncorrectPassword');
    }

    /**
     * @group Shell
     * @covers Ssh2Connector::__construct
     * @covers Ssh2Connector::isConnected
     * @covers Ssh2Connector::isLoggedIn
     * @covers Ssh2Connector::getErrno
     * @covers Ssh2Connector::getError
     * @covers Ssh2Connector::logout
     * @covers Ssh2Connector::read
     * @covers Ssh2Connector::write
     */
    public function testNewConnector()
    {
        $connector = new Ssh2Connector(HOSTNAME, HOSTPORT, null);

        $this->assertTrue($connector->isConnected(), 'isConnected must be true');
        $this->assertFalse($connector->isLoggedIn(), 'isLoggedIn must be false');
        $this->assertNull($connector->getErrno(), 'Connector Errno must be NULL!');
        $this->assertEmpty($connector->getError());

        $this->assertFalse($connector->logout());
        $this->assertFalse($connector->read());
        $this->assertFalse($connector->write(COMMAND));

        return $connector;
    }

    /**
     * @group Shell
     * @covers Ssh2Connector::login
     * @covers Ssh2Connector::isConnected
     * @covers Ssh2Connector::isLoggedIn
     * @depends testNewConnector
     */
    public function testLogin(Ssh2Connector $connector)
    {
        $this->assertTrue($connector->login(USERNAME, USERPASS),
                'Login error: User ' . USERNAME . ' with password ' .
                USERPASS . ' not logged in.');
        $this->assertTrue($connector->isConnected());
        $this->assertTrue($connector->isLoggedIn());

        return $connector;
    }

    /**
     * @group Shell
     * @covers Ssh2Connector::read
     * @depends testLogin
     */
    public function testRead(Ssh2Connector $connector)
    {
        $buffer   = '';
        $search   = '$ ';   // linux default command prompt
        $found    = false;
        $started  = time(); // start reading time
        $timeout  = 5;      // seconds
        $timedOut = false;

        while (!$found && !$timedOut &&
                (($char = $connector->read()) !== false)) {

            $buffer .= $char;

            if (substr($buffer, 0 - strlen($search)) === $search) {
                $found = true;
                continue;
            }

            $timedOut = ((time() - $started) > $timeout);
        }

        $this->assertGreaterThanOrEqual(strlen($search), strlen($buffer));
        $this->assertTrue($found);
        $this->assertFalse($timedOut);

        return $connector;
    }

    /**
     * @group Shell
     * @covers Ssh2Connector::write
     * @covers Ssh2Connector::read
     * @depends testRead
     */
    public function testWriteAndRead(Ssh2Connector $connector)
    {
        $this->assertEquals(strlen(COMMAND . PHP_EOL),
                $connector->write(COMMAND . PHP_EOL));

        $buffer   = '';
        $search   = '$ ';   // linux default command prompt
        $found    = false;
        $started  = time(); // start reading time
        $timeout  = 5;      // seconds
        $timedOut = false;

        while (!$found && !$timedOut &&
                (($char = $connector->read()) !== false)) {

            $buffer .= $char;

            if (substr($buffer, 0 - strlen($search)) === $search) {
                $found = true;
                continue;
            }

            $timedOut = ((time() - $started) > $timeout);
        }

        $this->assertGreaterThanOrEqual(strlen($search), strlen($buffer));
        $this->assertGreaterThanOrEqual(strlen(COMMAND), strpos($buffer, USERNAME));
        $this->assertTrue($found);
        $this->assertFalse($timedOut);

        return $connector;
    }

    /**
     * @group Shell
     * @covers Ssh2Connector::logout
     * @covers Ssh2Connector::isConnected
     * @covers Ssh2Connector::isLoggedIn
     * @depends testWriteAndRead
     */
    public function testLogout(Ssh2Connector $connector)
    {
        $this->assertTrue($connector->logout());
        $this->assertFalse($connector->logout());
        $this->assertFalse($connector->isLoggedIn());
        $this->assertTrue($connector->isConnected());
    }

}
