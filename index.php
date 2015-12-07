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


if (!PluginArtefactBooklet::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('booklet','artefact.booklet')));
}

$browse = (int) param_variable('browse', 0);

//Modif JF
$idframe  = param_integer('idframe', 0);
$okdisplay = param_integer('okdisplay', 0);

// Modif Mahara 15.10
$idtome = NULL;
$tomeselected = NULL;

$idmodifliste = param_integer('idmodifliste', null);

$menuspecialform =  NULL; // menu des fiches / frames

// pour recuperer idmodiflist passé dans l'url
$designer = get_record('artefact_booklet_designer', 'id', $USER->get('id'));
// renvoit les designers d'id = user pour savoir si user est designer

// Modif JF : il faut verifier si le livret est restreint à un groupe
// et si oui que l'utilisateur est membre du groupe
// $tomes = get_records_array('artefact_booklet_tome', 'public', 1);


$user = get_record('usr', 'id', $USER->get('id'));

if ($tomes = get_tomes_user($USER->get('id'))){
	// renvoit la liste des tomes publics

	// renvoit l'enregistrement de user pour tester ensuite si son champ admin est a vrai
	if (!$selectedTome = get_record('artefact_booklet_selectedtome', 'iduser', $USER->get('id'))) {
    	// si pas de tome selectionné, on utilise le 1er
	    if (!empty($tomes) && isset($tomes[0])) {
    	    $tomeselected = $tomes[0];
        	$idtome = $tomeselected->id;
	    }
		else{
			// not any tome
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
if (!empty($idtome)) {
    // si idtome est défini, ce qui est vrai dans tous les cas sauf avant que le 1er tome soit public
    $tome = get_record('artefact_booklet_tome', 'id', $idtome);
    define('TITLE', $tome->title);
    $sql = "SELECT MIN( displayorder) as val
            FROM {artefact_booklet_tab}
            WHERE idtome = ?";
    $min = get_record_sql($sql, array($idtome));
    // calcule valeur min de displayorder dans artefact_booklet_tab
    $sql = "SELECT MAX( displayorder ) as val
            FROM {artefact_booklet_tab}
            WHERE idtome = ?";
    $max = get_record_sql($sql, array($idtome));
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
    		$indexform = ArtefactTypeVisualization::get_aframeform_display($idtome, $tab, $idframe, $idmodifliste, $browse);
		}
		else{
    		$indexform= ArtefactTypeVisualization::get_aframeform($idtome, $tab, $idframe, $idmodifliste, $browse);
		}
	}
	else{
		if ($okdisplay){
    		$indexform = ArtefactTypeVisualization::get_form_display($idtome, $tab, $idmodifliste, $browse);
		}
		else{
    		$indexform = ArtefactTypeVisualization::get_form($idtome, $tab, $idmodifliste, $browse);
		}
	}
}
else {
    define('TITLE', get_string('booklet', 'artefact.booklet'));
    $inlinejs ="";
    $tabs= array();
    $indexform = "";
}

// Selection d'un tome
if (!empty($tomes)) {
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


// Retrouver les competences  associes à des fiches

	// skills selection
	if (!isset($idtab)){
        $idtab = 0;
	}

    $skillsselectform = array(
        'name'        => 'skillsselectform',
        'successcallback' => 'skillsselectform_submit',
        'method'      => 'post',
        'renderer'    => 'oneline',
        'elements'    => array(
            'save' => array(
                'type' => 'submit',
                'value' => get_string('selectskills', 'artefact.booklet'),
            ),
        ),

        'autofocus'  => false,
    );

    $pf = '<fieldset class="pieform-fieldset"><legend>'. get_string('selectskillsfromframes', 'artefact.booklet') . ' </legend><div class="fondvert">' . pieform($skillsselectform) . '<i>'.get_string('selectskillsfromframesdesc', 'artefact.booklet').'</i> </div></fieldset>';
    $indexform['skillsselectform'] = $pf;

	// Gestion des competences // skills management
    $skillsform = array(
        'name'        => 'skillsform',
        'successcallback' => 'skillsform_submit',
        'method'      => 'post',
        'renderer'    => 'oneline',
        'elements'    => array(
            'save' => array(
                'type' => 'submit',
                'value' => get_string('manageskills', 'artefact.booklet'),
            ),
        ),
        'idtab' => array(
                	'type' => 'hidden',
            		'value' => $idtab,
	    ),

        'autofocus'  => false,
    );

    $pf = '<fieldset class="pieform-fieldset"><legend>'. get_string('skillsmanagement', 'artefact.booklet') . ' </legend><div class="fondmauve">' . pieform($skillsform) . '<i>'.get_string('manageskillsdesc', 'artefact.booklet').'</i> </div></fieldset>';
    $indexform['skillsform'] = $pf;


if ($designer) {
    // Modification des booklets
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


if (!empty($user) && !empty($user->admin)) {
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
    $adminform = pieform(
		array(
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
    	        ),
    		)
		)
	);

    $pf = '<fieldset class="pieform-fieldset"><legend>'. get_string('adminfield', 'artefact.booklet') . ' </legend>' . $adminform . $admindeleteform . '</fieldset>';
    $indexform['adminform'] = $pf;
}
if (isset($idtome)) {
    $aide = '<span class="help"><a href="" onclick="contextualHelp(&quot;pieform'.$idtome.'&quot;,&quot;to'.$idtome.'&quot;,&quot;artefact&quot;,&quot;booklet&quot;,&quot;&quot;,&quot;&quot;,this); return false;"><img src="'.get_config('wwwroot').'/theme/raw/plugintype/artefact/booklet/images/help.png" alt="Help" title="Help"></a></span>';
}
else {
    $aide = '';
}

// DEBUG
//print_object ($indexform);
//exit;

$smarty = smarty(array('tablerenderer','jquery'));
$smarty->assign('PAGEHELPNAME', true);
$smarty->assign('PAGEHELPICON', $aide);
$smarty->assign('PAGEHEADING', TITLE);
if (!empty($menuspecialform)){
	$smarty->assign('menuspecialform', $menuspecialform);
}
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

function skillsform_submit(Pieform $form, $values) {
    $goto = get_config('wwwroot').'/artefact/booklet/manageskills.php';
    redirect($goto);
}

function skillsselectform_submit(Pieform $form, $values) {
    $goto = get_config('wwwroot').'/artefact/booklet/selectframesskills.php';
    redirect($goto);
}

/**
 * Retourne une liste de livrets qui sont publics
 *  & ne sont pas restrients à un groupe
 *  & sinon si l'utilisateur est membre du groupe
 */
function get_tomes_user($iduser){
	$tomes=array();
	if ($tomespublics = get_records_array('artefact_booklet_tome', 'public', 1)){
		// Tomes non assignes à des groupes
    	foreach ($tomespublics as $tome){
			if ($groupsselected = get_records_array('artefact_booklet_group', 'idtome', $tome->id)){
                foreach ($groupsselected as $selgroup){
     				if (group_user_member($selgroup->idgroup, $iduser)){
                        $tomes[] = $tome;
					}
				}
			}
			else{ // pas de restriction de groupe pour ce tome
                $tomes[] = $tome;
			}
		}
	}
	return $tomes;
}

/**
 * Establishes what role a user has in a given group.
 *
 * If the user is not in the group, this returns false.
 *
 * @param mixed $groupid  ID of the group to check
 * @param mixed $userid   ID of the user to check.
 * @return mixed          The role the user has in the group, or false if they
 *                        have no role in the group
 */
function group_user_member($groupid, $userid=null) {
    static $result;
    if (empty($userid) || empty($groupid) ) {
        return false;
    }
    return $result[$groupid][$userid] = get_field('group_member', 'role', 'group', $groupid, 'member', $userid);
}
