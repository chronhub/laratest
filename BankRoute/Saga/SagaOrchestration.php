<?php

declare(strict_types=1);

namespace BankRoute\Saga;

use Exception;
use function array_reverse;

final readonly class SagaOrchestration
{
    public function __construct(private array $steps)
    {
    }

    public function executeSaga(): void
    {
        $compensationSteps = [];

        try {
            foreach ($this->steps as $step) {
                $step->execute();
                $compensationSteps[] = $step;
            }
        } catch (Exception $e) {
            $this->compensateSaga($compensationSteps);

            throw $e;
        }
    }

    public function compensateSaga(array $compensationSteps): void
    {
        $reversedSteps = array_reverse($compensationSteps);
        foreach ($reversedSteps as $step) {
            $step->compensate();
        }
    }
}
