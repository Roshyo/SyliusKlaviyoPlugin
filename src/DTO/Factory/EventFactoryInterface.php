<?php

declare(strict_types=1);

namespace Setono\SyliusKlaviyoPlugin\DTO\Factory;

use Setono\SyliusKlaviyoPlugin\DTO\Event;
use Setono\SyliusKlaviyoPlugin\DTO\Properties;

interface EventFactoryInterface
{
    public function create(Properties $properties): Event;
}