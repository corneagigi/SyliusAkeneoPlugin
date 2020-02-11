<?php

declare(strict_types=1);

namespace Synolia\SyliusAkeneoPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Synolia\SyliusAkeneoPlugin\DependencyInjection\Compiler\AkeneoTaskCompilerPass;
use Synolia\SyliusAkeneoPlugin\Task\AkeneoTaskInterface;

final class SynoliaSyliusAkeneoPlugin extends Bundle
{
    use SyliusPluginTrait;

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container
            ->registerForAutoconfiguration(AkeneoTaskInterface::class)
            ->addTag(AkeneoTaskInterface::TAG_ID);
        $container->addCompilerPass(new AkeneoTaskCompilerPass());
    }
}
