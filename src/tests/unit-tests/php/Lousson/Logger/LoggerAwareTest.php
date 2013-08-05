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
 *  Lousson\LoggerAwareTest class definition
 *
 *  @package    org.lousson.logger
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson;

/** Dependencies: */
use Lousson\LoggerAware;
use PHPUnit_Framework_TestCase;

/**
 *  A test case for LoggerAware objects
 *
 *  @since      lousson/Lousson_Logger-1.2.0
 *  @package    org.lousson.logger
 *  @link       http://www.phpunit.de/manual/current/en/
 */
class LoggerAwareTest extends PHPUnit_Framework_TestCase
{
    /**
     *  Obtain the object to test with
     *
     *  The getLoggerAware() method returns the LoggerAware instance the
     *  tests operate on.
     *
     *  @return \Lousson\LoggerAware
     *          A logger aware object is returned on success
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function getLoggerAware()
    {
        $aware = new LoggerAware();
        return $aware;
    }

    /**
     *  Test the setLogger() method
     *
     *  The testSetLogger() method is a test case that verifies that the
     *  object does not return a NullLogger instance after setLogger() has
     *  been invoked with a custom logger implementation.
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testSetLogger()
    {
        $aware = $this->getLoggerAware();
        $before = $this->getMock("Psr\\Log\\LoggerInterface");
        $aware->setLogger($before);
        $after = $this->invokeGetLogger($aware);
        $this->assertNotInstanceOf("Psr\\Log\\NullLogger", $after);
    }

    /**
     *  Test the getLogger() method
     *
     *  The testGetLogger() method is a test case that verifies the object
     *  returning a logger instance even in case setLogger() has not been
     *  invoked before.
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testGetLogger()
    {
        $aware = $this->getLoggerAware();
        $this->invokeGetLogger($aware);
    }

    /**
     *  Invoke the getLogger() method
     *
     *  The invokeGetLogger() method is used internally to invoke the
     *  getLogger() method on the given $object and validate the type of
     *  the returned object before passing it back to the caller.
     *
     *  @param  LoggerAware         $object         The object to invoke
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    private function invokeGetLogger(LoggerAware $object)
    {
        $logger = $object->getLogger();
        $this->assertInstanceOf("Psr\\Log\\LoggerInterface", $logger);
        return $logger;
    }
}

