<?php

if (!defined('TL_ROOT'))
    die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  MEN AT WORK 2011, certo web & design GmbH 2011 
 * @package    loginRedirects 
 * @license    LGPL 
 * @filesource
 */
/**
 * Table tl_content
 */
// Palettes
$GLOBALS['TL_DCA']['tl_content']['palettes']["loginRedirects"] = '{type_legend},type;{lr_legend},lr_choose_redirect;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;';

// Fields
$GLOBALS['TL_DCA']['tl_content']['fields']['lr_choose_redirect'] = array(
    'label' => $GLOBALS['TL_LANG']['tl_content']['lr_choose_redirect'],
    'exclude' => true,
    'inputType' => 'multiColumnWizard',
    'save_callback' => array(
        array("LoginRedirectsCallback", "saveCallChooseMemberRedirect")
    ),
    'eval' => array
        (
        'style' => 'width:100%;',
        'columnFields' => array
            (
            'lr_usergroup' => array
                (
                'label' => $GLOBALS['TL_LANG']['tl_content']['lr_usergroup'],
                'inputType' => 'select',
                'options_callback' => array("LoginRedirectsCallback", "optionCallMemberGroups"),
                'eval' => array('mandatory' => true, 'style' => 'width:210px;'),
            ),
            'lr_redirecturl' => array
                (
                'label' => $GLOBALS['TL_LANG']['tl_content']['lr_redirecturl'],
                'exclude' => true,
                'search' => true,
                'inputType' => 'text',
                'eval' => array('mandatory' => true, 'rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50 wizard', 'style' => 'width:370px;'),
                'wizard' => array
                    (
                    array('tl_content', 'pagePicker')
                )
            )
        )
    )
);

/**
 * Callback Class
 */
class LoginRedirectsCallback extends Backend
{

    /**
     * Return all active groups with id and name.
     * 
     * @return array 
     */
    public function optionCallMemberGroups()
    {
        $arrReturn = array();
		$arrReturn["all"] = $GLOBALS['TL_LANG']['tl_content']['lr_all_groups'];

        $arrMemberGroups = $this->Database->prepare("SELECT * FROM tl_member_group WHERE disable != 1")->execute()->fetchAllAssoc();

        foreach ($arrMemberGroups as $key => $value)
        {
            $arrReturn[$value["id"]] = $value["name"];
        }

        return $arrReturn;
    }

    /**
     * Savecallback. Check if only a group is choosen not once or once.
     * 
     * @param string $varVal
     * @param DataContainer $dc
     * @return string 
     */
    public function saveCallChooseMemberRedirect($varVal, DataContainer $dc)
    {
        $arrGroups = deserialize($varVal);
        $arrGroupsFound = array();
        
        foreach ($arrGroups as $key => $value)
        {
            if (in_array($value["lr_usergroup"], $arrGroupsFound))
            {                
                $_SESSION["TL_ERROR"]["loginRedirects"] = $GLOBALS['TL_LANG']['ERR']['lr_error_groups'];
                return "";
            }
            else
            {
                $arrGroupsFound[] = $value["lr_usergroup"];
            }
        }
        
        if (strlen($_SESSION["TL_ERROR"]["loginRedirects"]) != 0)
        {
            $_SESSION["TL_ERROR"] = "";
        }
        
        return $varVal;
    }

}

?>