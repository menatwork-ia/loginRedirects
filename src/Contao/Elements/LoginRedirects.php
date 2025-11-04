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


use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Database;
use Contao\FrontendUser;
use Contao\StringUtil;
use Contao\System;
use Dflydev\DotAccessData\Data;
use Psr\Log\LogLevel;

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
     * Template var
     *
     * @var string
     */
    protected $strTemplate = "ce_loginRedirects";

    /**
     * Check if we are in the backend.
     *
     * @return bool
     */
    private function checkIfBackend(): bool
    {
        $requestStack = System::getContainer()->get("request_stack");
        $scopeMatcher = System::getContainer()->get("contao.routing.scope_matcher");
        $request      = $requestStack?->getCurrentRequest();
        if ($request && $scopeMatcher?->isBackendRequest($request)) {
            return true;
        }

        return false;
    }

    /**
     * Check and get the FE user.
     *
     * @return FrontendUser|null
     */
    private function getFrontendUser(): ?FrontendUser
    {
        $security = System::getContainer()->get("security.helper");
        $user     = $security->getUser();
        if (
            $user instanceof FrontendUser
            && $security->isGranted('IS_AUTHENTICATED_REMEMBERED', 'contao_frontend')
        ) {
            return $user;
        }

        return null;
    }

    /**
     * Backend
     *
     * @return string
     */
    public function generate()
    {
        // If backendmode shows widlcard.
        if ($this->checkIfBackend()) {
            $arrRedirect = StringUtil::deserialize($this->lr_choose_redirect, true);

            $arrWildcard = [];
            $i           = 0;

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
            $objTemplate->title    = $this->headline;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Frontend
     */
    protected function compile()
    {
        $feUser      = $this->getFrontendUser();
        $arrRedirect = StringUtil::deserialize($this->lr_choose_redirect, true);
        if (count($arrRedirect) == 0) {
            return;
        }

        // Get usergroups
        $arrCurrentGroups = (is_array($feUser->groups)) ? $feUser->groups : [];

        // Build group and members array
        foreach ($arrRedirect as $key => $value) {
            $redirect = false;
            $arrId    = explode("::", $value['lr_id']);

            switch ($arrId[0]) {
                case 'G':
                    //redirect if the user is in the correct group
                    if (in_array($arrId[1], $arrCurrentGroups)) {
                        $redirect = true;
                    }
                    break;
                case 'M':
                    //redirect if the FE-User id is found
                    if ($feUser->id == $arrId[1]) {
                        $redirect = true;
                    }
                    break;
                case 'allmembers':
                    //redirect if we have a valid FE-User
                    if ($feUser->id != '') {
                        $redirect = true;
                    }
                    break;
                case 'guestsonly':
                    //skip loop if we have a user-id
                    if ($feUser->id == '') {
                        $redirect = true;
                    }
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
                $arrPage = Database::getInstance()
                                   ->prepare("SELECT * FROM tl_page WHERE id=?")
                                   ->execute((int) $intPage)
                                   ->fetchAllAssoc()
                ;

                //Check if we have a page
                if (count($arrPage) == 0) {
                    $logger = static::getContainer()->get('monolog.logger.contao');
                    $logger->log(
                        LogLevel::ERROR,
                        "Try to redirect, but the necessary page cannot be found in the database.",
                        array('contao' => new ContaoContext(__FUNCTION__, __CLASS__))
                    );
                } else {
                    // Get information form current page.
                    $arrCurrentPage = $GLOBALS['objPage']->row();

                    // Check if redirect target and current page are equal.                                    
                    if ($arrCurrentPage['id'] != $arrPage[0]['id']) {
                        $parser       = System::getContainer()->get('contao.insert_tag.parser');
                        $pageRedirect = $parser->replace($value['lr_redirecturl']);
                        $this->redirect($pageRedirect);
                    }
                }
            }
        }
    }

    /** ------------------------------------------------------------------------
     * Helper
     */

    /**
     * Look up a member name or group name
     *
     * @param string|int $strID
     *
     * @return string
     */
    private function lookUpName(string|int $strID): string
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

                    $objUser = Database::getInstance()
                                       ->prepare("SELECT * FROM tl_member WHERE id=?")
                                       ->limit(1)
                                       ->execute($strID)
                    ;

                    if ($objUser->numRows == 0) {
                        return $GLOBALS['TL_LANG']['ERR']['lr_unknownMember'];
                    } elseif (strlen($objUser->firstname) != 0 && strlen($objUser->lastname) != 0) {
                        return $objUser->firstname . " " . $objUser->lastname;
                    } else {
                        return $objUser->username;
                    }
                } elseif ($strID[0] == "G") {
                    $strID    = $strID[1];
                    $objGroup = Database::getInstance()
                                        ->prepare("SELECT * FROM tl_member_group WHERE id=?")
                                        ->limit(1)
                                        ->execute($strID)
                    ;

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
     * @param string|int $strID
     *
     * @return array
     */
    private function lookUpPage(string|int $strID): array
    {
        $strID   = str_replace(["{{link_url::", "}}"], ["", ""], $strID);
        $arrPage = Database::getInstance()
                           ->prepare("SELECT * FROM tl_page WHERE id=?")
                           ->execute((int) $strID)
                           ->fetchAllAssoc()
        ;

        if (count($arrPage) == 0) {
            return [
                "title" => $GLOBALS['TL_LANG']['ERR']['lr_unknownPage'],
                "link"  => "",
            ];
        } else {
            return [
                "title" => $arrPage[0]["title"] . ((strlen($arrPage[0]["pageTitle"]) != 0) ? " - " . $arrPage[0]["pageTitle"] : ""),
                "link"  => \Contao\PageModel::findById($arrPage[0])?->getFrontendUrl(),
            ];
        }
    }
}