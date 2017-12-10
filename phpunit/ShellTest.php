<?php

/**
 * Description of ShellTest
 *
 * @author din70 <igor.dyshlenko@gmail.com>
 */
use din70\Tools\ShellAccess\Ssh2Connector;
use din70\Tools\ShellAccess\Shell;
use PHPUnit\Framework\TestCase;

require_once '../lib/din70/Tools/ShellAccess/ShellConnectorInterface.php';
require_once '../lib/din70/Tools/ShellAccess/LogWrapper.php';
require_once '../lib/din70/Tools/ShellAccess/Ssh2Connector.php';
require_once '../lib/din70/Tools/ShellAccess/Shell.php';
require_once './commonData.php';

class ShellTest extends TestCase
{

    const
            COMMAND_PROMPT = '$ ',
            LINUX_TERMINAL_EOL = "\r";

    /**
     * @group Shell
     * @covers Shell::__construct
     * @covers Shell::login
     * @expectedException RuntimeException
     */
    public function testException2()
    {
        $connector = new Ssh2Connector(HOSTNAME, HOSTPORT, null);
        $shell     = new Shell($connector);
        $shell->login(USERNAME, 'IncorrectPassword');
    }

    /**
     * @group Shell
     * @covers Shell::__construct
     * @covers Shell::isConnected
     * @covers Shell::isLoggedIn
     * @covers Shell::isOnLine
     * @covers Shell::getErrno
     * @covers Shell::getError
     * @covers Shell::logout
     * @covers Shell::read
     * @covers Shell::write
     */
    public function testNewShell()
    {
        $connector = new Ssh2Connector(HOSTNAME, HOSTPORT, null);
        $shell     = new Shell($connector);

        $this->assertTrue($shell->isConnected(), 'isConnected must be true');
        $this->assertFalse($shell->isLoggedIn(), 'isLoggedIn must be false');
        $this->assertFalse($shell->isOnLine(), 'isLoggedIn must be false');

        $this->assertNull($shell->getErrno(), 'Shell Errno must be NULL!');
        $this->assertEmpty($shell->getError());

        $this->assertFalse($shell->logout());
        $this->assertFalse($shell->read());
        $this->assertFalse($shell->write(COMMAND));

        return $shell;
    }

    /**
     * @group Shell
     * @covers Shell::prompt
     * @depends testNewShell
     */
    public function testPrompt(Shell $shell)
    {
        $this->assertEquals($shell->prompt('--->'), '--->');
        $this->assertEquals($shell->prompt(), '--->');
        $this->assertEquals($shell->prompt(self::COMMAND_PROMPT),
                                           self::COMMAND_PROMPT);

        return $shell;
    }

    /**
     * @group Shell
     * @covers Shell::eol
     * @depends testPrompt
     */
    public function testEol(Shell $shell)
    {
        $this->assertEquals($shell->eol("\n\r"), "\n\r");
        $this->assertEquals($shell->eol(), "\n\r");
        $this->assertEquals($shell->eol(self::LINUX_TERMINAL_EOL),
                                        self::LINUX_TERMINAL_EOL);

        return $shell;
    }

    /**
     * @group Shell
     * @covers Shell::login
     * @covers Shell::isConnected
     * @covers Shell::isLoggedIn
     * @covers Shell::isOnLine
     * @depends testEol
     */
    public function testLogin(Shell $shell)
    {
        $this->assertTrue($shell->login(USERNAME, USERPASS),
                'Login error: User "' . USERNAME .
                '" with password "' . USERPASS .
                '" not logged in.');
        $this->assertTrue($shell->isConnected());
        $this->assertTrue($shell->isLoggedIn());
        $this->assertTrue($shell->isOnLine());

        return $shell;
    }

    /**
     * @group Shell
     * @covers Shell::goAhead
     * @covers Shell::getResult
     * @covers Shell::write
     * @covers Shell::read
     * @depends testLogin
     */
    public function testReadWrite(Shell $shell)
    {
        $shell->goAhead();
        $str = $shell->getResult();

        $this->assertGreaterThanOrEqual(strlen($shell->prompt()), strlen($str));
        $this->assertTrue(is_int(strpos($str, $shell->prompt())));

        $this->assertEquals($shell->write($shell->eol()), strlen($shell->eol()));
        $this->assertEquals($shell->read(null, strlen(COMMAND)), strlen(COMMAND));
        $this->assertTrue(is_int($shell->read(self::COMMAND_PROMPT)));
        $str1 = $shell->getResult();
        $this->assertGreaterThanOrEqual(strlen($shell->prompt()), strlen($str1));
        $this->assertTrue(is_int(strpos($str1, $shell->prompt())));

        return $shell;
    }

    /**
     * @group Shell
     * @covers Shell::exec
     * @depends testReadWrite
     */
    public function testExec(Shell $shell)
    {
        $str = $shell->exec(COMMAND);

        $this->assertGreaterThanOrEqual(strlen($shell->prompt()), strlen($str));

        $promptPosition = strpos($str, $shell->prompt());
        $this->assertTrue(is_int($promptPosition));

        $commandPosition = strpos($str, COMMAND);
        $this->assertTrue(is_int($commandPosition));

        $usernamePosition = strpos($str, USERNAME);
        $this->assertTrue(is_int($usernamePosition));

        $this->assertGreaterThanOrEqual($commandPosition, $usernamePosition);
        $this->assertGreaterThanOrEqual($usernamePosition, $promptPosition);

        return $shell;
    }

    /**
     * @group Shell
     * @covers Shell::isConnected
     * @covers Shell::isLoggedIn
     * @covers Shell::isOnLine
     * @covers Shell::logout
     * @depends testExec
     */
    public function testLogout(Shell $shell)
    {
        $this->assertTrue($shell->isConnected());
        $this->assertTrue($shell->isLoggedIn());
        $this->assertTrue($shell->isOnLine());

        $this->assertTrue($shell->logout());
        $this->assertFalse($shell->logout());

        $this->assertFalse($shell->isLoggedIn());
        $this->assertFalse($shell->isOnLine());
        $this->assertTrue($shell->isConnected());
    }

}
