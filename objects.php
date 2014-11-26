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


$frame = get_record('artefact_booklet_frame', 'id', $idframe);

$tab = get_record('artefact_booklet_tab', 'id', $frame->idtab);

$tome = get_record('artefact_booklet_tome', 'id', $tab->idtome);

define('TITLE', $tome->title.' -> '.$tab->title.' -> '.$frame->title);


$objectsform = ArtefactTypeObject::get_form($idframe);
$inlinejs = ArtefactTypeObject::get_js('object', $idframe);


$smarty = smarty(array('tablerenderer','jquery'));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->assign('objectsform', $objectsform);
$smarty->display('artefact:booklet:objects.tpl');
