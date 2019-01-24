<?php declare(strict_types=1);

namespace Yokai\Batch\Bridge\Symfony\Framework\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader as ConfigLoader;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader as DependencyInjectionLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Yokai\Batch\Launcher\JobLauncherInterface;
use Yokai\Batch\Serializer\JobExecutionSerializerInterface;
use Yokai\Batch\Storage\JobExecutionStorageInterface;

final class YokaiBatchExtension extends Extension
{
    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = $this->getLoader($container);
        $loader->load('global/');
        $this->setParameters($container, $config);
        $this->loadBridges($loader, $container);
        $this->configureAutowiring($container, $config);
    }

    private function getLoader(ContainerBuilder $container): ConfigLoader\LoaderInterface
    {
        $locator = new FileLocator(__DIR__.'/../Resources/services');
        $resolver = new ConfigLoader\LoaderResolver(
            [
                new DependencyInjectionLoader\XmlFileLoader($container, $locator),
                new DependencyInjectionLoader\DirectoryLoader($container, $locator),
            ]
        );

        return new ConfigLoader\DelegatingLoader($resolver);
    }

    private function setParameters(ContainerBuilder $container, array $config): void
    {
        //todo $parameters should be constructed with $config
        $parameters = [
            'storage.filesystem.dir' => implode(
                DIRECTORY_SEPARATOR,
                [$container->getParameter('kernel.project_dir'), 'var', 'batch']
            ),
            'job_execution.serialize_format' => 'json',
            'console.output_log_filename' => 'batch_execute.log',
            'job_execution_serializer.symfony_serializer.common_context' => [],
            'job_execution_serializer.symfony_serializer.serialize_context' => [],
            'job_execution_serializer.symfony_serializer.deserialize_context' => [],
        ];

        foreach ($parameters as $parameterName => $parameterValue) {
            $container->setParameter('yokai_batch.'.$parameterName, $parameterValue);
        }
    }

    private function loadBridges(ConfigLoader\LoaderInterface $loader, ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');

        $bridges = [
            'doctrine/orm/' => isset($bundles['DoctrineBundle']),
            'doctrine/mongodb/' => isset($bundles['DoctrineMongoDBBundle']),
            'symfony/console/' => class_exists(Application::class),
            'symfony/serializer/' => interface_exists(SerializerInterface::class),
            'symfony/validator/' => interface_exists(ValidatorInterface::class),
        ];

        foreach (array_keys(array_filter($bridges)) as $resource) {
            $loader->load($resource);
        }
    }

    private function configureAutowiring(ContainerBuilder $container, array $config): void
    {
        //todo $rules should be constructed with $config
        $rules = [
            JobLauncherInterface::class => 'yokai_batch.job_launcher.simple',
            JobExecutionSerializerInterface::class => 'yokai_batch.job_execution_serializer.symfony_serializer',
            JobExecutionStorageInterface::class => 'yokai_batch.storage.filesystem',
        ];

        foreach ($rules as $alias => $id) {
            $container->setAlias($alias, $id);
        }
    }
}
