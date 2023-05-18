<?php

declare(strict_types=1);

namespace BankRoute\ProcessManager;

use App\Models\UserRepository;
use Chronhub\Storm\Reporter\DomainEvent;
use Chronhub\Storm\Reporter\ReportEvent;
use Chronhub\Storm\Reporter\ReportCommand;
use App\Report\Customer\Signup\AuthUserCreated;
use App\Report\Customer\Signup\RegisterCustomer;
use App\Report\Customer\Signup\CustomerSignupStarted;
use BankRoute\Model\Customer\Event\CustomerRegistered;
use App\Report\Customer\Signup\CustomerSignupCompleted;

readonly class CustomerRegistrationProcess
{
    final public const CUSTOMER_REGISTRATION = 'pm-customer-registration';

    public function __construct(
        private UserRepository $userRepository,
        private RedisProcessManager $processManager,
        private ReportCommand $reportCommand,
        private ReportEvent $reportEvent
    ) {
    }

    public function onEvent(DomainEvent $event): void
    {
        $process = $this->processEvents();

        $process[$event::class]($event);
    }

    protected function processEvents(): array
    {
        return [
            CustomerSignupStarted::class => function (CustomerSignupStarted $event) {
                $userId = $event->content['id'];

                $this->processManager->start(self::CUSTOMER_REGISTRATION, $userId, CustomerSignupStarted::class);

                $this->processManager->next(self::CUSTOMER_REGISTRATION, $userId, CustomerRegistered::class, [
                    $event->toContent(),
                ]);

                $this->reportCommand->relay(RegisterCustomer::fromContent([
                    'customer_id' => $userId,
                    'customer_email' => $event->content['email'],
                    'customer_name' => $event->content['name'],
                ]));
            },

            CustomerRegistered::class => function (CustomerRegistered $event) {
                $customerId = $event->content['customer_id'];

                $lastEvent = $this->processManager->expect(self::CUSTOMER_REGISTRATION, $customerId);

                if ($lastEvent !== $event::class) {
                    return;
                }

                $process = $this->processManager->current(self::CUSTOMER_REGISTRATION, $customerId);

                $this->processManager->next(self::CUSTOMER_REGISTRATION, $customerId, AuthUserCreated::class);

                $this->reportEvent->relay(AuthUserCreated::fromContent($process['extra'][0]));
            },

            AuthUserCreated::class => function (AuthUserCreated $event) {
                $authUserId = $event->content['id'];

                $lastEvent = $this->processManager->expect(self::CUSTOMER_REGISTRATION, $authUserId);

                if ($lastEvent !== $event::class) {
                    return;
                }

                $this->userRepository->register($event->content);

                $this->reportEvent->relay(CustomerSignupCompleted::fromContent([
                    'id' => $authUserId,
                    'email' => $event->content['email'],
                    'name' => $event->content['name'],
                ]));

                $this->processManager->complete(self::CUSTOMER_REGISTRATION, $authUserId);
            },
        ];
    }
}
