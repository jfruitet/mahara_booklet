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
define('SECTION_PAGE', 'frames');

defined('INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'booklet');

$idtab = param_integer('id', null);


$tab = get_record('artefact_booklet_tab', 'id', $idtab);

$tome = get_record('artefact_booklet_tome', 'id', $tab->idtome);

define('TITLE', $tome->title.' -> '.$tab->title);



$framesform = ArtefactTypeFrame::get_form($idtab);
$inlinejs = ArtefactTypeFrame::get_js('frame', $idtab);


$smarty = smarty(array('tablerenderer','jquery'));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->assign('framesform', $framesform);
$smarty->display('artefact:booklet:frames.tpl');
