<?php

namespace Zyh\ApiGateway\Event;

use Zyh\ApiGateway\Http\Response;

class ResponseIsMorphing
{
    /**
     * Response instance.
     *
     * @var \Zyh\ApiGateway\Http\Response
     */
    public $response;

    /**
     * Response content.
     *
     * @var string
     */
    public $content;

    /**
     * Create a new response is morphing event. Content is passed by reference
     * so that multiple listeners can modify content.
     *
     * @param \Zyh\ApiGateway\Http\Response $response
     * @param string                   $content
     *
     * @return void
     */
    public function __construct(Response $response, &$content)
    {
        $this->response = $response;
        $this->content = &$content;
    }
}
