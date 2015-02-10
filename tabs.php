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
define('SECTION_PAGE', 'tabs');
defined('INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'booklet');
$idtome = param_integer('id', null);
if ($tome = get_record('artefact_booklet_tome', 'id', $idtome)){
	define('TITLE', $tome->title);

	// Modif JF
	if (empty($tome->status)){
		$tabsform = ArtefactTypeTab::get_form($idtome);
		$tpl='artefact:booklet:tabs.tpl';
	}
	else{
    	$tabsform = ArtefactTypeTab::get_form_status($idtome);
    	$tpl='artefact:booklet:tabs2.tpl';
	}
	$inlinejs = ArtefactTypeTab::get_js('tab', $idtome);

	$smarty = smarty(array('tablerenderer','jquery'));
	$smarty->assign('PAGEHEADING', TITLE);
	$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
	$smarty->assign('tabsform', $tabsform);
	$smarty->display($tpl);
}