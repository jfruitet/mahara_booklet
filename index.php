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

$browse = (int) param_variable('browse', 0);

//Modif JF
$idframe  = param_integer('idframe', 0);
$okdisplay = param_integer('okdisplay', 0);

$idmodifliste = param_integer('idmodifliste', null);
// pour recuperer idmodiflist passé dans l'url
$designer = get_record('artefact_booklet_designer', 'id', $USER->get('id'));
// renvoit les designers d'id = user pour savoir si user est designer
$tomes = get_records_array('artefact_booklet_tome', 'public', 1);
// renvoit la liste des tomes publics
$admin = get_record('usr', 'id', $USER->get('id'));
// renvoit l'enregistrement de user pour tester ensuite si son champ admin est a vrai
if (!$selectedTome = get_record('artefact_booklet_selectedtome', 'iduser', $USER->get('id'))) {
    // si pas de tome selectionné, on utilise le 1er
    if ($tomes[0]) {
        $tomeselected = $tomes[0];
        $idtome = $tomeselected->id;
    }
}
else {
    $idtome = $selectedTome->idtome;
    $t = get_record('artefact_booklet_tome', 'id', $idtome);
    if ($t->public == 0 && count($designer) == 0) {
        // si tome selectionne n'est pas public, on utilise le premier
        $tomeselected = $tomes[0];
        $idtome = $tomeselected->id;
    }
}

$visuatest = false;
if (count($designer) != 0) {
    // si user est designer
    $temp = param_integer('tome', null);
    if (!is_null($temp)) {
        // si un parametre tome est fourni
        $count = count_records('artefact_booklet_tome', 'id', $temp);
        if ($count != 0) {
            // si ce tome existe, on l'utilise au lieu du tome selectionné
            $idtome = $temp;
            $visuatest = true;
        }
    }
}

// Affichage d'un tome
if (isset($idtome)) {
    // si idtome est défini, ce qui est vrai dans tous les cas sauf avant que le 1er tome soit public
    $tome = get_record('artefact_booklet_tome', 'id', $idtome);
    define('TITLE', $tome->title);
    $sql = "SELECT MIN( displayorder) as val
            FROM {artefact_booklet_tab}
            WHERE idtome = ?";
    $min = get_record_sql($sql, $idtome);
    // calcule valeur min de displayorder dans artefact_booklet_tab
    $sql = "SELECT MAX( displayorder ) as val
            FROM {artefact_booklet_tab}
            WHERE idtome = ?";
    $max = get_record_sql($sql, $idtome);
    // calcule valeur max de displayorder dans artefact_booklet_tab
    $tab = param_integer('tab', $min->val);
    if ($tab > $max->val) {
        $tab = $min->val;
    }
    // parametre tab transmis dans l'url, par defaut ou si > max, on prend le min

    if (!$tabs = ArtefactTypeVisualization::submenu_items($idtome)){
        $SESSION->add_error_msg(get_string('incorrectbooklettab', 'artefact.booklet'));
        redirect(get_config('wwwroot') . '/artefact/internal/index.php');
	}

	// construit le tableau des tabs
    define('BOOKLET_SUBPAGE', $tabs[$tab]['page']);
    // ajoute au tableau des tabs mention de celui qui est sélectionné
	$tabs = ArtefactTypeVisualization::submenu_items($idtome);

	// Modif JF :: Hide / Show
    $inlinejs = "";

	$idtab = get_record('artefact_booklet_tab', 'displayorder', $tab, 'idtome', $tome->id);
    // tab du tome dont le display order est $tab

    $frames = get_records_array('artefact_booklet_frame', 'idtab', $idtab->id);
    // frames du tab courant
    $ids = array();
    // tableau pour listes id -> id
	if ($frames) {
    	foreach ($frames as $frame) {
            //echo "<br / FRAME\n";
         	//print_object($frame);
            if ($frame->list) {
           	    $ids[$frame->id] = $frame->id;
	    	}
	    }
    	// pour generer le tableau des fonctions js pour chaque frame
        //echo "<br / TAB\n";
		//print_object($tab);
		//echo "<br / IDS\n";
        //print_object($ids);

		$inlinejs = ArtefactTypeVisualization::get_js('visualization', $ids, $tab);
		//print_object($inlinejs);
		//exit;
    }

	// Menu
	$menuspecialform =  ArtefactTypeVisualization::get_menu_frames($idtome, $tab, $idframe, $idmodifliste, $browse, $okdisplay);

    // renvoit la forme correspondant au tome à afficher
	if (!empty($idframe)){
		if ($okdisplay){
    		$tomeform = ArtefactTypeVisualization::get_aframeform_display($idtome, $tab, $idframe, $idmodifliste, $browse);
		}
		else{
    		$tomeform = ArtefactTypeVisualization::get_aframeform($idtome, $tab, $idframe, $idmodifliste, $browse);
		}
	}
	else{
		if ($okdisplay){
    		$tomeform = ArtefactTypeVisualization::get_form_display($idtome, $tab, $idmodifliste, $browse);
		}
		else{
    		$tomeform = ArtefactTypeVisualization::get_form($idtome, $tab, $idmodifliste, $browse);
		}
	}

	$indexform = $tomeform;
}
else {
    define('TITLE', get_string('booklet', 'artefact.booklet'));
    $inlinejs ="";
    $tabs= array();
    $indexform = "";
}

// Selection d'un tome
if ($tomes) {
    // pour formulaire de choix du tome, le selectionné par défaut
    $options = array();
    // construit dans options un tableau des tomes : id -> title
    foreach ($tomes as $item) {
        $options[$item->id] = $item->title;
    }
    $choiceform = pieform(array(
        'name' => 'choiceform',
        'plugintype' => 'artefact',
        'successcallback' => 'choiceform_submit',
        'pluginname' => 'booklet',
        'method' => 'post',
        'renderer' => 'oneline',
        'elements' => array(
            'typefield' => array(
                'type' => 'select',
                'options' => $options,
                'title' => get_string('tomechoice', 'artefact.booklet'),
                'defaultvalue' => (!$visuatest) ? $idtome : null,
            ),
            'save' => array(
                'type' => 'submit',
                'value' => get_string('valid', 'artefact.booklet'),
            )
        ),
    ));
}
else {
    $choiceform = "";
}

// Modification des booklets
/*
if ($designer) {
    $modform = array(
        'name'        => 'modform',
        'plugintype'  => 'artefact',
        'successcallback' => 'modform_submit',
        'pluginname'  => 'booklet',
        'method'      => 'post',
        'renderer'    => 'oneline',
        'elements'    => array(
            'save' => array(
                'type' => 'submit',
                'value' => get_string('modif', 'artefact.booklet'),
            )
        ),
        'autofocus'  => false,
    );
    $indexform['modform'] = pieform($modform);
}
*/

// Modification des booklets
if ($designer) {
    $modform = array(
        'name'        => 'modform',
        'plugintype'  => 'artefact',
        'successcallback' => 'modform_submit',
        'pluginname'  => 'booklet',
        'method'      => 'post',
        'renderer'    => 'oneline',
        'elements'    => array(
            'save' => array(
                'type' => 'submit',
                'value' => get_string('modif', 'artefact.booklet'),
            )
        ),
        'autofocus'  => false,
    );

    $pf = '<fieldset class="pieform-fieldset"><legend>'. get_string('modifbooklet', 'artefact.booklet') . ' </legend><div class="surligne">' . pieform($modform) . '<i>'.get_string('modifbookletdesc', 'artefact.booklet').'</i> </div></fieldset>';
    $indexform['modform'] = $pf;
}


if ($admin->admin) {
    // si admin : formulaires de gestion des concepteurs
    $sql = "SELECT * FROM {usr}
           WHERE id IN (SELECT id from {artefact_booklet_designer})";
    $items = get_records_sql_array($sql,array());
    // liste des usr qui sont designers
    if ($items) {
        $designers = array();
        foreach ($items as $item) {
            // construit un tableau des designers : id -> name
            $designers[$item->id] = $item->firstname.' '.$item->lastname.' ('.$item->username.')';
        }
        // formulaire de suppression de designers
        $admindeleteform = pieform(array(
            'name' => 'admindeleteform',
            'plugintype' => 'artefact',
            'successcallback' => 'admindeleteform_submit',
            'pluginname' => 'booklet',
            'method' => 'post',
            'renderer' => 'oneline',
            'elements' => array(
                'id' => array(
                    'type' => 'select',
                    'options' => $designers,
                    'title' => get_string('deletedesigner', 'artefact.booklet'),
                ),
                'save' => array(
                    'type' => 'submit',
                    'value' => get_string('delete', 'artefact.booklet'),
                )
            ),
        ));
    }
    else {
        $admindeleteform = "";
    }
    // formulaire d'ajout de designers
    $adminform = pieform(array(
        'name' => 'adminform',
        'plugintype' => 'artefact',
        'successcallback' => 'adminform_submit',
        'pluginname' => 'booklet',
        'method' => 'post',
        'renderer' => 'oneline',
        'elements' => array(
            'name' => array(
                'type' => 'text',
                'title' => get_string('adddesigner', 'artefact.booklet'),
                'size' => 20,
            ),
            'save' => array(
                'type' => 'submit',
                'value' => get_string('add', 'artefact.booklet'),
            )
        ),
    ));
    $aide = '';
    $pf = '<fieldset class="pieform-fieldset"><legend>'. get_string('adminfield', 'artefact.booklet') . ' ' . $aide . '</legend>' . $adminform . $admindeleteform . '</fieldset>';
    $indexform['adminform'] = $pf;
}
if (isset($idtome)) {
    $aide = '<span class="help"><a href="" onclick="contextualHelp(&quot;pieform'.$idtome.'&quot;,&quot;to'.$idtome.'&quot;,&quot;artefact&quot;,&quot;booklet&quot;,&quot;&quot;,&quot;&quot;,this); return false;"><img src="'.get_config('wwwroot').'/theme/raw/static/images/help.png" alt="Help" title="Help"></a></span>';
}
else {
    $aide = '';
}


$smarty = smarty(array('tablerenderer','jquery'));
$smarty->assign('PAGEHELPNAME', true);
$smarty->assign('PAGEHELPICON', $aide);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('menuspecialform', $menuspecialform);
$smarty->assign('help', $aide);
$smarty->assign('indexform', $indexform);
$smarty->assign('choiceform', $choiceform);
$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->assign('d', $designer);
$smarty->assign('SUBPAGENAV', $tabs);
$smarty->display('artefact:booklet:index.tpl');

function modform_submit(Pieform $form, $values) {
    $goto = get_config('wwwroot').'/artefact/booklet/tomes.php';
    redirect($goto);
}

function choiceform_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    $goto = get_config('wwwroot') . '/artefact/booklet/index.php';
    $count = count_records('artefact_booklet_selectedtome', 'iduser', $USER->get('id'));
    $data = new StdClass;
    $data->idtome = $values['typefield'];
    $data->iduser = $USER->get('id');
    if ($count == 0) {
        insert_record('artefact_booklet_selectedtome', $data);
    }
    else {
        update_record('artefact_booklet_selectedtome', $data, 'iduser', $USER->get('id'));
    }
    $SESSION->add_ok_msg(get_string('bookletsaved', 'artefact.booklet'));
    redirect($goto);
}

function adminform_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    $goto = get_config('wwwroot') . '/artefact/booklet/index.php';
    $designer = get_record('usr', 'username', $values['name']);
    if (!$designer) {
        $SESSION->add_error_msg(get_string('errusername', 'artefact.booklet'));
        redirect($goto);
    }
    $count = count_records('artefact_booklet_designer', 'id', $designer->id);
    if ($count != 0) {
        $SESSION->add_error_msg(get_string('useralreadyadd', 'artefact.booklet'));
        redirect($goto);
    }
    $dataobject = new stdClass();
    $dataobject->id = $designer->id;
    insert_record('artefact_booklet_designer', $dataobject);
    $SESSION->add_ok_msg(get_string('usersaved', 'artefact.booklet'));
    redirect($goto);
}

function admindeleteform_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    $goto = get_config('wwwroot') . '/artefact/booklet/index.php';
    delete_records('artefact_booklet_designer', 'id', $values['id']);
    $SESSION->add_ok_msg(get_string('userdeleted', 'artefact.booklet'));
    redirect($goto);
}
