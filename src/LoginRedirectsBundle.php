<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2020
 * @package    MenAtWork\CeInputVarBundle
 * @license    GNU/LGPL
 * @filesource
 */

namespace MenAtWork\LoginRedirectsBundle;

use MenAtWork\LoginRedirectsBundle\DependencyInjection\LoginRedirectsExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class LoginRedirectsBundle
 *
 * @package MenAtWork\LoginRedirectsBundle
 */
class LoginRedirectsBundle extends Bundle
{
    const SCOPE_BACKEND = 'backend';
    const SCOPE_FRONTEND = 'frontend';

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new LoginRedirectsExtension();
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }
}
