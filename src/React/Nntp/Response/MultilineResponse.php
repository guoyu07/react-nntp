<?php

namespace React\Nntp\Response;

use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use React\Stream\ReadableStreamInterface;
use React\Stream\Util;
use React\Stream\WritableStreamInterface;

/**
 * MultilineResponse
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class MultilineResponse extends EventEmitter implements MultilineResponseInterface, ReadableStreamInterface
{
    private $lines;
    private $loop;
    private $readable = true;
    private $response;
    private $stream;

    /**
     * Constructor
     *
     * @param \React\Nntp\Response\ResponseInterface $response A Response instance.
     * @param \React\Stream\WritableStreamInterface  $stream   A WritableStreamInterface instance.
     * @param \React\EventLoop\LoopInterface         $loop     A LoopInterface instance.
     */
    public function __construct(ResponseInterface $response, WritableStreamInterface $stream, LoopInterface $loop)
    {
        $this->response = $response;
        $this->stream = $stream;
        $this->loop = $loop;

        $this->lines = array();

        $this->stream->on('data', array($this, 'handleData'));
        $this->stream->on('end', array($this, 'handleEnd'));
        $this->stream->on('error', array($this, 'handleError'));
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    /**
     * {@inheritDoc}
     */
    public function getMessage()
    {
        return $this->response->getMessage();
    }

    /**
     * {@inheritDoc}
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * {@inheritDoc}
     */
    public function isMultilineResponse()
    {
        return true;
    }

    public function isReadable()
    {
        return $this->readable;
    }

    public function pause()
    {

    }

    public function resume()
    {

    }

    public function pipe(WritableStreamInterface $dest, array $options = array())
    {
        Util::pipe($this, $dest, $options);

        return $dest;
    }

    public function close(\Exception $error = null)
    {
        if (!$this->readable) {
            return;
        }

        $this->readable = false;

        $this->emit('end', array($error, $this));

        $this->removeAllListeners();
    }

    public function handleData($data)
    {
        $this->buffer .= $data;

        if (false !== (bool) preg_match("/\r\n.(\r\n)?$/", $this->buffer)) {
            $this->lines = explode("\r\n", $this->buffer);

            if (end($this->lines) === "") {
                array_pop($this->lines);
            }

            if (end($this->lines) === ".") {
                array_pop($this->lines);
            }

            $this->buffer = null;

            $this->stream->removeListener('data', array($this, 'handleData'));
            $this->stream->removeListener('end', array($this, 'handleEnd'));
            $this->stream->removeListener('error', array($this, 'handleError'));

            $this->close();
        }
    }

    public function handleEnd()
    {
        var_dump(__FUNCTION__);
    }

    public function handleError()
    {
        var_dump(__FUNCTION__);
    }
}
