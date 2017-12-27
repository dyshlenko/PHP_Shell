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
 * LogWrapper is a wrapper for the Log class logger from the PEAR package and
 * a stub for the Shell class.
 *
 * @author Igor Dyshlenko
 * @category Console
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace din70\Tools\ShellAccess;

class LogWrapper
{

    /**
     * Instance of PEAR\Log object.
     * @var PEAR\Log 
     */
    protected $logger = null;

    /**
     * Constructor
     * @param PEAR\Log $logger
     */
    public function __construct($logger = null)
    {
        if ($logger && is_object($logger) && is_a($logger, 'Log')) {
            $this->logger = $logger;
        }
    }

    /**
     * Log an alert event. It will log a message at the PEAR_LOG_ALERT log level.
     * @param mixed $msg
     * @return bool True if the message was successfully logged.
     */
    public function alert($msg)
    {
        if ($this->logger) {
            return $this->logger->alert($msg);
        }
    }

    /**
     * Log a critical event. It will log a message at the PEAR_LOG_CRIT log
     * level.
     * @param mixed $msg
     * @return bool True if the message was successfully logged.
     */
    public function crit($msg)
    {
        if ($this->logger) {
            return $this->logger->crit($msg);
        }
    }

    /**
     * Log a debug event. It will log a message at the PEAR_LOG_DEBUG log level.
     * @param mixed $msg
     * @return bool True if the message was successfully logged.
     */
    public function debug($msg)
    {
        if ($this->logger) {
            return $this->logger->debug($msg);
        }
    }

    /**
     * Log a emergency event. It will log a message at the PEAR_LOG_EMERG log
     * level.
     * @param mixed $msg
     * @return bool True if the message was successfully logged.
     */
    public function emerg($msg)
    {
        if ($this->logger) {
            return $this->logger->emerg($msg);
        }
    }

    /**
     * Log a error event. It will log a message at the PEAR_LOG_ERR log level.
     * @param mixed $msg
     * @return bool True if the message was successfully logged.
     */
    public function err($msg)
    {
        if ($this->logger) {
            return $this->logger->err($msg);
        }
    }

    /**
     * Log a information event. It will log a message at the PEAR_LOG_INFO log
     * level.
     * @param mixed $msg
     * @return bool True if the message was successfully logged.
     */
    public function info($msg)
    {
        if ($this->logger) {
            return $this->logger->info($msg);
        }
    }

    /**
     * Log an event.
     * @param mixed $msg
     * @param int $priority (optional) The priority of the message. Valid values
     * 				are: LOG_EMERG, LOG_ALERT, LOG_CRIT, LOG_ERR, LOG_WARNING,
     * 				LOG_NOTICE, LOG_INFO, and LOG_DEBUG. The default is LOG_INFO.
     * @return bool True if the message was successfully logged.
     */
    public function log($msg, $priority = null)
    {
        if ($this->logger) {
            return $this->logger->log($msg, $priority);
        }
    }

    /**
     * Log a notice event. It will log a message at the PEAR_LOG_NOTICE log level.
     * @param mixed $msg
     * @return bool True if the message was successfully logged.
     */
    public function notice($msg)
    {
        if ($this->logger) {
            return $this->logger->notice($msg);
        }
    }

    /**
     * Log a warning event. It will log a message at the PEAR_LOG_WARNING log level.
     * @param mixed $msg
     * @return bool True if the message was successfully logged.
     */
    public function warning($msg)
    {
        if ($this->logger) {
            return $this->logger->warning($msg);
        }
    }

}
