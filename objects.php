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

$idframe = param_integer('id', null);
$idtab = param_integer('idtab', null);
$idparentframe = param_integer('idparentframe', null);

if (!empty($idframe)  && ($frame = get_record('artefact_booklet_frame', 'id', $idframe))){
    if ($tab = get_record('artefact_booklet_tab', 'id', $frame->idtab)){
    	if ($tome = get_record('artefact_booklet_tome', 'id', $tab->idtome)){
			define('TITLE', $tome->title.' -> '.$tab->title.' -> '.$frame->title);
            $objectsform = ArtefactTypeObject::get_form($idframe);
			$inlinejs = ArtefactTypeObject::get_js_2('object', $idframe, false);  // sans les cadres inclus
			$inlinejs .= ArtefactTypeFrame::get_js_2('frame', $frame->idtab, true, $frame->id);  // sans les cadres inclus
			//echo "<br />$inlinejs\n";
			//exit;
			$smarty = smarty(array('tablerenderer','jquery'));
			$smarty->assign('PAGEHEADING', TITLE);
			$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
			$smarty->assign('objectsform', $objectsform);
			$smarty->display('artefact:booklet:objects.tpl');
		}
	}
}
else{
	// New included frame
	//echo "<br />objects.php :: Ligne 45 :: IDTAB : $idtab    :: IDPARENTFRAME : $idparentframe<br /> \n";
	try {
		$count = count_records('artefact_booklet_frame', 'idtab', $idtab, 'idparentframe', $idparentframe);
	    $rec = new stdclass();
 		$rec->title = get_string('successorframe', 'artefact.booklet', $idparentframe);
	    $rec->idtab = $idtab;
    	$rec->help = '';
	    $rec->list = 0;
    	$rec->displayorder = $count + 1;
		$rec->idparentframe = $idparentframe;
		//echo "<br />objects.php :: Ligne 55 :: NEWRECFRAME<br /> \n";
		//print_object($rec);
		//exit;
    	/*
		if ($id = insert_record('artefact_booklet_frame', $rec, 'id', true)){
        	if ($frame = get_record('artefact_booklet_frame', 'id', $id)){
        		//echo "<br />objects.php :: Ligne 60 :: FRAME<br /> \n";
				//print_object($frame);
				//exit;
	    		redirect(get_config('wwwroot') . '/artefact/booklet/objects.php?id=' . $id);
			}
		}
		*/
		$id = insert_record('artefact_booklet_frame', $rec, 'id', true);
	    $goto = get_config('wwwroot') . '/artefact/booklet/objects.php?id=' . $id;
    }
    catch (Exception $e) {
        $goto = get_config('wwwroot') . '/artefact/booklet/tab.php?id=' . $idtab;
        $SESSION->add_error_msg(get_string('framesavefailed', 'artefact.booklet'));
        redirect($goto);
    }
    redirect($goto);
}

