<?php

declare(strict_types=1);

namespace Synolia\SyliusAkeneoPlugin\Factory;

use League\Pipeline\Pipeline;
use League\Pipeline\PipelineInterface;
use Synolia\SyliusAkeneoPlugin\Pipeline\Processor;
use Synolia\SyliusAkeneoPlugin\Task\Association\CreateProductAssociationTask;
use Synolia\SyliusAkeneoPlugin\Task\Product\CreateConfigurableProductEntitiesTask;
use Synolia\SyliusAkeneoPlugin\Task\Product\CreateSimpleProductEntitiesTask;
use Synolia\SyliusAkeneoPlugin\Task\Product\EnableDisableProductsTask;
use Synolia\SyliusAkeneoPlugin\Task\Product\RetrieveProductsTask;
use Synolia\SyliusAkeneoPlugin\Task\Product\SetupProductTask;
use Synolia\SyliusAkeneoPlugin\Task\Product\TearDownProductTask;

final class ProductPipelineFactory extends AbstractPipelineFactory
{
    public function create(): PipelineInterface
    {
        $pipeline = new Pipeline(new Processor($this->dispatcher));

        return $pipeline
            ->pipe($this->taskProvider->get(SetupProductTask::class))
            ->pipe($this->taskProvider->get(RetrieveProductsTask::class))
            ->pipe($this->taskProvider->get(CreateSimpleProductEntitiesTask::class))
            ->pipe($this->taskProvider->get(EnableDisableProductsTask::class))
            ->pipe($this->taskProvider->get(CreateConfigurableProductEntitiesTask::class))
            ->pipe($this->taskProvider->get(CreateProductAssociationTask::class))
            ->pipe($this->taskProvider->get(TearDownProductTask::class))
        ;
    }
}
