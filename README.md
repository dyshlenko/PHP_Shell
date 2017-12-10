# shell
v 0.01b
PHP connect to remote host over ssh

Connect to a remote host through the adapter class. Write / read data to the stream, execute commands on the remote host, obtain the result of the execution of commands.
The bundled adapter class for connecting to ssh2. It is possible to develop an adapter class for telnet without modifying the existing code.

Example of using:
```php
// Load classes files.
require_once 'lib/LogWrapper.php';
require_once 'lib/ShellConnector.php';
require_once 'lib/Ssh2Connector.php';
require_once 'lib/Shell.php';

// Logger initialization
require_once 'Log.php';
$logger = Log::singleton('console');
$logger->setMask(PEAR_LOG_ALL);

const HOST = 'localhost';
const COMMAND = 'df -lh';
const
    LOGIN = 'user',
    PASSWORD = 'password';

$shell = new Shell(new Ssh2Connector(HOST, 22, $logger), null, null, $logger);

echo "\n\n<b>Login as ", LOGIN, ', password ', PASSWORD, "</b>\n";
$shell->login(LOGIN, PASSWORD);

echo "\nisConnected() = ";
var_dump($shell->isConnested());

echo "\nisLoggedIn() = ";
var_dump($shell->isLoggedIn());

echo "\nisOnline() = ";
var_dump($shell->isOnLine());

$shell->eol("\r");                // Set end-of-line symbol
echo "\ngoAhead().\n";
$shell->goAhead();                // Get all chars before command prompt to internal buffer.
echo "\ngetResult() = ";
var_dump($shell->getResult());    // Get data from internal buffer.

// Execute command
echo "\n\n<b>Execute command \"", COMMAND, "\"</b>\n";
var_dump($result = $shell->exec(COMMAND));

echo "\n\n<b>Logout ", LOGIN, "</b>\n";
$shell->logout();

echo "\nisConnected() = ";
var_dump($shell->isConnested());

echo "\nisLoggedIn() = ";
var_dump($shell->isLoggedIn());

echo "\nisOnline() = ";
var_dump($shell->isOnLine());
```
