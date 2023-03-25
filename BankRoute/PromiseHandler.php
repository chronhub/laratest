<?php

declare(strict_types=1);

namespace BankRoute;

use React\Promise\PromiseInterface;

trait PromiseHandler
{
    public function handlePromise(PromiseInterface $promise, bool $raisedException = true): mixed
    {
        $result = null;
        $exception = null;

        $promise->then(
            function ($value) use (&$result) {
                $result = $value;
            },
            function ($reason) use (&$result, &$exception) {
                $exception = $reason;
            }
        );

        if ($raisedException && $exception !== null) {
            throw $exception;
        }

        return $result;
    }
}
