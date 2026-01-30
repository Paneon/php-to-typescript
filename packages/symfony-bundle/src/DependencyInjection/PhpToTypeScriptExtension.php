<?php declare(strict_types=1);

namespace Paneon\PhpToTypeScriptBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class PhpToTypeScriptExtension extends Extension
{
    public function getAlias(): string
    {
        // Must match the root key used in config/packages/php_to_typescript.yaml
        return 'php_to_typescript';
    }

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('type_script_generator.indentation', $config['indentation']);
        $container->setParameter('type_script_generator.prefix', $config['prefix']);
        $container->setParameter('type_script_generator.suffix', $config['suffix']);
        $container->setParameter('type_script_generator.interfaces', $config['interfaces']);
        $container->setParameter('type_script_generator.directories', $config['directories']);
        $container->setParameter('type_script_generator.inputDirectory', $config['inputDirectory']);
        $container->setParameter('type_script_generator.outputDirectory', $config['outputDirectory']);
        $container->setParameter('type_script_generator.nullable', $config['nullable']);
        $container->setParameter('type_script_generator.useType', $config['useType']);
        $container->setParameter('type_script_generator.export', $config['export']);
        $container->setParameter('type_script_generator.useEnumUnionType', $config['useEnumUnionType']);
        $container->setParameter('type_script_generator.singleFileMode', $config['singleFileMode']);
        $container->setParameter('type_script_generator.singleFileOutput', $config['singleFileOutput']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }
}
