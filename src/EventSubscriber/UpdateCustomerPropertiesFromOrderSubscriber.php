<?php

declare(strict_types=1);

namespace Setono\SyliusKlaviyoPlugin\EventSubscriber;

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Setono\SyliusKlaviyoPlugin\DTO\Properties\CustomerProperties;
use Setono\SyliusKlaviyoPlugin\DTO\Properties\Factory\PropertiesFactoryInterface;
use Setono\SyliusKlaviyoPlugin\DTO\Properties\Location;
use Setono\SyliusKlaviyoPlugin\Event\PropertiesArePopulatedEvent;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class UpdateCustomerPropertiesFromOrderSubscriber implements EventSubscriberInterface
{
    private PropertiesFactoryInterface $propertiesFactory;

    public function __construct(
        PropertiesFactoryInterface $propertiesFactory,
    ) {
        $this->propertiesFactory = $propertiesFactory;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PropertiesArePopulatedEvent::class => 'update',
        ];
    }

    public function update(PropertiesArePopulatedEvent $event): void
    {
        if (!isset($event->context['order']) || !$event->context['order'] instanceof OrderInterface) {
            return;
        }

        $this->populate($event->context['order'], $event->event->customerProperties);
    }

    private function populate(OrderInterface $order, CustomerProperties $customerProperties): void
    {
        $address = $order->getBillingAddress();
        if (null === $address) {
            return;
        }

        $customerProperties->firstName = $address->getFirstName();
        $customerProperties->lastName = $address->getLastName();
        if (null === $address->getPhoneNumber()) {
            $customerProperties->phoneNumber = null;
        } else {
            try {
                $customerProperties->phoneNumber = (string) PhoneNumber::parse($address->getPhoneNumber(), $address->getCountryCode());
            } catch (PhoneNumberParseException) {
                $customerProperties->phoneNumber = null;
            }
        }
        $customerProperties->location = $this->propertiesFactory->create(Location::class, $address);
    }
}
