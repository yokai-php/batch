<?php

declare(strict_types=1);

namespace Yokai\Batch\Bridge\Symfony\Framework\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader as ConfigLoader;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
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
use Yokai\Batch\Storage\ListableJobExecutionStorageInterface;
use Yokai\Batch\Storage\QueryableJobExecutionStorageInterface;

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
        if (isset($config['service'])) {
            $defaultStorage = $config['service'];
        } elseif (isset($config['dbal'])) {
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
        } elseif (isset($config['filesystem'])) {
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

            $defaultStorage = 'yokai_batch.storage.filesystem';
        } else {
            throw new \LogicException();//todo
        }

        try {
            $defaultStorageDefinition = $container->getDefinition($defaultStorage);
        } catch (ServiceNotFoundException $exception) {
            throw new \LogicException(
                sprintf('Configured default job execution storage service "%s" does not exists.', $defaultStorage),
                0,
                $exception
            );
        }

        $interfaces = [
            JobExecutionStorageInterface::class => true,
            ListableJobExecutionStorageInterface::class => false,
            QueryableJobExecutionStorageInterface::class => false,
        ];
        $defaultStorageClass = $defaultStorageDefinition->getClass();
        foreach ($interfaces as $interface => $required) {
            if (!is_a($defaultStorageClass, $interface, true)) {
                if ($required) {
                    throw new \LogicException();//todo
                }
                continue;
            }
            $container
                ->setAlias($interface, $defaultStorage)
                ->setPublic(true)
            ;
        }
    }
}
