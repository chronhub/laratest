<?php

declare(strict_types=1);

namespace App\Testing;

use Chronhub\Storm\Reporter\OnFinalizePriority;
use Chronhub\Storm\Contracts\Tracker\MessageStory;
use Chronhub\Storm\Reporter\DetachMessageListener;
use Illuminate\Queue\MaxAttemptsExceededException;
use Chronhub\Storm\Contracts\Tracker\MessageTracker;
use Chronhub\Storm\Contracts\Tracker\MessageSubscriber;
use Chronhub\Storm\Chronicler\Exceptions\ConcurrencyException;

final class StopPropagationOnTimeoutSubscriber implements MessageSubscriber
{
    use DetachMessageListener;

    public function attachToReporter(MessageTracker $tracker): void
    {
        $this->messageListeners[] = $tracker->onFinalize(function (MessageStory $story): void {
            $exception = $story->exception();

            if ($exception instanceof MaxAttemptsExceededException || $exception instanceof ConcurrencyException) {
                $story->stop(true);
                $story->resetException();
            }
        }, OnFinalizePriority::FINALIZE_TRANSACTION->value - 100);
    }
}
