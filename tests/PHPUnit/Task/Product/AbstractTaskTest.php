<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusAkeneoPlugin\PHPUnit\Task\Product;

use Akeneo\Pim\ApiClient\Api\AttributeApi;
use Akeneo\Pim\ApiClient\Api\AttributeOptionApi;
use Akeneo\Pim\ApiClient\Api\CategoryApi;
use Akeneo\Pim\ApiClient\Api\FamilyApi;
use Akeneo\Pim\ApiClient\Api\FamilyVariantApi;
use Akeneo\Pim\ApiClient\Api\ProductApi;
use Akeneo\Pim\ApiClient\Api\ProductMediaFileApi;
use Akeneo\Pim\ApiClient\Api\ProductModelApi;
use donatj\MockWebServer\Response;
use donatj\MockWebServer\ResponseStack;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Synolia\SyliusAkeneoPlugin\Entity\ApiConfiguration;
use Synolia\SyliusAkeneoPlugin\Entity\ProductConfiguration;
use Synolia\SyliusAkeneoPlugin\Entity\ProductConfigurationAkeneoImageAttribute;
use Synolia\SyliusAkeneoPlugin\Entity\ProductConfigurationImageMapping;
use Tests\Synolia\SyliusAkeneoPlugin\PHPUnit\Api\ApiTestCase;

abstract class AbstractTaskTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->manager = self::$container->get('doctrine')->getManager();

        $this->initializeApiConfiguration();

        $this->manager->flush();

        $this->server->setResponseOfPath(
            '/' . sprintf(CategoryApi::CATEGORIES_URI),

            new ResponseStack(
                new Response($this->getFileContent('categories_all.json'), [], HttpResponse::HTTP_OK)
            )
        );

        $this->server->setResponseOfPath(
            '/' . sprintf(AttributeApi::ATTRIBUTES_URI),
            new ResponseStack(
                new Response($this->getFileContent('attributes_options_apollon.json'), [], HttpResponse::HTTP_OK)
            )
        );

        $this->server->setResponseOfPath(
            '/' . FamilyApi::FAMILIES_URI,
            new ResponseStack(
                new Response($this->getFileContent('families.json'), [], HttpResponse::HTTP_OK)
            )
        );

        $this->server->setResponseOfPath(
            '/' . sprintf(FamilyVariantApi::FAMILY_VARIANTS_URI, 'clothing'),
            new ResponseStack(
                new Response($this->getFileContent('family_clothing_variants.json'), [], HttpResponse::HTTP_OK)
            )
        );

        $this->server->setResponseOfPath(
            '/' . sprintf(AttributeOptionApi::ATTRIBUTE_OPTIONS_URI, 'clothing_size'),
            new ResponseStack(
                new Response($this->getFileContent('attribute_options_clothing_size.json'), [], HttpResponse::HTTP_OK)
            )
        );

        $this->server->setResponseOfPath(
            '/' . sprintf(AttributeOptionApi::ATTRIBUTE_OPTIONS_URI, 'collection'),
            new ResponseStack(
                new Response($this->getFileContent('attribute_options_collection.json'), [], HttpResponse::HTTP_OK)
            )
        );

        $this->server->setResponseOfPath(
            '/' . sprintf(AttributeOptionApi::ATTRIBUTE_OPTIONS_URI, 'color'),
            new ResponseStack(
                new Response($this->getFileContent('attribute_options_color.json'), [], HttpResponse::HTTP_OK)
            )
        );

        $this->server->setResponseOfPath(
            '/' . sprintf(AttributeOptionApi::ATTRIBUTE_OPTIONS_URI, 'size'),
            new ResponseStack(
                new Response($this->getFileContent('attribute_options_size.json'), [], HttpResponse::HTTP_OK)
            )
        );

        $this->server->setResponseOfPath(
            '/' . sprintf(FamilyVariantApi::FAMILY_VARIANT_URI, 'clothing', 'clothing_color_size'),
            new ResponseStack(
                new Response($this->getFileContent('family_variant_clothing_color_size.json'), [], HttpResponse::HTTP_OK)
            )
        );
        $this->server->setResponseOfPath(
            '/' . sprintf(ProductModelApi::PRODUCT_MODELS_URI),
            new ResponseStack(
                new Response($this->getFileContent('product_models_apollon.json'), [], HttpResponse::HTTP_OK)
            )
        );

        $this->server->setResponseOfPath(
            '/' . sprintf(ProductApi::PRODUCTS_URI),
            new ResponseStack(
                new Response($this->getFileContent('products_all.json'), [], HttpResponse::HTTP_OK)
            )
        );

        $this->server->setResponseOfPath(
            '/' . sprintf(ProductMediaFileApi::MEDIA_FILE_DOWNLOAD_URI, '6/3/5/c/635cbfe306a1c13867fe7671c110ee3333fcba13_bag.jpg'),
            new ResponseStack(
                new Response($this->getFileContent('product_1111111171.jpg'), [], HttpResponse::HTTP_OK)
            )
        );
    }

    protected function tearDown(): void
    {
        $this->manager->close();
        $this->manager = null;

        $this->server->stop();

        parent::tearDown();
    }

    protected function createConfiguration(): void
    {
        $apiConfiguration = new ApiConfiguration();
        $apiConfiguration->setBaseUrl('');
        $apiConfiguration->setApiClientId('');
        $apiConfiguration->setApiClientSecret('');
        $apiConfiguration->setPaginationSize(100);
        $apiConfiguration->setIsEnterprise(true);
        $apiConfiguration->setUsername('');
        $apiConfiguration->setPassword('');
        $this->manager->persist($apiConfiguration);
        $this->manager->flush();
    }

    protected function createProductConfiguration(): void
    {
        $productConfiguration = new ProductConfiguration();
        $productConfiguration
            ->setAkeneoPriceAttribute('price')
            ->setAkeneoEnabledChannelsAttribute('enabled_channels')
        ;
        $this->manager->persist($productConfiguration);

        $imageMapping = new ProductConfigurationImageMapping();
        $imageMapping->setAkeneoAttribute('picture');
        $imageMapping->setSyliusAttribute('main');
        $imageMapping->setProductConfiguration($productConfiguration);
        $this->manager->persist($imageMapping);
        $productConfiguration->addProductImagesMapping($imageMapping);

        $imageAttributes = ['picture', 'image'];

        foreach ($imageAttributes as $imageAttribute) {
            $akeneoImageAttribute = new ProductConfigurationAkeneoImageAttribute();
            $akeneoImageAttribute->setAkeneoAttributes($imageAttribute);
            $akeneoImageAttribute->setProductConfiguration($productConfiguration);
            $this->manager->persist($akeneoImageAttribute);
            $productConfiguration->addAkeneoImageAttribute($akeneoImageAttribute);
        }

        $this->manager->flush();
    }
}