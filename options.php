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
$object = get_record('artefact_booklet_object', 'id', $idobject);
$frame = get_record('artefact_booklet_frame', 'id', $object->idframe);
$tab = get_record('artefact_booklet_tab', 'id', $frame->idtab);
$tome = get_record('artefact_booklet_tome', 'id', $tab->idtome);
define('TITLE', $tome->title.' -> '.$tab->title.' -> '.$frame->title.' -> '.$object->title);

//Allow to show or hide table needed for radiobtn and synthese
if ($object->type == 'synthesis' ) {
    $synthese = true;
    $radio = false;
    $inlinejs = ArtefactTypeSynthesis::get_js($object->type, $idobject);
}
else if ($object->type == 'radio') {
    $radio = true;
    $synthese = false;
    $inlinejs = ArtefactTypeRadio::get_js($object->type, $idobject);
}
else {
    $radio = false;
    $synthese = false;
    $inlinejs = "";
}
$optionsform = ArtefactTypeSynthesis::get_form($idobject);
$smarty = smarty(array('tablerenderer','jquery'));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->assign('optionsform', $optionsform);
$smarty->assign('radio', $radio);
$smarty->assign('synthese', $synthese);
$smarty->display('artefact:booklet:options.tpl');
