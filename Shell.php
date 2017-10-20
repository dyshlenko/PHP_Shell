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
 * Shell performs operations with the connected server (ssh2) - connect, execute
 * commands, obtain execution results, etc.
 * The possibility of working on other protocols (for example, telnet) is
 * provided for the implementation of the corresponding classes with the
 * ShellConnector interface.
 *
 * The concept is taken from the class Net_Telnet,
 * Copyright 2012 Jesse Norell <jesse@kci.net>
 * Copyright 2012 Kentec Communications, Inc.
 *
 * @author Igor Dyshlenko
 * @category Console
 * @license https://opensource.org/licenses/MIT MIT
 */

class Shell {
	protected
		$connector,		// 
		$eol = PHP_EOL,	// End of line code
		$timeout = 5,	// Timeout for io operations (sec.)
		$useSleep = 0,	// Use usleep (timeout in ms)
		$prompt,		// Terminal command prompt
		$logger;		// Logger - object Log class

	protected
		$readBuffer,
		$writeBuffer;

	/**
	 * Constructor
	 * @param ShellConnector $connector
	 * @param string $prompt - Command interpreter prompt
	 * @param Log $logger - PEAR Log object or null
	 * @throws LogicException
	 */
	public function __construct(ShellConnector $connector, $prompt='$ ', $timeout=null, $logger=null) {
		$this->logger = new LogWrapper($logger);

		if (($tOut = intval($timeout)) > 0) {
			$this->timeout = $tOut;
			$this->logger->debug(__METHOD__ . ': Timeout setted to ' . $tOut . ' sec.');
		}
		unset($tOut);

		if (!$connector->isConnected()) {
			$this->logger->err(__METHOD__ . ': Fail: connector state "Disconnected"!');
			throw new LogicException('Fail: connector state "Disconnected"!');
		}
		$this->connector = $connector;
		$this->prompt = $prompt;
	}

	public function __destruct(){
		$this->logout();
		if ($this->connector->isLoggedIn()) {
			$this->connector->logout();
		}
		if ($this->connector->isConnected()) {
			$this->connector->disconnect();
		}
	}

	/**
	 * Login function
	 * @param string $userName
	 * @param string $pass
	 * @return bool true if success.
	 * @throws LogicException if authentication error.
	 */
	public function login($username, $pass) {
		return $this->connector->login($username, $pass);
	}

	/**
	 * Logout function
	 * @return bool true if success, false if fail.
	 */
	public function logout() {
		return $this->connector->logout();
	}

	/**
	 * Execute the command.
	 * @param string $command
	 * @return string result.
	 */
	public function exec($command) {
		$this->write($command . $this->eol);
		$this->read($this->prompt);
		return $this->getResult();
	}

	/**
	 * @todo Abort execution of command
	 */
/*	public function stop() {
		
	}
*/

	/**
	 * Get the result (screen buffer).
	 * @return string
	 */
	public function getResult() {
		$result = $this->readBuffer;
		$this->readBuffer = '';
		return $result;
	}

	/**
	 * Get Error Message.
	 * @return string error message if error, empty string ('') otherwise
	 */
	public function getError() {
		return $this->connector->getError();
	}

	/**
	 * Get Error number.
	 * @return mixed int error code if error, NULL otherwise
	 */
	public function getErrno() {
		return $this->connector->getErrno();
	}

	/**
	 * Get "is connected" state
	 * @return bool
	 */
	public function isConnested() {
		return $this->connector->isConnected();
	}

	/**
	 * Get "is logged in" state
	 * @return bool
	 */
	public function isLoggedIn() {
		return $this->connector->isLoggedIn();
	}

	/**
	 * Can execute the operation?
	 * @return boolean
	 */
	public function isOnLine() {
		return $this->isConnested() && $this->isLoggedIn();
	}

	/***
	 * @todo Is the previous command completed normally?
	 */
/*	public function isOk() {
		return false;
	}
*/
	/**
	 * Set / get end-of-line value
	 * @param mixed $eol if NULL - do nothing, else - set new end-of-line value.
	 * @return string current end-of-line value.
	 */
	public function eol($eol=null) {
		if (!is_null($eol)){
			$this->eol = strval($eol);
			$this->logger->debug(__METHOD__ . ': End of line setted to "' .
				str_replace(array("\n", "\r", "\t"), array('\n', '\r', '\t'), $eol) . '"');
		}
		return $this->eol;
	}

	/**
	 * Set / get the value of the command prompt.
	 * @param mixed $prompt if NULL - do nothing, else - set new command line
	 *						prompt value.
	 * @return string current prompt command line value.
	 */
	public function prompt($prompt=null) {
		if (!is_null($prompt)){
			$this->prompt = strval($prompt);
			$this->logger->debug(__METHOD__ . ': Prompt setted to "' .
				str_replace(array("\n", "\r", "\t"), array('\n', '\r', '\t'), $prompt) . '"');
		}
		return $this->prompt;
	}

	/**
	 * Skip all data before the command prompt.
	 * @return mixed FALSE if error, (int) count readed data bytes otherwise
	 */
	public function goAhead() {
		return $this->read($this->prompt);
	}

	/**
	 * Skip data from output stream
	 * @param string $searchFor - skip data to the desired value inclusive.
	 * @param int $numChars - skip a maximum of $numChars characters.
	 * @return mixed FALSE if error, (int) count readed data bytes otherwise
	 */
	public function read($searchFor=null, $numChars=null) {
		$buffer = '';
		$nums = intval($numChars);
		$search = strval($searchFor);
		$found = false;
		$started = time();
		$timedOut = false;

		while (	!$found && !$timedOut &&
				(($nums == 0) || ($nums && (strlen($buffer) < $nums))) &&
				(($char= $this->readStream()) !== false)	) {

			$buffer .= $char;

			if (($searchFor !== null) && (substr($buffer, 0 - strlen($search)) === $search)) {
				$this->lastmatch = $search;
				$found = true;
				continue;
			}

			$timedOut = ((time() - $started) > $this->timeout);
			$found = ($nums && (strlen($buffer) >= $nums));
		}

		$this->readBuffer .= $buffer;

		return ($found) ? strlen($buffer) : false;
	}

	/**
	 * Get symbol from connector input stream
	 * @return mixed string or FALSE if eof().
	 */
	protected function readStream() {
		return $this->connector->read();
	}

	/**
	 * Write data to stream
	 * @param string $data - data for write to input stream
	 * @return int number of written chars
	 */
	public function write($data) {
		$this->writeBuffer .= $data;
		return $this->writeStream();
	}

	/**
	 * Put data to connector output stream
	 * @param type $data
	 * @return int
	 * @throws RuntimeException
	 */
	protected function writeStream($data=null) {
		$written = 0;
		$n = 0;

		if (!$this->isOnLine()){
			return 0;
		}

		if (($data !== null) and (strlen($data) > 0)){
			$buf = $data;
			$total = strlen($data);
		} else {
			$buf = $this->writeBuffer;
			$total = strlen($this->writeBuffer);
			$this->writeBuffer = null;
		}

		while ($written < $total) {
			$buf = substr($buf, $n);
			if (($n = $this->connector->write($buf)) === false) {
				if (!$this->isConnested()){
					$this->logger->debug(__METHOD__ . ': Disconnected.');
					break;
				} else {
					$this->logger->err(__METHOD__ . ': Error writing to socket.');
					throw new RuntimeException('Error writing to socket.');
				}
			}
			$written += $n;
		}

		return $written;
	}
}