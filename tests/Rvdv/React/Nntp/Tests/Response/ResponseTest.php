<?php

/*
 * This file is part of React NNTP.
 *
 * (c) Robin van der Vleuten <robinvdvleuten@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rvdv\React\Nntp\Tests\Response;

use Rvdv\React\Nntp\Response\Response;

/**
 * ResponseTest
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function responseShouldBeCreatedFromString()
    {
        $response = new Response();

        $response->write("200 Successful response\r\n");

        $this->assertInstanceOf('Rvdv\React\Nntp\Response\ResponseInterface', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Successful response", $response->getMessage());
    }

    /**
     * @test
     */
    public function invalidArgumentWhenInvalidString()
    {
        $this->setExpectedException('InvalidArgumentException');

        $response = new Response();

        $response->write("A very very invalid string\r\n");
    }

    /**
     * @test
     */
    public function runtimeErrorWhenInvalidStatusCode()
    {
        $this->setExpectedException('RuntimeException');

        $response = new Response();

        $response->write("000 Unknown status code\r\n");
    }

    /**
     * @test
     */
    public function indicatingMultilineWhenSpecificStatusCode()
    {
        $response = new Response();

        $response->write("222 Multiline response\r\n");

        $this->assertTrue($response->isMultilineResponse());
    }

    /**
     * @test
     */
    public function indicatingNotMultilineWhenSpecificStatusCode()
    {
        $response = new Response();

        $response->write("200 Not multiline response\r\n");

        $this->assertFalse($response->isMultilineResponse());
    }

    /**
     * @test
     */
    public function responseCanBeTypeCastedToString()
    {
        $response = new Response();

        $response->write("200 Successful response\r\n");

        $this->assertEquals("200 Successful response", (string) $response);
    }
}
