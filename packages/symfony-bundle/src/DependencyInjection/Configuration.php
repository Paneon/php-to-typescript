<?php declare(strict_types=1);

namespace Paneon\PhpToTypeScriptBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        // The configuration key (a.k.a. extension alias) must match what users put in
        // config/packages/php_to_typescript.yaml.
        $treeBuilder = new TreeBuilder('php_to_typescript');
        $rootNode = $treeBuilder->getRootNode();

        $this->addGeneralConfiguration($rootNode);
        $this->addFileConfiguration($rootNode);
        $this->addDirectoryConfiguration($rootNode);

        return $treeBuilder;
    }

    public function addGeneralConfiguration($builder): void
    {
        $builder
            ->children()
                ->integerNode('indentation')->defaultValue(2)->end()
                ->scalarNode('inputDirectory')->defaultValue('src/')->end()
                ->scalarNode('outputDirectory')->defaultValue('assets/js/interfaces/')->end()
                ->scalarNode('prefix')->defaultValue('')->end()
                ->scalarNode('suffix')->defaultValue('')->end()
                ->booleanNode('nullable')->defaultValue(false)->end()
                ->booleanNode('useType')->defaultValue(false)->end()
                ->booleanNode('export')->defaultValue(false)->end()
                ->booleanNode('useEnumUnionType')->defaultValue(false)->end()
                ->booleanNode('singleFileMode')->defaultValue(false)->end()
                ->scalarNode('singleFileOutput')->defaultValue('types.ts')->end()
            ->end();
    }

    public function addFileConfiguration($builder): void
    {
        $builder
            ->children()
                ->arrayNode('interfaces')
                    ->normalizeKeys(false)
                    ->beforeNormalization()
                        ->always(function ($v) {
                            // If it's already a list format (numeric keys), return as-is
                            if (isset($v[0]) || empty($v)) {
                                return $v;
                            }

                            // Convert map format to list format
                            // From: ['path/to/file' => ['output' => 'out/']]
                            // To: [['path' => 'path/to/file', 'output' => 'out/']]
                            $normalized = [];
                            foreach ($v as $path => $config) {
                                $normalized[] = array_merge(['path' => $path], $config ?? []);
                            }
                            return $normalized;
                        })
                    ->end()
                    ->arrayPrototype()
                        ->normalizeKeys(false)
                        ->children()
                            ->scalarNode('path')->isRequired()->end()
                            ->scalarNode('output')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function addDirectoryConfiguration($builder): void
    {
        $builder
            ->children()
                ->arrayNode('directories')
                    ->normalizeKeys(false)
                    ->beforeNormalization()
                        ->always(function ($v) {
                            // If it's already a list format (numeric keys), return as-is
                            if (isset($v[0]) || empty($v)) {
                                return $v;
                            }

                            // Convert map format to list format
                            // From: ['path/to/dir' => ['output' => 'out/', 'requireAnnotation' => false]]
                            // To: [['path' => 'path/to/dir', 'output' => 'out/', 'requireAnnotation' => false]]
                            $normalized = [];
                            foreach ($v as $path => $config) {
                                $normalized[] = array_merge(['path' => $path], $config ?? []);
                            }
                            return $normalized;
                        })
                    ->end()
                    ->arrayPrototype()
                        ->normalizeKeys(false)
                        ->children()
                            ->scalarNode('path')->isRequired()->end()
                            ->scalarNode('output')->end()
                            ->booleanNode('requireAnnotation')->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
