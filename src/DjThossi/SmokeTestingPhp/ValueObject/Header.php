<?php
namespace DjThossi\SmokeTestingPhp\ValueObject;

class Header
{
    /**
     * @var HeaderKey
     */
    private $key;

    /**
     * @var HeaderValue
     */
    private $value;

    /**
     * @param HeaderKey $key
     * @param HeaderValue $value
     */
    public function __construct(HeaderKey $key, HeaderValue $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @return HeaderKey
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return HeaderValue
     */
    public function getValue()
    {
        return $this->value;
    }
}
