<?php

declare(strict_types=1);

namespace Setono\SyliusKlaviyoPlugin\Client;

use Setono\SyliusKlaviyoPlugin\DTO\Event;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\Assert;
use Psr\Log\LoggerInterface;


final class TrackIdentifyClient implements TrackIdentifyClientInterface
{
    private RestClientInterface $httpClient;

    private SerializerInterface $serializer;

    private LoggerInterface $logger;

    public function __construct(
        RestClientInterface $httpClient,
        SerializerInterface $serializer,
        LoggerInterface $logger,
    ) {
        $this->httpClient = $httpClient;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    public function trackEvent(Event $event): void
    {
        $json = $this->serializer->serialize($event, 'json', [
            'groups' => 'setono:sylius-klaviyo:event',
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
        ]);

        $response = $this->httpClient->post('events', [
            'data' => json_decode($json, true),
        ]);

        if ($response->getStatusCode() !== 202) {
            $this->logger->error('Unexpected response from Klaviyo', [
                'status_code' => $response->getStatusCode(),
                'response_body' => $response->getContent(false),
            ]);
        }

        Assert::same($response->getStatusCode(), 202, '[Klaviyo] Unexpected response : ' . $response->getContent(false));
    }
}
