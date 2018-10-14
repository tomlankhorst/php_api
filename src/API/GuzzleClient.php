<?php

namespace Keepa\API;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class GuzzleClient implements HttpClientInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var \GuzzleHttp\Psr7\Response
     */
    protected $response;

    /**
     * @var HandlerStack
     */
    protected $stack;

    /**
     * @var array
     */
    protected $postData = [];

    /**
     * @var string
     */
    protected $method = 'GET';

    /**
     * @var string
     */
    protected $url;

    /**
     * @var array
     */
    protected $headers = [
        'Accept-Encoding' => 'gzip'
    ];

    /**
     * @inheritDoc
     */
    public function __construct(){}

    /**
     * @inheritDoc
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setUserAgent($agent)
    {
        $this->headers['User-Agent'] = $agent;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setPostData($data)
    {
        $this->postData = array_merge($this->postData, $data);
        $this->method = 'POST';
        return $this;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function get()
    {
        try {
            $this->response = $this->getClient()->request($this->method, $this->url, [
                'headers' => $this->headers,
                'form_params' => $this->postData
            ]);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            throw new \Exception("GuzzleException", 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getResponseCode()
    {
        return $this->response->getStatusCode();
    }

    /**
     * @inheritDoc
     */
    public function getBody()
    {
        return $this->response->getBody();
    }

    /**
     * @param HandlerStack $handler
     * @return $this
     */
    public function setHandler(HandlerStack $handler)
    {
        $this->stack = $handler;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function __destruct(){}

    /**
     * @return Client
     */
    protected function getClient()
    {
        $opts = [
            'handler' => $this->stack
        ];

        return new Client($opts);
    }

}