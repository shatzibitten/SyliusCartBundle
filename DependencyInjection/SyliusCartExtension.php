<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\CartBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;

/**
 * Cart extension.
 * 
 * @author Paweł Jędrzejewski <pjedrzejewski@diweb.pl>
 */
class SyliusCartExtension extends Extension
{
    /**
     * @see Extension/Symfony\Component\DependencyInjection\Extension.ExtensionInterface::load()
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();

        $config = $processor->processConfiguration($configuration, $config);
        
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/container'));
         
        if (!in_array($config['driver'], array('ORM'))) {
            throw new \InvalidArgumentException(sprintf('Driver "%s" is unsupported for this extension.', $config['driver']));
        }
        
        if (!in_array($config['engine'], array('php', 'twig'))) {
            throw new \InvalidArgumentException(sprintf('Engine "%s" is unsupported for this extension.', $config['engine']));
        }
        
        $loader->load(sprintf('driver/%s.xml', $config['driver']));
        $loader->load(sprintf('engine/%s.xml', $config['engine']));
        
        $container->setParameter('sylius_cart.driver', $config['driver']);
        $container->setParameter('sylius_cart.engine', $config['engine']);
        
        $container->setAlias('sylius_cart.operator', $config['operator']);
        
        $configurations = array(
            'controllers',
            'forms',
            'provider',
            'listeners',
        );
         
        foreach($configurations as $basename) {
            $loader->load(sprintf('%s.xml', $basename));
        }
        
        $this->remapParametersNamespaces($config['classes'], $container, array(
            'model'      => 'sylius_cart.model.%s.class',
            'listener'    => 'sylius_cart.listener.%s.class',
        ));
        
        $this->remapParametersNamespaces($config['classes']['controller'], $container, array(
            'backend'      => 'sylius_cart.controller.backend.%s.class',
            'frontend'	   => 'sylius_cart.controller.frontend.%s.class'
        ));
        
        $this->remapParametersNamespaces($config['classes']['form'], $container, array(
            'type'      => 'sylius_cart.form.type.%s.class',
        ));
    }
    
	/**
     * Remap parameters.
     * 
     * @param $config
     * @param ContainerBuilder $container
     * @param $map
     */
    protected function remapParameters(array $config, ContainerBuilder $container, array $map)
    {
        foreach ($map as $name => $paramName) {
            if (isset($config[$name])) {
                $container->setParameter($paramName, $config[$name]);
            }
        }
    }

    /**
     * Remap parameter namespaces.
     * 
     * @param $config
     * @param ContainerBuilder $container
     * @param $map
     */
    protected function remapParametersNamespaces(array $config, ContainerBuilder $container, array $namespaces)
    {
        foreach ($namespaces as $ns => $map) {
            if ($ns) {
                if (!isset($config[$ns])) {
                    continue;
                }
                $namespaceConfig = $config[$ns];
            } else {
                $namespaceConfig = $config;
            }
            if (is_array($map)) {
                $this->remapParameters($namespaceConfig, $container, $map);
            } else {
                foreach ($namespaceConfig as $name => $value) {
                    if (null !== $value) {
                        $container->setParameter(sprintf($map, $name), $value);
                    }
                }
            }
        }
    }
}