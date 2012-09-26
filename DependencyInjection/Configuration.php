<?php
namespace Publero\AdvancedTelecomSMSBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author mhlavac
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('publero_advanced_telecom_sms', 'array');

        $rootNode
            ->children()
                ->scalarNode('service_id')->isRequired()->end()
                ->scalarNode('password')->isRequired()->end()
                ->scalarNode('mode')
                    ->defaultValue('https')
                    ->validate()
                        ->ifNotInArray(array('http', 'https'))
                        ->thenInvalid('%s mode is not supported')
                    ->end()
                ->end()
                ->scalarNode('server')
                    ->defaultValue('production')
                    ->validate()
                        ->ifNotInArray(array('production', 'test'))
                        ->thenInvalid('%s server is not supported')
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}