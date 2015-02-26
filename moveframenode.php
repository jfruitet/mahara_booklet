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
define('SECTION_PAGE', 'index');
defined('INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
require_once('license.php');
safe_require('artefact', 'booklet');
safe_require('artefact', 'file');

$idframe  = param_integer('idframe', 0);

if ($idframe && $frame = get_record('artefact_booklet_frame', 'id', $idframe)){
    if ($rectab = get_record('artefact_booklet_tab', 'id', $frame->idtab)){
        if ($tome = get_record('artefact_booklet_tome', 'id', $rectab->idtome)){
			define('TITLE', $tome->title.' -> '.$rectab->title);
		    if (!$tabs = ArtefactTypeVisualization::submenu_items($tome->id)){
        		$SESSION->add_error_msg(get_string('incorrectbooklettab', 'artefact.booklet'));
		        redirect(get_config('wwwroot') . '/artefact/internal/index.php');
			}
            // construit le tableau des tabs
		    $sql = "SELECT MIN( displayorder) as val
 FROM {artefact_booklet_tab}
 WHERE idtome = ?";
    		$min = get_record_sql($sql, $tome->id);
    		// calcule valeur min de displayorder dans artefact_booklet_tab
    		$sql = "SELECT MAX( displayorder ) as val
 FROM {artefact_booklet_tab}
 WHERE idtome = ?";
    		$max = get_record_sql($sql, $tome->id);
    		// calcule valeur max de displayorder dans artefact_booklet_tab
    		$tab = param_integer('tab', $min->val);
    		if ($tab > $max->val) {
        		$tab = $min->val;
    		}
    		// parametre tab transmis dans l'url, par defaut ou si > max, on prend le min

    		define('BOOKLET_SUBPAGE', $tabs[$tab]['page']);
    		// ajoute au tableau des tabs mention de celui qui est sélectionné
			$tabs = ArtefactTypeVisualization::submenu_items($tome->id);

			// Menu
			//$menuspecialform =  ArtefactTypeVisualization::get_menu_frames($tome->id, $rectab->id);

			$framesform = ArtefactTypeFrame::get_movenodeform($frame->idtab, $frame->id);
			//print_object($framesform);
			// exit;
            //$inlinejs = '';
			$inlinejs = ArtefactTypeFrame::get_js('frame', $frame->idtab);


			$smarty = smarty(array('tablerenderer','jquery'));
			$smarty->assign('PAGEHEADING', TITLE);
			$smarty->assign('framesform', $framesform);
			$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
            $smarty->assign('SUBPAGENAV', $tabs);
			$smarty->display('artefact:booklet:moveframenode.tpl');
			exit;
        }
	}
}
// redirect (get_config('wwwroot') . '/artefact/booklet/index.php');