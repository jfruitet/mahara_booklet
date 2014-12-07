<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', true);
define('MENUITEM', 'content/booklet');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'booklet');
define('SECTION_PAGE', 'tomes');
defined('INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'booklet');
$idtome = param_integer('id', null);

if (!empty($idtome)){
	$tome = get_record('artefact_booklet_tome', 'id', $idtome);
	if (!empty($tome)){
    	$imageauthor = get_config('wwwroot') . 'theme/raw/static/images/btn_access.png';

		define('TITLE', $tome->title);
        $smarty = smarty();
 		$smarty->assign('title', $tome->title);
		if (isset($tome->status)){
            $smarty->assign('statusmodif', get_string('statusmodif','artefact.booklet'));
			if (empty($tome->status)){
				$smarty->assign('statusvalue', get_string('allowed','artefact.booklet'));
			}
			else{
                $smarty->assign('statusvalue', get_string('forbidden','artefact.booklet'));
			}
		}
        $smarty->assign('idtome', $idtome);

		$author = get_record('artefact_booklet_author', 'idtome', $idtome);
		if (!empty($author)){
    		$smarty->assign('author', 1);
            $smarty->assign('authortitle', get_string('author','artefact.booklet'));
	        $smarty->assign('authorlastname', $author->authorlastname);
    	    $smarty->assign('authorfirstname', $author->authorfirstname);
        	$smarty->assign('authormail', $author->authormail);
	        $smarty->assign('authorinstitution', $author->authorinstitution);
    	    $smarty->assign('authorurl', '<a target="_blank" href="'. $author->authorurl .'">'.$author->authorurl.'</a>');
        	$smarty->assign('version', $author->version);
			$smarty->assign('dateversion', $author->timestamp);
    	    $smarty->assign('copyright', '<b>'.get_string('copyright','artefact.booklet')."</b>\n<pre>".$author->copyright."</pre>\n");
		}
		else{
    		$smarty->assign('author', 0);
        	$smarty->assign('copyright', get_string('copyright','artefact.booklet').' '.get_string('copyright_ccnd','artefact.booklet'));
		}
		$smarty->assign('PAGEHEADING', TITLE);
		$smarty->display('artefact:booklet:copyright.tpl');
	}
}


