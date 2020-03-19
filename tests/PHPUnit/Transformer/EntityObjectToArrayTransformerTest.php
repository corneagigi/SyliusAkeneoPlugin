<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusAkeneoPlugin\PHPUnit\Transformer;

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Synolia\SyliusAkeneoPlugin\Entity\ApiConfiguration;
use Synolia\SyliusAkeneoPlugin\Entity\ApiConfigurationWebsiteMapping;
use Synolia\SyliusAkeneoPlugin\Transformer\EntityObjectToArrayTransformer;

final class EntityObjectToArrayTransformerTest extends KernelTestCase
{
    /** @var EntityObjectToArrayTransformer */
    private $entityObjectToArrayTransformer;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->entityObjectToArrayTransformer = self::$container->get(EntityObjectToArrayTransformer::class);
        self::assertInstanceOf(EntityObjectToArrayTransformer::class, $this->entityObjectToArrayTransformer);
    }

    public function testApiConfigurationObjectToArray(): void
    {
        $apiConfiguration = new ApiConfiguration();
        $apiConfiguration->addWebsiteMapping(new ApiConfigurationWebsiteMapping());

        $result = $this->entityObjectToArrayTransformer->entityObjectToArray($apiConfiguration);

        $expected = [
            'base_url' => null,
            'api_client_id' => null,
            'api_client_secret' => null,
            'enterprise' => null,
            'token' => null,
            'refresh_token' => null,
            'pagination_size' => null,
            'username' => null,
            'password' => null,
            'website_mappings' => [
                [
                  'channel' => null,
                  'akeneo_channel' => null,
                ],
            ],
        ];

        Assert::assertEquals($expected, $result);
    }
}