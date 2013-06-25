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
 *  Lousson\Logger class declaration
 *
 *  @package    org.lousson.logger
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson;

/** Dependencies: */
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Closure;

/** Exceptions: */
use Lousson\LoggerError;

/**
 *  A generic logger implementation
 *
 *  The Lousson\Logger class is an implementation of the LoggerInterface
 *  specified in PSR-3.
 *
 *  @since      lousson/Lousson_Logger-0.1.0
 *  @package    org.lousson.logger
 *  @link       http://www.php-fig.org/psr/3/
 */
class Logger extends AbstractLogger
{
    /**
     *  The log-level indicating that the system is unusable
     *
     *  @var string
     */
    const LOG_EMERGENCY = LogLevel::EMERGENCY;

    /**
     *  The log-level indicating that an action must be taken immediately
     *
     *  @var string
     */
    const LOG_ALERT = LogLevel::ALERT;

    /**
     *  The log-level indicating critical conditions
     *
     *  @var string
     */
    const LOG_CRITICAL = LogLevel::CRITICAL;

    /**
     *  The log-level indicating error conditionsv
     *
     *  @var string
     */
    const LOG_ERROR = LogLevel::ERROR;

    /**
     *  The log-level indicating warning conditionsv
     *
     *  @var string
     */
    const LOG_WARNING = LogLevel::WARNING;

    /**
     *  The log-level indicating normal but significant conditions
     *
     *  @var string
     */
    const LOG_NOTICE = LogLevel::NOTICE;

    /**
     *  The log-level for informational messages
     *
     *  @var string
     */
    const LOG_INFO = LogLevel::INFO;

    /**
     *  The log-level for debug messages
     *
     *  @var string
     */
    const LOG_DEBUG = LogLevel::DEBUG;

    /**
     *  Create a logger instance
     *
     *  The constructor requires the provisioning of the actual logger
     *  $callback That is a closure that resembles the LoggerInterface's
     *  log() method's interface - where the 3rd parameter is actually
     *  not required.
     *
     *  @param  Closure             $callback   The logger callback
     */
    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     *  Log a context-sensitive message
     *
     *  The log() method is used to process the given $message according
     *  to the log $level and after applying the $context values, if any.
     *
     *  @param  string              $level      The message severity
     *  @param  string              $message    The message pattern
     *  @param  array               $context    The context values, if any
     *
     *  @throws \Lousson\Error\LoggerError
     *          Raised in case the $level is not recognized
     */
    public function log($level, $message, array $context = array())
    {
        $callback = $this->callback;
        $level = $this->fetchLevel($level);
        $message = $this->fetchMessage($message, $context);

        $callback($level, $message);
    }

    /**
     *  Fetch the log level
     *
     *  The fetchLevel() method is used internally to validate the $level
     *  passed to the log() method - and to ensure that it's a string.
     *
     *  @param  string              $level      The log level
     *
     *  @return string
     *          The normalized log level name is returned on success
     *
     *  @throws \Lousson\Error\LoggerError
     *          Raised in case the log $level is invalid
     */
    private function fetchLevel($level)
    {
        $level = (string) $level;

        if (!in_array($level, self::$logLevels)) {
            $message= "Could not log unknown level: \"$level\"";
            throw new LoggerError($message);
        }

        return $level;
    }

    /**
     *  Fetch the message to log
     *
     *  The fetchMessage() method is used internally to interpolate the
     *  message $pattern passed to the log() method with the data given
     *  in the $context array.
     *
     *  @param  string              $pattern    The message pattern
     *  @param  array               $context    The message context
     *
     *  @return string
     *          The interpolated log message is returned on success
     */
    private function fetchMessage($pattern, array $context = array())
    {
        $values = array();

        foreach ($context as $key => $value) {
            $values["{{$key}}"] = $this->fetchString($value);
        }

        $message = strtr($pattern, $values);
        return $message;
    }

    /**
     *  Fetch a values string representation
     *
     *  The fetchString() method is used internally to convert the given
     *  $value into its string representation.
     *
     *  @param  mixed               $value      The value to convert
     *
     *  @return string
     *          The $value's string representation is returned on success
     */
    private function fetchString($value)
    {
        if (is_scalar($value)) {
            $string = (string) $value;
        }
        else if (is_null($value)) {
            $string = "NULL";
        }
        else if (is_object($value)) {
            $string = $this->fetchStringFromObject($value);
        }
        else {
            $string = gettype($value);
        }

        return $string;
    }

    /**
     *  Fetch an objects string representation
     *
     *  The fetchStringFromObject() method is used internally to convert
     *  the given $object into its string representation.
     *
     *  @param  object              $object         The object to convert
     *
     *  @return string
     *          The $object's string representation is returned on success
     */
    private function fetchStringFromObject($object)
    {
        if (is_callable(array($object, "__toString"))) {
            $string = (string) $object;
        }
        else {
            $string = get_class($object);
        }

        return $string;
    }

    /**
     *  A list of all recognized log levels
     *
     *  @var array
     */
    private static $logLevels = array(
        self::LOG_EMERGENCY,
        self::LOG_ALERT,
        self::LOG_CRITICAL,
        self::LOG_ERROR,
        self::LOG_WARNING,
        self::LOG_NOTICE,
        self::LOG_INFO,
        self::LOG_DEBUG,
    );

    /**
     *  The logger callback
     *
     *  @var \Closure
     */
    private $callback;
}

