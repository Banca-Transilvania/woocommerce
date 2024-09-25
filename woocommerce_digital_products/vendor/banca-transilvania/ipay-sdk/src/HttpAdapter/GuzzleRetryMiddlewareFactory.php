<?php

namespace BTransilvania\Api\HttpAdapter;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class GuzzleRetryMiddlewareFactory
{
    /**
     * Maximum number of retries.
     */
    public const MAX_RETRIES = 5;

    /**
     * Delay increase per retry in milliseconds.
     */
    public const DELAY_INCREASE_MS = 1000;

    /**
     * Creates a retry middleware.
     *
     * @param bool $delay Indicates if delay should be applied between retries.
     * @return callable The middleware capable of retrying requests based on certain conditions.
     */
    public function retry(bool $delay = true): callable
    {
        return Middleware::retry(
            $this->decideRetry(),
            $this->calculateDelay($delay)
        );
    }

    /**
     * Calculates the delay before the next retry.
     *
     * @param bool $applyDelay Whether to apply the default delay or not.
     * @return callable Returns a function that calculates the delay.
     */
    private function calculateDelay(bool $applyDelay): callable
    {
        return function ($retryCount) use ($applyDelay) {
            return $applyDelay ? static::DELAY_INCREASE_MS * $retryCount : 0;
        };
    }

    /**
     * Decides whether to retry the request.
     *
     * @return callable The decision logic to retry a request.
     */
    private function decideRetry()
    {
        return function (
            $retries,
            Request $request,
            ?Response $response = null,
            ?TransferException $exception = null
        ) {
            if ($retries >= static::MAX_RETRIES) {
                return false;
            }

            if ($exception instanceof ConnectException) {
                return true;
            }

            return false;
        };
    }
}
