<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusAkeneoPlugin\PHPUnit\Task\Product;

use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductVariant;
use Sylius\Component\Core\Model\Taxon;
use Sylius\Component\Core\Model\TaxonInterface;
use Synolia\SyliusAkeneoPlugin\Payload\Attribute\AttributePayload;
use Synolia\SyliusAkeneoPlugin\Payload\Product\ProductPayload;
use Synolia\SyliusAkeneoPlugin\Provider\AkeneoTaskProvider;
use Synolia\SyliusAkeneoPlugin\Task\Attribute\CreateUpdateEntityTask;
use Synolia\SyliusAkeneoPlugin\Task\Attribute\RetrieveAttributesTask;
use Synolia\SyliusAkeneoPlugin\Task\Product\CreateSimpleProductEntitiesTask;
use Synolia\SyliusAkeneoPlugin\Task\Product\RetrieveProductsTask;

final class CreateSimpleProductEntitiesTaskTest extends AbstractTaskTest
{
    /** @var AkeneoTaskProvider */
    private $taskProvider;

    /** @var \Akeneo\Pim\ApiClient\AkeneoPimClientInterface */
    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->taskProvider = self::$container->get(AkeneoTaskProvider::class);
        $this->client = $this->createClient();
        self::assertInstanceOf(AkeneoTaskProvider::class, $this->taskProvider);
    }

    public function testRetrieveProductsTask(): void
    {
        $productPayload = new ProductPayload($this->client);

        /** @var RetrieveProductsTask $retrieveProductsTask */
        $retrieveProductsTask = $this->taskProvider->get(RetrieveProductsTask::class);
        $productPayload = $retrieveProductsTask->__invoke($productPayload);
        $this->assertInstanceOf(ProductPayload::class, $productPayload);
    }

    public function testCreateSimpleProductsTask(): void
    {
        $this->createProductConfiguration();
        $this->importCategories();
        $this->importAttributes();

        $productPayload = new ProductPayload($this->client);

        /** @var RetrieveProductsTask $retrieveProductsTask */
        $retrieveProductsTask = $this->taskProvider->get(RetrieveProductsTask::class);
        /** @var ProductPayload $productPayload */
        $productPayload = $retrieveProductsTask->__invoke($productPayload);

        $this->assertCount(1, $productPayload->getSimpleProductPayload()->getProducts());

        /** @var CreateSimpleProductEntitiesTask $createSimpleProductEntitiesTask */
        $createSimpleProductEntitiesTask = $this->taskProvider->get(CreateSimpleProductEntitiesTask::class);
        $createSimpleProductEntitiesTask->__invoke($productPayload);

        /** @var \Sylius\Component\Core\Model\ProductInterface $product */
        $product = $this->manager->getRepository(Product::class)->findOneBy(['code' => '1111111171']);
        $this->assertNotNull($product);

        //Testing product attribute translations inside models
        $product->setCurrentLocale('en_US');
        $this->assertEquals('Bag', $product->getName());

        //Testing product attribute translations
        foreach ($product->getAttributes() as $attribute) {
            if ('ean' === $attribute->getCode()) {
                $this->assertEquals('1234567890183', $attribute->getValue());
            }
        }

        //Testing image import
        $this->assertCount(1, $product->getImages());

        //Testing categories
        $categories = ['master_accessories_bags', 'print_accessories', 'supplier_zaro'];
        $this->assertCount(\count($categories), $product->getTaxons());
        foreach ($product->getTaxons() as $taxon) {
            $this->assertContains($taxon->getCode(), $categories);
        }

        //Testing simple variant
        /** @var \Sylius\Component\Core\Model\ProductVariantInterface $productVariant */
        $productVariant = $this->manager->getRepository(ProductVariant::class)->findOneBy(['code' => $product->getCode()]);
        $this->assertNotNull($productVariant);

        $this->assertEquals(1, $productVariant->getChannelPricings()->count());
        foreach ($productVariant->getChannelPricings() as $channelPricing) {
            $this->assertEquals(89900, $channelPricing->getPrice());
            $this->assertEquals(89900, $channelPricing->getOriginalPrice());
        }
    }

    private function importAttributes(): void
    {
        $initialPayload = new AttributePayload($this->client);
        /** @var RetrieveAttributesTask $retrieveTask */
        $retrieveTask = $this->taskProvider->get(RetrieveAttributesTask::class);
        $payload = $retrieveTask->__invoke($initialPayload);

        /** @var CreateUpdateEntityTask $task */
        $task = $this->taskProvider->get(CreateUpdateEntityTask::class);
        $task->__invoke($payload);
    }

    private function importCategories(): void
    {
        $categories = ['master_accessories_bags', 'print_accessories', 'supplier_zaro'];

        foreach ($categories as $categoryCode) {
            $category = $this->manager->getRepository(Taxon::class)->findOneBy(['code' => $categoryCode]);

            if (!$category instanceof TaxonInterface) {
                $category = new Taxon();
                $this->manager->persist($category);
            }
            $category->setCurrentLocale('en_US');
            $category->setCode($categoryCode);
            $category->setSlug($categoryCode);
            $category->setName($categoryCode);
        }
        $this->manager->flush();
    }
}