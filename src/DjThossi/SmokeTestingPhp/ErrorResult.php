<?php
namespace DjThossi\SmokeTestingPhp;

class ErrorResult implements Result
{
    /**
     * @var Url
     */
    private $url;

    /**
     * @var string
     */
    private $errorMessage;

    /**
     * @param Url $url
     * @param string $errorMessage
     */
    public function __construct(Url $url, $errorMessage)
    {
        $this->ensureValidErrorMessage($errorMessage);

        $this->url = $url;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Returns a string including statuscode, time to first byte and body.
     *
     * @return string
     */
    public function asFailureMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param string $body
     *
     * @throws SmokeTestException
     */
    private function ensureValidErrorMessage($body)
    {
        if (!is_string($body)) {
            $message = sprintf(
                'Expected body to be string, got "%s"',
                is_object($body) ? get_class($body) : gettype($body)
            );
            throw new SmokeTestException($message);
        }
    }

    /**
     * @return Url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function getTimeToFirstByteInMilliseconds()
    {
        return 0;
    }

    public function getBody()
    {
        return null;
    }
}