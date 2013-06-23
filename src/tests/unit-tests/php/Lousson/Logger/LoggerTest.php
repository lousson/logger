<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 textwidth=75: *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Copyright (c) 2013, The Lousson Project                               *
 *                                                                       *
 * All rights reserved.                                                  *
 *                                                                       *
 * Redistribution and use in source and binary forms, with or without    *
 * modification, are permitted provided that the following conditions    *
 * are met:                                                              *
 *                                                                       *
 * 1) Redistributions of source code must retain the above copyright     *
 *    notice, this list of conditions and the following disclaimer.      *
 * 2) Redistributions in binary form must reproduce the above copyright  *
 *    notice, this list of conditions and the following disclaimer in    *
 *    the documentation and/or other materials provided with the         *
 *    distribution.                                                      *
 *                                                                       *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   *
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     *
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS     *
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE        *
 * COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,            *
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES    *
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR    *
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)    *
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,   *
 * STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)         *
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED   *
 * OF THE POSSIBILITY OF SUCH DAMAGE.                                    *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 *  Lousson\LoggerTest class definition
 *
 *  @package    org.lousson.logger
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson;

/** Dependencies: */
use Lousson\Logger;
use Psr\Log\Test\LoggerInterfaceTest;

/**
 *  A test case for logger implementations
 *
 *  The Lousson\LoggerTest class is a test case for the Lousson\Logger
 *  class. It is derived from the one that ships with the PSR-3 reference
 *  implementation.
 *
 *  @since      lousson/Lousson_Logger-0.1.0
 *  @package    org.lousson.logger
 *  @link       http://www.phpunit.de/manual/current/en/
 */
class LoggerTest extends LoggerInterfaceTest
{
    /**
     *  Obtain the logger instance
     *
     *  The getLogger() method returns an instance of the logger class
     *  that is to be tested.
     *
     *  @return \Lousson\Logger
     *          A logger instance is returned on success
     */
    public function getLogger()
    {
        $cache = &$this->messages;
        $callback = function($level, $message) use (&$cache) {
            $cache[] = "$level $message";
        };

        $logger = new Logger($callback);
        return $logger;
    }

    /**
     *  Obtain the log messages
     *
     *  The getLogs() method returns all the logs passed to the logger
     *  returned by getLogger() since the last invocation of getLogs() -
     *  in the exact same order they've been issued and in the format of
     *  "$level $message".
     *
     *  @return array
     *          A list of log entries is returned on success
     */
    public function getLogs()
    {
        $logs = array();

        while(!empty($this->messages)) {
            $logs[] = array_shift($this->messages);
        }

        return $logs;
    }

    /**
     *  A cache for log messages
     *
     *  @var array
     */
    private $messages = array();
}

