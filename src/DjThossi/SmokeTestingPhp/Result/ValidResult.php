<?php
namespace DjThossi\SmokeTestingPhp\Result;

use DjThossi\SmokeTestingPhp\ValueObject\Body;
use DjThossi\SmokeTestingPhp\ValueObject\StatusCode;
use DjThossi\SmokeTestingPhp\ValueObject\TimeToFirstByte;
use DjThossi\SmokeTestingPhp\ValueObject\Url;

class ValidResult implements Result
{
    /**
     * @var Url
     */
    private $url;

    /**
     * @var Body
     */
    private $body;

    /**
     * @var TimeToFirstByte
     */
    private $timeToFirstByte;

    /**
     * @var StatusCode
     */
    private $statusCode;

    /**
     * @param Url $url
     * @param Body $body
     * @param TimeToFirstByte $timeToFirstByte
     * @param StatusCode $statusCode
     */
    public function __construct(Url $url, Body $body, TimeToFirstByte $timeToFirstByte, StatusCode $statusCode)
    {
        $this->url = $url;
        $this->body = $body;
        $this->timeToFirstByte = $timeToFirstByte;
        $this->statusCode = $statusCode;
    }

    /**
     * @return Url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return Body
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return StatusCode
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return TimeToFirstByte
     */
    public function getTimeToFirstByte()
    {
        return $this->timeToFirstByte;
    }

    /**
     * Returns a string including statuscode, time to first byte and body.
     *
     * @return string
     */
    public function asString()
    {
        return sprintf(
            "StatusCode: %s\nTimeToFirstByte: %u\nBody: %s",
            $this->statusCode->asInteger(),
            $this->timeToFirstByte->inMilliSeconds(),
            $this->body->asString()
        );
    }

    /**
     * @return bool
     */
    public function isValidResult()
    {
        return true;
    }
}
