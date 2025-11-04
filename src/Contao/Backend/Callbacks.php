<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2023
 * @package    MenAtWork\LoginRedirectsBundle
 * @license    GNU/LGPL
 * @filesource
 */

namespace MenAtWork\LoginRedirectsBundle\Contao\Backend;

use Contao\Backend;
use Contao\Database;
use Contao\DataContainer;
use Contao\Message;
use Contao\StringUtil;

/**
 * Callback Class
 */
class Callbacks extends Backend
{
    /**
     * Return all active members/fe32760
     * groups with id and name.
     *
     * @return array
     */
    public function getSelection(): array
    {
        $arrReturn               = array();
        $arrReturn["all"]        = $GLOBALS['TL_LANG']['tl_content']['lr_all'];
        $arrReturn["allmembers"] = $GLOBALS['TL_LANG']['tl_content']['lr_allmembers'];
        $arrReturn["guestsonly"] = $GLOBALS['TL_LANG']['tl_content']['lr_guestsonly'];

        // Groups
        $arrMemberGroups = Database::getInstance()
                                   ->prepare("SELECT * FROM tl_member_group WHERE disable != 1 ORDER BY name")
                                   ->execute()
                                   ->fetchAllAssoc()
        ;

        foreach ($arrMemberGroups as $key => $value) {
            $arrReturn[$GLOBALS['TL_LANG']['tl_content']['lr_groups']]["G::" . $value["id"]] = $value["name"];
        }

        // Members
        $arrMember = Database::getInstance()
                             ->prepare("SELECT * FROM tl_member WHERE locked != 1 ORDER BY firstname, lastname")
                             ->execute()
                             ->fetchAllAssoc()
        ;

        foreach ($arrMember as $key => $value) {
            if (strlen($value["firstname"]) != 0 && strlen($value["lastname"]) != 0) {
                $arrReturn[$GLOBALS['TL_LANG']['tl_content']['lr_members']]["M::" . $value["id"]] = $value["firstname"] . " " . $value["lastname"];
            } else {
                $arrReturn[$GLOBALS['TL_LANG']['tl_content']['lr_members']]["M::" . $value["id"]] = $value["username"];
            }
        }

        return $arrReturn;
    }

    /**
     * Check if a member or group is chosen twice.
     *
     * @param string        $varVal
     * @param DataContainer $dc
     *
     * @return string
     */
    public function checkSelection(string $varVal, DataContainer $dc): string
    {
        if (!$varVal) {
            return $varVal;
        }

        $arrValue      = StringUtil::deserialize($varVal);
        $arrValueFound = [];

        // Check duplicates
        foreach ($arrValue as $value) {
            if (in_array($value["lr_id"], $arrValueFound)) {
                Message::addError($GLOBALS['TL_LANG']['ERR']['lr_duplicate']);
            } else {
                $arrValueFound[] = $value["lr_id"];
            }
        }

        return serialize($arrValue);
    }
}