<?php
namespace Publero\AdvancedTelecomSMSBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

class PubleroAdvancedTelecomSMSExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $fileLocator = new FileLocator(__DIR__.'/../Resources/config');
        $loader = new YamlFileLoader($container, $fileLocator);
        $loader->load('service.yml');

        $config = $this->processConfiguration(new Configuration(), $configs);

        foreach ($config as $index => $value) {
            $container->setParameter($this->getAlias() . '.' . $index, $value);
        }

        $connectionConfig = $this->getUrlConfig($fileLocator, $config['server'], $config['mode']);
        foreach ($connectionConfig as $index => $param) {
            $container->setParameter($this->getAlias() . '.' . $index, $param);
        }
    }

    /**
     * @param FileLocator $fileLocator
     * @param string $server
     * @param string $mode
     * @return array
     */
    private function getUrlConfig(FileLocator $fileLocator, $server = true, $mode = true)
    {
        $connectionConfig = Yaml::parse($fileLocator->locate('mpport_url.yml'));

        return $connectionConfig[$server][$mode];
    }
}
