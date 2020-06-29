<?php

declare(strict_types=1);

namespace Yokai\Batch\Bridge\Symfony\Framework\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader as ConfigLoader;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader as DependencyInjectionLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Yokai\Batch\Bridge\Doctrine\DBAL\DoctrineDBALJobExecutionStorage;
use Yokai\Batch\Bridge\Symfony\Serializer\SerializerJobExecutionSerializer;
use Yokai\Batch\Launcher\JobLauncherInterface;
use Yokai\Batch\Storage\FilesystemJobExecutionStorage;
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

        $this->configureStorage($container, $config['storage']);

        $launcher = 'yokai_batch.job_launcher.simple';
        if (class_exists(Application::class)) {
            $launcher = 'yokai_batch.job_launcher.run_command';
        }
        $container->setAlias(JobLauncherInterface::class, $launcher);
    }

    private function getLoader(ContainerBuilder $container): ConfigLoader\LoaderInterface
    {
        $locator = new FileLocator(__DIR__ . '/../Resources/services');
        $resolver = new ConfigLoader\LoaderResolver(
            [
                new DependencyInjectionLoader\XmlFileLoader($container, $locator),
                new DependencyInjectionLoader\DirectoryLoader($container, $locator),
            ]
        );

        return new ConfigLoader\DelegatingLoader($resolver);
    }

    private function configureStorage(ContainerBuilder $container, array $config): void
    {
        $defaultStorage = null;

        if (isset($config['dbal'])) {
            $container
                ->register('yokai_batch.storage.dbal', DoctrineDBALJobExecutionStorage::class)
                ->setArguments(
                    [
                        new Reference("doctrine.dbal.{$config['dbal']['connection']}_connection"),
                        $config['dbal']['options'],
                    ]
                )
            ;

            $defaultStorage = 'yokai_batch.storage.dbal';
        }

        if (isset($config['filesystem'])) {
            $serializer = $config['filesystem']['serializer'];
            $format = $serializer['format'];

            if (isset($serializer['service'])) {
                $serializerId = $serializer['service'];
            } elseif (isset($serializer['symfony'])) {
                if (!interface_exists(SerializerInterface::class)) {
                    throw new \LogicException(); //todo
                }

                $serializerId = 'yokai_batch.job_execution_serializer.filesystem_storage';
                $container
                    ->register($serializerId, SerializerJobExecutionSerializer::class)
                    ->setArguments(
                        [
                            new Reference(SerializerInterface::class),
                            $format,
                            $serializer['symfony']['context']['common'],
                            $serializer['symfony']['context']['serialize'],
                            $serializer['symfony']['context']['deserialize'],
                        ]
                    )
                ;
            } else {
                throw new \LogicException(); //todo
            }

            $container
                ->register('yokai_batch.storage.filesystem', FilesystemJobExecutionStorage::class)
                ->setArguments(
                    [
                        new Reference($serializerId),
                        $config['filesystem']['dir'],
                        $format,
                    ]
                )
            ;

            $defaultStorage = $defaultStorage ?: 'yokai_batch.storage.filesystem';
        }

        if ($defaultStorage === null) {
            throw new \LogicException();//todo
        }

        $container
            ->setAlias(JobExecutionStorageInterface::class, $defaultStorage)
            ->setPublic(true)
        ;
    }
}
