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
require_once(dirname(__FILE__) . '/lib_skills.php');

    define('TITLE', get_string('selectframesfromskills', 'artefact.booklet'));

    $inlinejs = '';
	// $inlinejs = get_skillsjs();
	if ($optionsform = ArtefactTypeSkillFrame::get_registeredframes()){
		//print_object($optionsform);
		//exit;
		$smarty = smarty(array('tablerenderer','jquery'));
		$smarty->assign('PAGEHEADING', TITLE);
		$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
		$smarty->assign('optionsform', $optionsform);
		$smarty->display('artefact:booklet:registerdframes.tpl');
		die;
	}
	$SESSION->add_error_msg(get_string('failed', 'artefact.booklet'));
	redirect(get_config('wwwroot') . '/artefact/booklet/index.php');
	die;
