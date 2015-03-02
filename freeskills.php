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
define('SECTION_PAGE', 'objects');
defined('INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'booklet');

$idobject = param_integer('id', null);
$idrecord  = param_integer('idrecord', 0);
$domainsselected  = param_alphanumext('domainsselected', null);


//'<a target="_blank" href="'.get_config('wwwroot').'/artefact/booklet/freeskills.php?idobject='.object->id.'&iduser='.$USER->get('id').'domainsselected=0">'.get_string('addfreeskills', 'artefact.booklet').'</a> ';
//echo "<br>IDOBJECT: $idobject, IDUSER: $iduser, IDSELECT: $idselect, DOMINSELECTED: $domainsselected\n";

// DEBUG
//echo "<br />ID : $idobject;  DOMAINSSELECTED : $domainsselected\n";
//exit;
if ($idobject){
	if ($object = get_record('artefact_booklet_object', 'id', $idobject)){
		if ($frame = get_record('artefact_booklet_frame', 'id', $object->idframe)){
			if ($tab = get_record('artefact_booklet_tab', 'id', $frame->idtab)){
				if ($tome = get_record('artefact_booklet_tome', 'id', $tab->idtome)){
					define('TITLE', $tome->title.' -> '.$tab->title.' -> '.$frame->title.' -> '.$object->title);
                    $inlinejs = "";
					$inlinejs = ArtefactTypeFreeSkills::get_js($object->type, $object->id);
                    $optionsform = ArtefactTypeFreeSkills::get_freeskillsform($tab->id, $object->id, $idrecord, $domainsselected);


					//print_object($optionsform);
					//exit;
					$smarty = smarty(array('tablerenderer','jquery'));
					$smarty->assign('PAGEHEADING', TITLE);
					$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
					$smarty->assign('optionsform', $optionsform);
					$smarty->display('artefact:booklet:freeskills.tpl');
					exit;
				}
			}
		}
	}
}
$SESSION->add_error_msg(get_string('failed', 'artefact.booklet'));
redirect(get_config('wwwroot') . '/artefact/booklet/index.php');


die;
