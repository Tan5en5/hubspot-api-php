<?php

namespace HubSpot;

use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

class RetryMiddlewareFactory {
    
    public static function createInternalErrorMiddleware(int $secondsDelay = 10, int $maxRetries = 5)
    {
        return static::createMiddlewareByHttpCode(500, $secondsDelay, $maxRetries);
    }
    
    public static function createRateLimitMiddleware(int $secondsDelay = 10, int $maxRetries = 5)
    {
        return static::createMiddlewareByHttpCode(429, $secondsDelay, $maxRetries);
    }
    
    public static function createMiddlewareByHttpCode(int $code, int $secondsDelay = 10, int $maxRetries = 5)
    {
        return Middleware::retry(static::getRetryFunction($code, $maxRetries), static::getDelayFunction($secondsDelay));
    }
    
    public static function getRetryFunction(int $code, int $maxRetries = 5)
    {
        return function (
            $retries,
            Request $request,
            Response $response = null,
            RequestException $exception = null
        ) use ($code, $maxRetries) {
            if ($retries >= $maxRetries) {
                return false;
            }

            if(($response instanceof Response) && ($response->getStatusCode() == $code)) {
                return true;
            }

            return false;
        };
    }
    
    public static function getDelayFunction(int $secondsDelay)
    {
        return function($retries) use ($secondsDelay) {
            return 1000 * $secondsDelay;
        };
    }
}