<?php

namespace Rvdv\React\Tests\Nntp;

use Phake;
use React\EventLoop\StreamSelectLoop;
use React\Socket\ConnectionInterface;
use React\Socket\Server;
use Rvdv\React\Nntp\Client;

class ClientTest extends TestCase
{
    /**
     * @test
     */
    public function factoryShouldReturnAClient()
    {
        $loop = Phake::mock('React\EventLoop\StreamSelectLoop');
        $dns = Phake::mock('React\Dns\Resolver\Resolver');

        $client = Client::factory($loop, $dns);
        $this->assertInstanceOf('Rvdv\React\Nntp\Client', $client);
    }

    /**
     * @test
     */
    public function connectionToNntpServerShouldReturnResponse()
    {
        $receivedResponse = null;

        $loop = new StreamSelectLoop();
        $dns = Phake::mock('React\Dns\Resolver\Resolver');

        $server = new Server($loop);
        $server->on('connection', $this->expectCallableOnce());
        $server->on('connection', function ($connection) use ($server, $loop) {
            $connection->write("200 Hello from the NNTP server\r\n");
        });
        $server->listen(9999);

        $client = Client::factory($loop, $dns);
        $client->connect('127.0.0.1', 9999)
            ->then(function ($response) use (&$receivedResponse, $loop) {
                $receivedResponse = $response;
                $loop->stop();
            })
        ;

        $client->run();

        $this->assertInstanceOf('Rvdv\React\Nntp\Response\ResponseInterface', $receivedResponse);
        $this->assertEquals(200, $receivedResponse->getStatusCode());
        $this->assertEquals("Hello from the NNTP server", $receivedResponse->getMessage());
    }

    /**
     * @test
     */
    public function connectionToUnknownNntpServerShouldFail()
    {
        $loop = new StreamSelectLoop();
        $dns = Phake::mock('React\Dns\Resolver\Resolver');

        $client = Client::factory($loop, $dns);
        $client->connect('127.0.0.1', 9999)
            ->then($this->expectCallableNever(), $this->expectCallableOnce())
        ;

        $client->run();
    }

    /**
     * @test
     */
    public function runAndStopShouldCallLoopInstanceMethods()
    {
        $loop = Phake::mock('React\EventLoop\StreamSelectLoop');
        $dns = Phake::mock('React\Dns\Resolver\Resolver');

        $client = Client::factory($loop, $dns);

        $client->run();
        $client->stop();

        Phake::verify($loop, Phake::times(1))->run();
        Phake::verify($loop, Phake::times(1))->stop();
    }
}
