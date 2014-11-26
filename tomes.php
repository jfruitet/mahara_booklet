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

// Page d'édition d'un tome
define('INTERNAL', true);
define('MENUITEM', 'content/booklet');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'booklet');
define('SECTION_PAGE', 'tomes');
defined('INTERNAL') || die();
require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('booklet', 'artefact.booklet'));
require_once('pieforms/pieform.php');
safe_require('artefact', 'booklet');

$tomeform = ArtefactTypeTome::get_form();
$inlinejs = ArtefactTypeTome::get_js('tome');
// le formulaire obtenu de pieform et le js sont integres dans le template smarty
$smarty = smarty(array('tablerenderer','jquery'));
$smarty->assign('tomeform', $tomeform);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->display('artefact:booklet:tomes.tpl');
