<?php declare(strict_types=1);

namespace Yokai\Batch\Bridge\Symfony\Framework\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterJobsCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container): void
    {
        $jobs = [];
        foreach ($container->findTaggedServiceIds('yokai_batch.job') as $serviceId => $tags) {
            foreach ($tags as $attributes) {
                $jobs[$attributes['job'] ?? $serviceId] = new Reference($serviceId);
            }
        }

        $container->getDefinition('yokai_batch.job_registry')
            ->setArgument('$jobs', ServiceLocatorTagPass::register($container, $jobs));
    }
}
