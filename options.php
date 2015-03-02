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
$domainsselected  = param_alphanumext('domainsselected', null);

// DEBUG
//echo "<br />ID : $idobject;  DOMAINSSELECTED : $domainsselected\n";
//exit;
if ($idobject){
	if ($object = get_record('artefact_booklet_object', 'id', $idobject)){
		if ($frame = get_record('artefact_booklet_frame', 'id', $object->idframe)){
			if ($tab = get_record('artefact_booklet_tab', 'id', $frame->idtab)){
				if ($tome = get_record('artefact_booklet_tome', 'id', $tab->idtome)){
					define('TITLE', $tome->title.' -> '.$tab->title.' -> '.$frame->title.' -> '.$object->title);
                    $reference = false;
                    $listskills = false;
                    $freeskills = false;
					$radio = false;
                    $synthese = false;
                    $inlinejs = "";

					//Allow to show or hide table needed for listskills, radio bitton and synthese
					if ($object->type == 'listskills' ) {
						//print_object($object);
						//echo "<br />options.php :: 30\n";
    					$listskills = true;
					    $inlinejs = ArtefactTypeListSkills::get_js($object->type, $idobject);
                        $optionsform = ArtefactTypeListSkills::get_form($idobject, $domainsselected);
					}
					elseif ($object->type == 'reference' ) {
						$reference = true;
						$inlinejs = ArtefactTypeReference::get_js($object->type, $idobject);
                        $optionsform = ArtefactTypeReference::get_form($idobject);
					}
					elseif ($object->type == 'freeskills' ) {
						//print_object($object);
						//echo "<br />options.php :: 30\n";
    					$freeskills = true;
					    $inlinejs = ArtefactTypeFreeSkills::get_js($object->type, $idobject);
                        $optionsform = ArtefactTypeFreeSkills::get_form($idobject);
					}
					else{
						if ($object->type == 'synthesis' ) {
							$synthese = true;
							$inlinejs = ArtefactTypeSynthesis::get_js($object->type, $idobject);
                        }
						else if ($object->type == 'radio') {
                   			$radio = true;
					    	$inlinejs = ArtefactTypeRadio::get_js($object->type, $idobject);
						}
                        $optionsform = ArtefactTypeSynthesis::get_form($idobject);
					 }

					//print_object($optionsform);
					//exit;
					$smarty = smarty(array('tablerenderer','jquery'));
					$smarty->assign('PAGEHEADING', TITLE);
					$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
					$smarty->assign('optionsform', $optionsform);
					$smarty->assign('radio', $radio);
					$smarty->assign('synthese', $synthese);
                    $smarty->assign('listskills', $listskills);
                    $smarty->assign('freeskills', $freeskills);
                    $smarty->assign('reference', $reference);
					$smarty->display('artefact:booklet:options.tpl');
					exit;
				}
			}
		}
	}
}
$SESSION->add_error_msg(get_string('failed', 'artefact.booklet'));
redirect(get_config('wwwroot') . '/artefact/booklet/index.php');
