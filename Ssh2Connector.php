<?php
/***
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
 * Ssh2Connector
 * The class provides basic functions for connecting and exchanging data with
 * the ssh2 server.
 *
 * @author Igor Dyshlenko
 * @category Console
 * @license https://opensource.org/licenses/MIT MIT
 */

class Ssh2Connector implements ShellConnector {
	protected $host, $port, $userName;
	protected $callbacks;

	protected 
		$connection		= null,
		$connected		= false,
		$connErrCode	= null,
		$connErrMsg		= '',
		$connErrLang	= null;

	protected $shell	= null;
	protected $logger	= null;
	protected $writeBuffer	=	null;

	/**
	 * Constructor
	 * @param string $host
	 * @param int $port - default 22.
	 * @param mixed $logger - PEAR Log class object for logging all events, any
	 *						other value is ignored.
	 * @throws BadFunctionCallException if ssh2_connect() function doesn't exist.
	 * @throws LogicException if connect to ssh server is fail.
	 */
	public function __construct($host, $port=22, $logger=null) {
		$this->logger = new LogWrapper($logger);

		if (!function_exists('ssh2_connect')) {
			$msg = 'Function ssh2_connect doesn\'t exist.';
			$this->logger->err(__METHOD__ . ': ' . $msg);
			throw new BadFunctionCallException($msg);
		}

		$this->host = $host;
		$this->port = $port;
		$this->callbacks = array('disconnect' => array($this, 'ssh_disconnect'));
		if (!($this->connection = ssh2_connect($host, $port, null, $this->callbacks))) {
			$msg = 'Fail: unable to establish connection to ' . $host . ':' . $port .
					"\n" . $this->connErrCode . ': ' . $this->connErrMsg;
			$this->logger->err(__METHOD__ . ': ' . $msg);
			throw new LogicException($msg);
		}
		$this->logger->debug(__METHOD__ . ': Connection to ' . $host . ':' . $port . ' established.');
		$this->connected = true;
	}

	public function __destruct() {
		$this->logout();
		$this->disconnect();
	}

	/**
	 * Login function
	 * @param string $userName - user name (login).
	 * @param string $pass - password.
	 * @return bool true if success.
	 * @throws LogicException if authentication error.
	 */
	public function login($userName, $pass='') {
		if (!ssh2_auth_password($this->connection, $userName, $pass)) {
			$msg = 'Fail: unable to authenticate user ' . $userName;
			$this->logger->err(__METHOD__ . ': ' . $msg);
			throw new LogicException($msg);
		}

		$this->userName = $userName;

		if (!($this->shell = ssh2_shell($this->connection, 'vt102', null, 80, 40, SSH2_TERM_UNIT_CHARS))) {
			$msg = 'Fail: unable to establish shell.';
			$this->logger->err(__METHOD__ . ': ' . $msg);
			throw new LogicException($msg);
		}

		$this->loggedIn = true;
		$this->logger->debug(__METHOD__ . ': user ' . $userName . ' logged in.');

		return true;
	}

	/**
	 * Logout function
	 * @return bool true if success, false if fail.
	 */
	public function logout() {
		if ($this->isLoggedIn()) {
			fclose($this->shell);
			$this->logger->debug(__METHOD__ . ': user ' . $this->userName . ' logged out.');

			return true;
		}

		return false;
	}

	public function disconnect() {}

	/**
	 * Get "is ssh2 connected" state
	 * @return bool
	 */
	public function isConnected() {
		return $this->connected;
	}

	/**
	 * Get "is logged in" state
	 * @return bool
	 */
	public function isLoggedIn() {
		return is_resource($this->shell);
	}

	/**
	 * Callback "disconnect" function for ssh2_connect().
	 * @param int $reason - Error Number
	 * @param string $message - Error message
	 * @param mixed $language
	 */
	public function ssh_disconnect($reason, $message, $language) {
		$this->connected = false;
		$this->connErrCode = $reason;
		$this->connErrMsg = $message;
		$this->connErrLang = $language;
		$this->logger->debug(__METHOD__ . ': SSH2 connection ' . $this->userName . '@' . $this->host .
			':' . $this->port . ' closed. ' . $reason . ': ' . $message);
	}

	/**
	 * Get Error Message returned ssh2_connect() function.
	 * @return string error message if error, empty string ('') otherwise
	 */
	public function getError() {
		return $this->connErrMsg;
	}

	/**
	 * Get Error Number returned ssh2_connect() function.
	 * @return mixed - int error code if error, NULL otherwise
	 */
	public function getErrno() {
		return $this->connErrCode;
	}

	/**
	 * Read character from stream.
	 * @return mixed - string (readed character) or FALSE if EOF or error.
	 */
	public function read() {

		if (!($this->isLoggedIn() && $this->isConnected()) || feof($this->shell)){
			$this->logger->debug(__METHOD__ . ': SSH2 connection ' . $this->userName . '@' . $this->host .
				':' . $this->port . ' closed or logged out or feof().');
			return false;
		}

		$char = fread($this->shell, 1);

		if ($char === '' && !feof($this->shell)) {
			usleep(100000);
			$char = fread($this->shell, 1);
		}

		if ($char === false) {
			$this->logger->debug(__METHOD__ . ': Error read from stream.');
		}

		return $char;
	}

	/**
	 * Write data to ssh connection.
	 * @param string $data - data for write
	 * @return int - number of written chars or FALSE if error
	 */
	public function write($data) {
		$total = strlen($data);
		$written = $n = 0;
		$buf = $data;

		while ($written < $total){
			$buf = substr($buf,$n);
			if (($n = fwrite($this->shell,$buf,4096)) === false) {
				if (feof($this->shell)) {
					$this->logger->debug(__METHOD__ . ': ' . $this->userName . '@' . $this->host . ' disconnected.');
					break;
				} else {
					$msg = 'Error writing to socket.';
					$this->logger->err(__METHOD__ . ': ' . $msg);
					throw new RuntimeException($msg);
				}
			}
			$written += $n;
		}

		fflush($this->shell);

		return !$this->connected || feof($this->shell) ? false : $written;
	}
}