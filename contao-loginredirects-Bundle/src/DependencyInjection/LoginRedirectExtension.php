<?php
/**
 *  Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2018
 * @package    MenAtWork\LoginRedirectBundle\DependencyInjection
 * @license    GNU/LGPL
 */

namespace MenAtWork\LoginRedirectBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class LoginRedirectExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        //$loader->load('listener.yml');
        //$loader->load('services.yml');
    }
}