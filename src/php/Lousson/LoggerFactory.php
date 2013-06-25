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
 *  Lousson\LoggerFactory class declaration
 *
 *  @package    org.lousson.logger
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson;

/** Interfaces: */
use Psr\Log\LoggerInterface;

/** Dependencies: */
use Lousson\Logger;
use Log;
use Closure;

/** Exceptions: */
use Lousson\LoggerError;

/**
 *  A factory for logger instances
 *
 *  The Lousson\LoggerFactory class is capable of creating logger instances
 *  of various fashion and purpose, all implementing the LoggerInterface as
 *  specified in PSR-3.
 *
 *  @since      lousson/Lousson_Logger-0.1.0
 *  @package    org.lousson.logger
 *  @link       http://www.php-fig.org/psr/3/
 */
class LoggerFactory
{
    /**
     *  Create a factory instance
     *
     *  The constructor requires a default $logger to be associated with
     *  the factory. If it is omitted, the factory will elude to a logger
     *  based on top of PHP's error_log() method.
     *
     *  @param  LoggerInterface     $logger     The default logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        if (null === $logger) {
            $logger = $this->createLoggerFromScratch();
        }

        $this->logger = $logger;
    }

    /**
     *  Create a logger instance
     *
     *  The createLogger() method returns an instance of the PSR-3 logger
     *  inteface that operates on top of the given $base. The following
     *  base types are supported:
     *
     *- \Psr\Log\LoggerInterface
     *  Instances of the PSR-3 logger interface are left untouched and
     *  returned as they are (see http://www.php-fig.org/psr/3/)
     *
     *- \Log
     *  Instances of the PEAR Log class are wrapped to become compatible
     *  with the LoggerInterface (see http://pear.php.net/package/Log/)
     *
     *- \Closure
     *  Closures are wrapped using \Lousson\Logger instances
     *
     *- callable
     *  Callables in general are wrapped by \Lousson\Logger instances and
     *  closures (see http://php.net/manual/en/language.types.callable.php)
     *
     *- NULL
     *  If the $base parameter is omitted or if it is NULL, the factory's
     *  default logger is returned
     *
     *  Note that Closures and callables must implement the interface of
     *  the PSR-3 LoggerInterface::log() method (where the 3rd parameter
     *  is actually not required). Otherwise the behavior of the returned
     *  logger object is undefined.
     *
     *  @param  mixed               $base       The logger's base
     *
     *  @return \Psr\Log\LoggerInterface
     *          A logger instance is returned on succcess
     *
     *  @throws \Lousson\Error\LoggerError
     *          Raised in case the $base's type is not supported
     */
    public function createLogger($base = null)
    {
        if (!isset($base)) {
            $logger = $this->logger;
        }
        else if (is_object($base)) {
            $logger = $this->createLoggerFromObject($base);
        }
        else if (is_callable($base)) {
            $logger = $this->createLoggerFromCallback($base);
        }
        else {
            $type = gettype($base);
            $message = "Could not create logger from $type";
            throw new LoggerError($message);
        }

        return $logger;
    }

    /**
     *  Create a logger based on error_log()
     *
     *  The createLoggerFromScratch() method is used internally to create
     *  a logger instance that operates with PHP's error_log() functin.
     *
     *  @return \Lousson\Logger
     *          A logger instance is returned on success
     */
    private function createLoggerFromScratch()
    {
        $callback = function($level, $message) {
            $level = ucfirst($level);
            error_log("$level: $message");
        };

        $logger = new Logger($callback);
        return $logger;
    }

    /**
     *  Create a logger based on an object
     *
     *  The createLoggerFromObject() method is used internally to create
     *  a logger instance based on the given $object; which must be either
     *  a Closure, an instance of the PEAR Log class or the PSR-3 logger
     *  interface.
     *
     *  @param  object              $object     The object to use
     *
     *  @return \Psr\Log\LoggerInterface
     *          A logger instance is returned on success
     *
     *  @throws \Lousson\Error\LoggerError
     *          Raised in case the $object's type is not supported or if
     *          its state is invalid
     */
    private function createLoggerFromObject($object)
    {
        if ($object instanceof LoggerInterface) {
            $logger = $object;
        }
        else if ($object instanceof Closure) {
            $logger = new Logger($object);
        }
        else if ($object instanceof Log) {
            $logger = $this->createLoggerFromLog($object);
        }
        else {
            $class = get_class($object);
            $message = "Could not create logger from $class instance";
            throw new LoggerError($message);
        }

        return $logger;
    }

    /**
     *  Create a callback log instance
     *
     *  The createLoggerFromCallback() method is used internally to create
     *  a logger instance wrapping the $callback provided.
     *
     *  @param  callable            $callback   The callback to invoke
     *
     *  @return \Lousson\Logger
     *          A logger instance is returned on success
     */
    private function createLoggerFromCallback($callback)
    {
        $callback = function($level, $message) use ($callback) {
            call_user_func($callback, $level, $message);
        };

        $logger = new Logger($callback);
        return $logger;
    }

    /**
     *  Create a PEAR Log decorator
     *
     *  The createLoggerFromLog() method is used internally to create a
     *  logger instance that decorates a PEAR Log object.
     *
     *  @param  Log                 $log        The PEAR Log instance
     *
     *  @return \Lousson\Logger
     *          A logger instance is returned on success
     */
    private function createLoggerFromLog(Log $log)
    {
        static $map = array(
            Logger::LOG_EMERGENCY => PEAR_LOG_EMERG,
            Logger::LOG_ALERT => PEAR_LOG_ALERT,
            Logger::LOG_CRITICAL => PEAR_LOG_CRIT,
            Logger::LOG_ERROR => PEAR_LOG_ERR,
            Logger::LOG_WARNING => PEAR_LOG_WARNING,
            Logger::LOG_NOTICE => PEAR_LOG_NOTICE,
            Logger::LOG_INFO => PEAR_LOG_INFO,
            Logger::LOG_DEBUG => PEAR_LOG_DEBUG,
        );

        $callback = function($level, $message) use ($log, $map) {
            $level = $map[$level];
            $log->log($message, $level);
        };

        $logger = new Logger($callback);
        return $logger;
    }

    /**
     *  The default logger instance
     *
     *  @var \Psr\Log\LoggerInterface
     */
    private $logger;
}

