<?php
/**
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
 * ShellConnector
 * The interface declare basic functions for connecting and exchanging data with
 * the server.
 *
 * @author Igor Dyshlenko
 * @category Console
 * @license https://opensource.org/licenses/MIT MIT
 */

interface ShellConnector {

	/**
	 * Constructor
	 * @param string $host
	 * @param int $port
	 * @param mixed $logger - PEAR Log class object for logging all events, any
	 *						other value is ignored.
	 * @throws LogicException if connect to server is fail.
	 */
	public function __construct($host, $port, $logger=null);

	/**
	 * Login function
	 * @param string $userName
	 * @param string $pass
	 * @return bool TRUE if success.
	 * @throws LogicException if authentication error.
	 */
	public function login($userName, $pass='');

	/**
	 * Logout function
	 * @return bool TRUE if success, FALSE if fail.
	 */
	public function logout();

	/**
	 * Disconnect function
	 */
	public function disconnect();

	/**
	 * Get "is connected" state
	 * @return bool TRUE if is connected.
	 */
	public function isConnected();

	/**
	 * Get "is logged in" state
	 * @return bool TRUE if is loged in.
	 */
	public function isLoggedIn();

	/**
	 * Get Error Message.
	 * @return string error message if error, empty string ('') otherwise
	 */
	public function getError();

	/**
	 * Get Error Number.
	 * @return mixed - int error code if error, NULL otherwise
	 */
	public function getErrno();

	/**
	 * Read character from stream
	 * @return mixed string - readed character or FALSE if EOF or error
	 */
	public function read();

	/**
	 * Write data to stream.
	 * @param string $data - data for write
	 * @return mixed - number of written chars or FALSE if error
	 */
	public function write($data);

}