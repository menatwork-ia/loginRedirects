<?php
/**
 * @copyright  MEN AT WORK 2018
 * @package    MenAtWork\LoginRedirectBundle
 * @license    GNU/LGPL
 */

namespace MenAtWork\LoginRedirectBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Config\ConfigInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use MenAtWork\LoginRedirectBundle\LoginRedirectBundle;

/**
 * Class Plugin
 *
 * @package MenAtWork\LoginRedirectBundle\ContaoManager
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
            BundleConfig::create(LoginRedirectBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class])
                ->setReplace(['contao-legacy/loginredirects'])
                ->setReplace(['andreasisaak/loginredirects']),
        ];
    }
}