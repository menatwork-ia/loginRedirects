<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2023
 * @package    MenAtWork\LoginRedirectsBundle
 * @license    GNU/LGPL
 * @filesource
 */

/**
 * Table tl_content
 */
// Palettes
$GLOBALS['TL_DCA']['tl_content']['palettes']['loginRedirects'] = '{type_legend},type;{lr_legend},lr_choose_redirect;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;';

// Fields
$GLOBALS['TL_DCA']['tl_content']['fields']['lr_choose_redirect'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['lr_choose_redirect'],
    'exclude' => true,
    'inputType' => 'multiColumnWizard',
    'save_callback' => [
        ["MenAtWork\LoginRedirectsBundle\Contao\Backend\Callbacks", "checkSelection"],
    ],
    'eval' => [
        'style' => 'width:100%;',
        'columnFields' => [
            'lr_id' => [
                'label' => &$GLOBALS['TL_LANG']['tl_content']['lr_id'],
                'inputType' => 'select',
                'options_callback' => ["MenAtWork\LoginRedirectsBundle\Contao\Backend\Callbacks", "getSelection"],
                'eval' => ['mandatory' => true, 'style' => 'width:210px;', 'includeBlankOption' => true],
            ],
            'lr_redirecturl' => [
                'label' => &$GLOBALS['TL_LANG']['tl_content']['lr_redirecturl'],
                'exclude' => true,
                'search' => true,
                'inputType' => 'text',
                'eval' => ['mandatory' => true, 'rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50 wizard', 'style' => 'width:370px;'],
                'wizard' => [
                    ['tl_content', 'pagePicker'],
                ],
            ],
        ],
    ],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['lr_choose_redirect']['eval']['columnFields']['lr_id']['eval']['chosen'] = true;