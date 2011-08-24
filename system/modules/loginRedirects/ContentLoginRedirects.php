<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

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
 * @copyright  MEN AT WORK 2011
 * @package    loginRedirects 
 * @license    GNU/LGPL 
 * @filesource
 */
class ContentLoginRedirects extends ContentElement
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
        if (TL_MODE == 'BE')
        {
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### LOGIN REDIRECTS ###';
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
        $this->import("FrontendUser");

        // Get the redirect array
        if (strlen($this->lr_choose_redirect) == 0)
            return;

        if (count($this->FrontendUser->groups) == 0)
            return;

        $arrMemberRedirect = deserialize($this->lr_choose_redirect);
        // Get usergroups
        $arrMemberGroups = $this->FrontendUser->groups;

        // Check if user is logedin
        if ($this->FrontendUser->login == 1)
        {
            // Check each group from user if it is in the redirect array, redirect the user
            foreach ($arrMemberRedirect as $key => $value)
            {
                if (in_array($value["lr_usergroup"], $arrMemberGroups) || $value["lr_usergroup"] == "all")
                {
                    // Get ID for page
                    $intPage = str_replace(array("{{link_url::", "}}"), array("", ""), $value["lr_redirecturl"]);
                    // Load Page
                    $arrPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($intPage)->fetchAllAssoc();

                    //Check if we have a page
                    if (count($arrPage) == 0)
                        $this->log("Try to redirect, but the necessary page cannot be found in the database.", __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
                    else
                        $this->redirect($this->generateFrontendUrl($arrPage[0]));
                }
            }
        }
    }

}

?>