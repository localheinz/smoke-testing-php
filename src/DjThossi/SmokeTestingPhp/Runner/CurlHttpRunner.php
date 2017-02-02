<?php
namespace DjThossi\SmokeTestingPhp\Runner;

use Curl\CaseInsensitiveArray;
use Curl\Curl;
use Curl\MultiCurl;
use DjThossi\SmokeTestingPhp\Collection\HeaderCollection;
use DjThossi\SmokeTestingPhp\Collection\ResultCollection;
use DjThossi\SmokeTestingPhp\Options\RequestOptions;
use DjThossi\SmokeTestingPhp\Result\ErrorResult;
use DjThossi\SmokeTestingPhp\Result\ValidResult;
use DjThossi\SmokeTestingPhp\ValueObject\Body;
use DjThossi\SmokeTestingPhp\ValueObject\BodyLength;
use DjThossi\SmokeTestingPhp\ValueObject\Concurrency;
use DjThossi\SmokeTestingPhp\ValueObject\ErrorMessage;
use DjThossi\SmokeTestingPhp\ValueObject\Header;
use DjThossi\SmokeTestingPhp\ValueObject\HeaderKey;
use DjThossi\SmokeTestingPhp\ValueObject\HeaderValue;
use DjThossi\SmokeTestingPhp\ValueObject\StatusCode;
use DjThossi\SmokeTestingPhp\ValueObject\TimeToFirstByte;
use DjThossi\SmokeTestingPhp\ValueObject\Url;

class CurlHttpRunner implements HttpRunner
{
    /**
     * @var BodyLength
     */
    private $bodyLength;

    /**
     * @var MultiCurl
     */
    private $multiCurl;

    /**
     * @var ResultCollection
     */
    private $results;

    /**
     * @var callable
     */
    private $successCallback;

    /**
     * @var callable
     */
    private $errorCallback;

    /**
     * @param Concurrency $concurrency
     * @param BodyLength $bodyLength
     * @param callable $successCallback
     * @param callable $errorCallback
     */
    public function __construct(
        Concurrency $concurrency,
        BodyLength $bodyLength,
        callable $successCallback,
        callable $errorCallback
    ) {
        $this->multiCurl = new MultiCurl();
        $this->multiCurl->setConcurrency($concurrency->asInteger());
        $this->multiCurl->success([$this, 'onSuccess']);
        $this->multiCurl->error([$this, 'onError']);
        $this->bodyLength = $bodyLength;
        $this->successCallback = $successCallback;
        $this->errorCallback = $errorCallback;
    }

    /**
     * @param RequestOptions $requestOptions
     */
    public function addRequest(RequestOptions $requestOptions)
    {
        $curl = new Curl();
        $curl->setUrl($requestOptions->getUrl()->asString());
        $curl->setTimeout($requestOptions->getRequestTimeout()->inSeconds());
        $curl->setOpt(CURLOPT_FOLLOWLOCATION, $requestOptions->getFollowRedirect()->asBoolean());

        if ($requestOptions->needsBasicAuth()) {
            $curl->setBasicAuthentication(
                $requestOptions->getBasicAuth()->getUsername(),
                $requestOptions->getBasicAuth()->getPassword()
            );
        }

        $this->multiCurl->addCurl($curl);
    }

    /**
     * @return ResultCollection
     */
    public function run()
    {
        $this->results = new ResultCollection();
        $this->multiCurl->start();

        return $this->results;
    }

    /**
     * @param Curl $curl
     */
    public function onSuccess(Curl $curl)
    {
        $curlInfo = $curl->getInfo();
        $timeToFirstByte = $curlInfo['starttransfer_time'] - $curlInfo['pretransfer_time'];
        $inMilliseconds = (int) round($timeToFirstByte * 1000);

        $body = substr($curl->response, 0, $this->bodyLength->asInteger());

        $validResult = new ValidResult(
            new Url($curl->url),
            $this->convertToHeaderCollection($curl->responseHeaders),
            new Body($body),
            new TimeToFirstByte($inMilliseconds),
            new StatusCode($curl->httpStatusCode)
        );

        call_user_func($this->successCallback, $validResult);

        $this->results->addResult($validResult);
    }

    /**
     * @param Curl $curl
     */
    public function onError(Curl $curl)
    {
        $url = new Url($curl->url);

        $errorMessage = new ErrorMessage(
            sprintf(
                'Curl Code: %s Error: %s',
                $curl->errorCode,
                $curl->errorMessage
            )
        );

        $errorResult = new ErrorResult(
            $url,
            $this->convertToHeaderCollection($curl->responseHeaders),
            $errorMessage
        );

        call_user_func($this->errorCallback, $errorResult);

        $this->results->addResult($errorResult);
    }

    /**
     * @param CaseInsensitiveArray $headers
     *
     * @return HeaderCollection
     */
    private function convertToHeaderCollection(CaseInsensitiveArray $headers)
    {
        $headerCollection = new HeaderCollection();
        foreach ($headers as $key => $value) {
            $header = new Header(new HeaderKey($key), new HeaderValue($value));
            $headerCollection->addHeader($header);
        }

        return $headerCollection;
    }
}
