<?php
/* * *
 * Copyright 2016 Igor Dyshlenko
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 * Example of use Shell class.
 *
 * @author Igor Dyshlenko
 * @category Console
 * @see exampleShell.html
 * @license https://opensource.org/licenses/MIT MIT
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Example of use Shell class</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <pre>
            <?php
            $start = time();

            ini_set('request_order', 'CGP');

            // Report all PHP errors
            error_reporting(-1);
            ini_set('error_reporting', E_ALL);

            use din70\Tools\ShellAccess\Ssh2Connector;
            use din70\Tools\ShellAccess\Shell;

            require_once '../din70/Tools/ShellAccess/LogWrapper.php';
            require_once '../din70/Tools/ShellAccess/ShellConnectorInterface.php';
            require_once '../din70/Tools/ShellAccess/Ssh2Connector.php';
            require_once '../din70/Tools/ShellAccess/Shell.php';

            // https://pear.php.net/package/Log/ Logger initialization
            require_once 'Log.php';
            $logger = Log::singleton('console');
            $logger->setMask(PEAR_LOG_ALL);

            const HOST = 'localhost';
            const COMMAND = 'df -lh';
            const
                    LOGIN = 'user',
                    PASSWORD = 'password';

            $logger->info('Run main code.');

            $shell = new Shell(new Ssh2Connector(HOST, 22, $logger), null, null,
                               $logger);

            echo "\nisConnected() = ";
            var_dump($shell->isConnected());

            echo "\nisLoggedIn() = ";
            var_dump($shell->isLoggedIn());

            echo "\nisOnline() = ";
            var_dump($shell->isOnLine());

            echo "\n\n<b>Login as ", LOGIN, ', password ', PASSWORD, "</b>\n";
            $shell->login(LOGIN, PASSWORD);

            echo "\nisConnected() = ";
            var_dump($shell->isConnected());

            echo "\nisLoggedIn() = ";
            var_dump($shell->isLoggedIn());

            echo "\nisOnline() = ";
            var_dump($shell->isOnLine());

            $shell->eol("\r");
            echo "\ngoAhead().\n";
            $shell->goAhead();
            echo "\ngetResult() = ";
            var_dump($shell->getResult());

            echo "\n\n<b>Execute command \"", COMMAND, "\"</b>\n";
            var_dump($result = $shell->exec(COMMAND));

            echo "\n\n<b>Logout ", LOGIN, "</b>\n";
            $shell->logout();

            echo "\nisConnected() = ";
            var_dump($shell->isConnected());

            echo "\nisLoggedIn() = ";
            var_dump($shell->isLoggedIn());

            echo "\nisOnline() = ";
            var_dump($shell->isOnLine());

            echo "\n\nScript finished. Runing time = ", time() - $start, ' seconds.';
            ?>

        </pre>
    </body>
</html>
