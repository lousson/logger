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
 *  Lousson\LoggerFactoryTest class definition
 *
 *  @package    org.lousson.logger
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson;

/** Dependencies: */
use Lousson\LoggerFactory;
use Psr\Log\LoggerInterface;
use PHPUnit_Framework_TestCase;

/**
 *  A test case for logger factories
 *
 *  @since      lousson/Lousson_Logger-0.1.0
 *  @package    org.lousson.logger
 *  @link       http://www.phpunit.de/manual/current/en/
 */
class LoggerFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     *  Obtain the logger factory
     *
     *  The getLoggerFactory() method returns an instance of the logger
     *  factory that is to be tested.
     *
     *  @return \Lousson\LoggerFactory
     *          A logger factory instance is returned on success
     */
    public function getLoggerFactory(LoggerInterface $default = null)
    {
        if (0 === func_num_args()) {
            $factory = new LoggerFactory();
        }
        else {
            $factory = new LoggerFactory($default);
        }

        return $factory;
    }

    /**
     *  Obtain a logger callback
     *
     *  The getLoggerCallback() method returns a closure that should get
     *  used to mock the actual logging method.
     *
     *  @return \Closure
     *          A callback for mocks is returned on success
     */
    public function getLoggerCallback()
    {
        $callback = function($level, $message) use (&$cache) {
            LoggerFactoryTest::$level = $level;
            LoggerFactoryTest::$message = $message;
        };

        return $callback;
    }

    /**
     *  Obtain a logger mock
     *
     *  The getLoggerMock() method returns a mock of the LoggerInterface.
     *
     *  @return \Psr\Log\LoggerInterface
     *          A logger mock is returned on success
     */
    public function getLoggerMock()
    {
        $methods = self::$logLevels;
        $methods[] = "log";

        $callback = $this->getLoggerCallback();

        $logger = $this->getMock("Psr\\Log\\LoggerInterface", $methods);
        $logger
            ->expects($this->any())
            ->method("log")
            ->will($this->returnCallback($callback));

        return $logger;
    }

    /**
     *  Update logger data
     *
     *  The setLoggerData() method can be used instead of the callback
     *  returned by getLoggerCallback(). It updates the internally stored
     *  last log $level and $message, which is used in the smoke tests.
     *
     *  @param  string              $level      The log level
     *  @param  string              $message    The log message
     */
    public function setLoggerData($level, $message)
    {
        self::$level = $level;
        self::$message = $message;
    }

    /**
     *  Provide supported logger bases
     *
     *  The provideValidLoggerBases() method returns an array of multiple
     *  items, each of whose is an array of one item: A valid base for use
     *  with the factory's createLogger() method.
     *
     *  @return array
     *          A list of createLogger() parameters is returned on success
     */
    public function provideValidLoggerBases()
    {
        $callback = function($message, $level) {
            $level = LoggerFactoryTest::$logLevels[$level];
            LoggerFactoryTest::$level = $level;
            LoggerFactoryTest::$message = $message;
        };

        if (!class_exists("LogLousson")) {
            eval("
                class LogLousson extends Log {
                    public function __construct() {}
                }
            ");
        }

        $log = $this->getMock("LogLousson", array("log"));
        $log->expects($this->any())
            ->method("log")
            ->will($this->returnCallback($callback));

        $bases[][] = $this->getLoggerCallback();
        $bases[][] = array($this, "setLoggerData");
        $bases[][] = $log;

        return $bases;
    }

    /**
     *  Provide unsupported logger bases
     *
     *  The provideInalidLoggerBases() method returns an array of multiple
     *  items, each of whose is an array of one item: An invalid base that
     *  causes an exception when used with the createLogger() method.
     *
     *  @return array
     *          A list of createLogger() parameters is returned on success
     */
    public function provideInvalidLoggerBases()
    {
        $bases[][] = false;
        $bases[][] = new \StdClass();

        return $bases;
    }

    /**
     *  Test the default behavior
     *
     *  The testDefaultLogger() method is a test case that verifies that
     *  the fallback behavior of the factory's createLogger() method and
     *  the constructor is valid.
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an internal error
     */
    public function testDefaultLogger()
    {
        $factory = $this->fetchLoggerFactory();

        $this->fetchLogger($factory);
        $this->fetchLogger($factory, null);

        $mock = $this->getLoggerMock();
        $factory = $this->fetchLoggerFactory($mock);
        $logger = $this->fetchLogger($factory);

        $this->smokeTestAlpha($logger);

        $factory = $this->fetchLoggerFactory();
        $logger = $this->fetchLogger($factory, $mock);

        $this->smokeTestAlpha($logger);;

        $logger = $this->fetchLogger($factory);
        $filename = tempnam(sys_get_temp_dir(), "lousson");
        $setup = ini_set("error_log", $filename);
        $logger->log(Logger::LOG_DEBUG, $filename);
        ini_set("error_log", $setup);
        $content = file_get_contents($filename);
        $this->assertContains($filename, $content);
        unlink($filename);

    }

    /**
     *  Test the createLogger() method
     *
     *  The testCreateLogger() method is a test case for the factory's
     *  createLogger() method. It verifies that an invocation with the
     *  given $base is successful and performs some smoke tests with the
     *  returned logger instance.
     *
     *  @param  mixerd              $base       The logger's base
     *
     *  @dataProvider               provideValidLoggerBases
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\LoggerError
     *          Raised in case the $base is considered invalid
     *
     *  @throws \Exception
     *          Raised in case of an internal error
     */
    public function testCreateLogger($base)
    {
        $factory = $this->fetchLoggerFactory();
        $logger = $this->fetchLogger($factory, $base);

        $this->smokeTestAlpha($logger);
        $this->smokeTestBeta($logger);
    }

    /**
     *  Test the createLogger() method
     *
     *  The testCreateLoggerError() method is a test case that operates
     *  with invalid logger $base parameters, in order to check whether
     *  the factory raises a logger exception or ignores the issue.
     *
     *  @param  mixed               $base       The invalid base
     *
     *  @dataProvider               provideInvalidLoggerBases
     *  @expectedException          Lousson\LoggerError
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\LoggerError
     *          Raised in case the test case is successful
     *
     *  @throws \Exception
     *          Raised in case of an internal error
     */
    public function testCreateLoggerError($base)
    {
        $factory = $this->fetchLoggerFactory();
        $logger = $this->fetchLogger($factory, $base);
    }

    /**
     *  A smoke test for loggers
     *
     *  The smokeTestAlpha() method performs some simple tests on the
     *  $logger's methods. This requires the logger instance to either
     *  invoke the setLoggerData() method itself or to be mocked with
     *  the callback returned by getLoggerCallback().
     *
     *  @param  LoggerInterface     $logger     The logger to test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an internal error
     */
    protected function smokeTestAlpha(LoggerInterface $logger)
    {
        $i = 0;

        foreach (self::$logLevels as $level) {
            $message = "$level#log#" . $i++;
            $logger->log($level, $message);
            $this->assertEquals($level, self::$level, $message);
            $this->assertEquals($message, self::$message);
        }
    }

    /**
     *  A smoke test for loggers
     *
     *  The smokeTestBeta() method performs some simple tests on the
     *  $logger's methods. This requires the logger instance to either
     *  invoke the setLoggerData() method itself or to be mocked with
     *  the callback returned by getLoggerCallback().
     *
     *  @param  LoggerInterface     $logger     The logger to test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an internal error
     */
    protected function smokeTestBeta(LoggerInterface $logger)
    {
        $i = 0;

        foreach (self::$logLevels as $level) {
            $message = "$level#$level#" . $i++;
            $logger->$level($message);
            $this->assertEquals($level, self::$level, $message);
            $this->assertEquals($message, self::$message);
        }
    }

    /**
     *  Fetch a factory instance
     *
     *  The fetchLoggerFactory() method is used internally to invoke the
     *  test's getLoggerFactory() method with the given $default. It
     *  ensures that the returned value is a LoggerFactory instance.
     *
     *  @return \Logger\LoggerFactory
     *          A logger factory instance is returned on success
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an internal error
     */
    private function fetchLoggerFactory(LoggerInterface $default = null)
    {
        $factory = 0 === func_num_args()
            ? $this->getLoggerFactory()
            : $this->getLoggerFactory($default);

        $testClass = get_class($this);
        $this->assertInstanceOf(
            "Lousson\\LoggerFactory", $factory,
            "The $testClass::getLoggerFactory() method must return an ".
            "instance of the Lousson\\LoggerFactory class"
        );

        return $factory;
    }

    /**
     *  Fetch a logger instance
     *
     *  The fetchLogger() method is used internally to invoke the
     *  $factory's createLogger() method with the given $base. It ensures
     *  that the returned value is a LoggerInterface instance.
     *
     *  @return \Psr\Log\LoggerInterface
     *          A logger instance is returned on success
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\LoggerError
     *          Raised in case the $base is considered invalid
     *
     *  @throws \Exception
     *          Raised in case of an internal error
     */
    private function fetchLogger(LoggerFactory $factory, $base = null)
    {
        $factoryClass = get_class($factory);
        $logger = 1 === func_num_args()
            ? $factory->createLogger()
            : $factory->createLogger($base);

        $this->assertInstanceOf(
            "Psr\\Log\\LoggerInterface", $logger,
            "The $factoryClass::createLogger() method must return an ".
            "instance of the Psr\\Log\\LoggerInterface"
        );

        return $logger;
    }

    /**
     *  A list of all recognized log levels
     *
     *  @var array
     */
    private static $logLevels = array(
        Logger::LOG_EMERGENCY,
        Logger::LOG_ALERT,
        Logger::LOG_CRITICAL,
        Logger::LOG_ERROR,
        Logger::LOG_WARNING,
        Logger::LOG_NOTICE,
        Logger::LOG_INFO,
        Logger::LOG_DEBUG,
    );

    /**
     *  The last log level issued
     *
     *  @var string
     */
    private static $level;

    /**
     *  The last log message issued
     *
     *  @var string
     */
    private static $message;
}

