<?php
/**
 * Description of LogWraperTest
 *
 * @author din70 <igor.dyshlenko@gmail.com>
 */
use din70\Tools\ShellAccess\LogWrapper;
use PHPUnit\Framework\TestCase;

require_once '../din70/Tools/ShellAccess/LogWrapper.php';

class LogWrapperTest extends TestCase
{
    const
            MSG_LOG = 'Its a LOG test message!',
            MSG_DEBUG = 'Its a DEBUG test message!',
            MSG_INFO = 'Its a INFO test message!',
            MSG_NOTICE = 'Its a NOTICE test message!',
            MSG_WARNING = 'Its a WARNING test message!',
            MSG_ERR = 'Its a ERR test message!',
            MSG_CRIT = 'Its a CRIT test message!',
            MSG_ALERT = 'Its a ALERT test message!',
            MSG_EMERG = 'Its a EMERG test message!';

    protected $logger = null,
            $wrapper = null;

    protected function setUp()
    {
        @require_once 'Log.php';
        if (!class_exists('Log')) {
            $this->markTestSkipped('PEAR Log class not found.');
        }

        $this->logger = Log::singleton('display');
        $this->wrapper = new LogWrapper($this->logger);
    }

    /**
     * @group Shell
     * @covers LogWrapper::log
     */
    public function testLog()
    {
        $this->expectOutputRegex('/' . self::MSG_LOG . '/');
        $this->wrapper->log(self::MSG_LOG);
    }

    /**
     * @group Shell
     * @covers LogWrapper::alert
     */
    public function testAlert()
    {
        $this->expectOutputRegex('/' . self::MSG_ALERT . '/');
        $this->wrapper->alert(self::MSG_ALERT);
    }

    /**
     * @group Shell
     * @covers LogWrapper::crit
     */
    public function testCrit()
    {
        $this->expectOutputRegex('/' . self::MSG_CRIT . '/');
        $this->wrapper->crit(self::MSG_CRIT);
    }

    /**
     * @group Shell
     * @covers LogWrapper::debug
     */
    public function testDebug()
    {
        $this->expectOutputRegex('/' . self::MSG_DEBUG . '/');
        $this->wrapper->debug(self::MSG_DEBUG);
    }

    /**
     * @group Shell
     * @covers LogWrapper::emerg
     */
    public function testEmerg()
    {
        $this->expectOutputRegex('/' . self::MSG_EMERG . '/');
        $this->wrapper->emerg(self::MSG_EMERG);
    }

    /**
     * @group Shell
     * @covers LogWrapper::err
     */
    public function testErr()
    {
        $this->expectOutputRegex('/' . self::MSG_ERR . '/');
        $this->wrapper->err(self::MSG_ERR);
    }

    /**
     * @group Shell
     * @covers LogWrapper::info
     */
    public function testInfo()
    {
        $this->expectOutputRegex('/' . self::MSG_INFO . '/');
        $this->wrapper->info(self::MSG_INFO);
    }

    /**
     * @group Shell
     * @covers LogWrapper::notice
     */
    public function testNotice()
    {
        $this->expectOutputRegex('/' . self::MSG_NOTICE . '/');
        $this->wrapper->notice(self::MSG_NOTICE);
    }

    /**
     * @group Shell
     * @covers LogWrapper::warning
     */
    public function testWarning()
    {
        $this->expectOutputRegex('/' . self::MSG_WARNING . '/');
        $this->wrapper->warning(self::MSG_WARNING);
    }
}
