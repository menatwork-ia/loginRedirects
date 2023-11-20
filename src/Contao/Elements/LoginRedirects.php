<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2023
 * @package    MenAtWork\LoginRedirectsBundle
 * @license    GNU/LGPL
 * @filesource
 */

namespace MenAtWork\LoginRedirectsBundle\Contao\Elements;
use BackendTemplate;
use ContentElement;
use StringUtil;

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2023
 * @package    MenAtWork\LoginRedirectsBundle
 * @license    GNU/LGPL
 * @filesource
 */
class LoginRedirects extends ContentElement
{

    /**
     * Tempate var
     *
     * @var string
     */
    protected $strTemplate = "ce_loginRedirects";

    /**
     * Backend
     *
     * @return string
     */
    public function generate()
    {
        // If backendmode shows widlcard.
        if (TL_MODE == 'BE') {
            $arrRedirect = deserialize($this->lr_choose_redirect);

            $arrWildcard = [];
            $i = 0;

            $arrWildcard[] = '### LOGIN REDIRECTS ###';
            $arrWildcard[] = '<br /><br />';
            $arrWildcard[] = '<table>';
            $arrWildcard[] = '<colgroup>';
            $arrWildcard[] = '<col width="175" />';
            $arrWildcard[] = '<col width="400" />';
            $arrWildcard[] = '</colgroup>';
            if (count($arrRedirect) > 0) {
                foreach ($arrRedirect as $key => $value) {
                    $arrWildcard[] = '<tr>';

                    $arrWildcard[] = '<td>';
                    $arrWildcard[] = ++$i . ". " . $this->lookUpName($value["lr_id"]);
                    $arrWildcard[] = '</td>';

                    $arrPage = $this->lookUpPage($value["lr_redirecturl"]);

                    $arrWildcard[] = '<td>';
                    if ($arrPage["link"] != "") {
                        $arrWildcard[] = '<a ' . LINK_NEW_WINDOW . ' href="' . $arrPage["link"] . '">';
                        $arrWildcard[] = $arrPage["title"];
                        $arrWildcard[] = '</a>';
                    } else {
                        $arrWildcard[] = $arrPage["title"];
                    }
                    $arrWildcard[] = '</td>';

                    $arrWildcard[] = '</tr>';
                }
            } else {
                $arrWildcard[] = '<tr><td>' . $GLOBALS['TL_LANG']['tl_content']['lr_noentries'] . '</td></tr>';
            }

            $arrWildcard[] = '</table>';

            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = implode("\n", $arrWildcard);
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Frontend
     */
    protected function compile()
    {
        // Import frontenduser
        $this->import("FrontendUser", 'User');

        // Get settings
        $arrRedirect = StringUtil::deserialize($this->lr_choose_redirect, true);

        //return if the array is empty
        if (count($arrRedirect) == 0) return;

        // Get usergroups
        $arrCurrentGroups = (is_array($this->User->groups)) ? $this->User->groups : [];

        // Build group and members array
        foreach ($arrRedirect as $key => $value) {
            $redirect = false;
            $arrId = explode("::", $value['lr_id']);

            switch ($arrId[0]) {
                case 'G':
                    //redirect if the user is in the correct group
                    if (in_array($arrId[1], $arrCurrentGroups)) $redirect = true;
                    break;
                case 'M':
                    //redirect if the FE-User id is found
                    if ($this->User->id == $arrId[1]) $redirect = true;
                    break;
                case 'allmembers':
                    //redirect if we have a valid FE-User
                    if ($this->User->id != '') $redirect = true;
                    break;
                case 'guestsonly':
                    //skip loop if we have a user-id
                    if ($this->User->id == '') $redirect = true;
                    break;
                case 'all':
                    //no test, just redirect:)
                    $redirect = true;
                    break;
            }

            if ($redirect) {
                // Get ID for page
                $intPage = str_replace(["{{link_url::", "}}"], ["", ""], $value["lr_redirecturl"]);
                // Load Page
                $arrPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute((int)$intPage)->fetchAllAssoc();

                //Check if we have a page
                if (count($arrPage) == 0) {
                    $this->log("Try to redirect, but the necessary page cannot be found in the database.", __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
                } else {
                    // Get information form current page.
                    $arrCurrentPage = $GLOBALS['objPage']->row();

                    // Check if redirect target and current page are equal.                                    
                    if ($arrCurrentPage['id'] != $arrPage[0]['id']) {
                        $pageRedirect = $this->replaceInsertTags($value['lr_redirecturl']);
                        $this->redirect($pageRedirect);
                    }
                }
            }
        }

        return;
    }

    /** ------------------------------------------------------------------------
     * Helper
     */

    /**
     * Look up a member name or group name
     * @param string $strID
     * @return string
     */
    private function lookUpName($strID)
    {
        switch ($strID) {
            case 'all':
            case 'allmembers':
            case 'guestsonly':
                return $GLOBALS['TL_LANG']['tl_content']['lr_' . $strID];
                break;
            default:
                $strID = explode("::", $strID);
                if ($strID[0] == "M") {
                    $strID = $strID[1];

                    $objUser = $this->Database->prepare("SELECT * FROM tl_member WHERE id=?")->limit(1)->execute($strID);

                    if ($objUser->numRows == 0) {
                        return $GLOBALS['TL_LANG']['ERR']['lr_unknownMember'];
                    } else {
                        if (strlen($objUser->firstname) != 0 && strlen($objUser->lastname) != 0) {
                            return $objUser->firstname . " " . $objUser->lastname;
                        } else {
                            return $objUser->username;
                        }
                    }
                } else if ($strID[0] == "G") {
                    $strID = $strID = $strID[1];

                    $objGroup = $this->Database->prepare("SELECT * FROM tl_member_group WHERE id=?")->limit(1)->execute($strID);

                    if ($objGroup->numRows == 0) {
                        return $GLOBALS['TL_LANG']['ERR']['lr_unknownGroup'];
                    } else {
                        return $objGroup->name;
                    }
                }
                break;
        }
        return $GLOBALS['TL_LANG']['ERR']['lr_unknownType'];
    }

    /**
     * Look up a page title
     *
     * @param string $strID
     * @return string
     */
    private function lookUpPage($strID)
    {
        $strID = str_replace(["{{link_url::", "}}"], ["", ""], $strID);
        $arrPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute((int)$strID)->fetchAllAssoc();

        if (count($arrPage) == 0) {
            return [
                "title" => $GLOBALS['TL_LANG']['ERR']['lr_unknownPage'],
                "link" => "",
            ];
        } else {
            return [
                "title" => $arrPage[0]["title"] . ((strlen($arrPage[0]["pageTitle"]) != 0) ? " - " . $arrPage[0]["pageTitle"] : ""),
                "link" => $this->generateFrontendUrl($arrPage[0]),
            ];
        }
    }

}

?>