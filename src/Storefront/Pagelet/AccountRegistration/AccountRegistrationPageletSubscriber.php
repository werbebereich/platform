<?php declare(strict_types=1);

namespace Shopware\Storefront\Pagelet\AccountRegistration;

use Shopware\Storefront\Event\AccountEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AccountRegistrationPageletSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            AccountEvents::ACCOUNTREGISTRATION_PAGELET_REQUEST => 'transformRequest',
        ];
    }

    public function transformRequest(AccountRegistrationPageletRequestEvent $event): void
    {
        //$registrationPageletRequest = $event->getRegistrationPageletRequest();
    }
}