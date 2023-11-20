<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2023
 * @package    MenAtWork\LoginRedirectsBundle
 * @license    GNU/LGPL
 * @filesource
 */

namespace MenAtWork\LoginRedirectsBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Config\ConfigInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use MenAtWork\LoginRedirectsBundle\LoginRedirectsBundle;

/**
 * Class Plugin
 *
 * @package MenAtWork\LoginRedirectsBundle\ContaoManager
 */
class Plugin implements BundlePluginInterface
{

    /**
     * @param ParserInterface $parser
     *
     * @return array|ConfigInterface[]
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(LoginRedirectsBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class])
        ];
    }
}