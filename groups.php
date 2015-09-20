<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     JFruitet
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
define('INTERNAL', true);
define('MENUITEM', 'content/booklet');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'booklet');
define('SECTION_PAGE', 'groups');
defined('INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'booklet');
$idtome = param_integer('id', null);

if ($idtome && $tome = get_record('artefact_booklet_tome', 'id', $idtome)){
 	define('TITLE', get_string('groupsmanagement', 'artefact.booklet'));

 	$sql = "SELECT id, name, description, public, jointype, hidden FROM {group} ";
    if ($groups = get_records_sql_array($sql, NULL)){
	    $inlinejs = '';
		if ($optionsform = getform_groupsselect($idtome, $groups)){
			//print_object($optionsform);
			//exit;
			$smarty = smarty(array('tablerenderer','jquery'));
			$smarty->assign('PAGEHEADING', TITLE);
			$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
			$smarty->assign('optionsform', $optionsform);
			$smarty->display('artefact:booklet:groupsselect.tpl');
			die;
		}
	}
}
$SESSION->add_error_msg(get_string('failed', 'artefact.booklet'));
redirect(get_config('wwwroot') . '/artefact/booklet/index.php');
die;


// ------------------------------------------------------------------------------
function getform_groupsselect($idtome, $groups = NULL ) {
	global $USER;
    global $THEME;
	$compositeform = array();
	$elements = array();
	$tab_groups_selected = array();

	if (!empty($idtome   )){
        $tab_groups_selected = get_records_array('artefact_booklet_group', 'idtome', $idtome);

	}
	if (!empty($groups)){
 			$i=0;
            $elements = array();
        	foreach ($groups as $group){
				$msg='';
                $selectvalue=0;
                if ($groupselected = get_record('artefact_booklet_group', 'idgroup', $group->id, 'idtome', $idtome)){
					$selectvalue=1;
				}

				if ($group->public){
					if (!empty($msg)) $msg.=', ';
                    $msg.=get_string('grouppublic','artefact.booklet');
				}
				if ($group->hidden){
                    if (!empty($msg)) $msg.=', ';
					$msg.=get_string('grouphidden','artefact.booklet');
				}
				if ($group->jointype=='open'){
                    if (!empty($msg)) $msg.=', ';
					$msg.=get_string('groupopen','artefact.booklet');
				}
				if (!empty($msg)){
                    $msg = ' [<i>'.$msg.'</i>]';
				}
		        $elements['html'.$i] = array(
           				'type' => 'html',
	           			'value' => strip_tags($group->description).$msg."\n",
    	       	);


                $elements['id'.$i] = array(
                	'type' => 'hidden',
   		        	'value' => $group->id,
           		);

				$elements['select'.$i] = array(
        		        	'type' => 'checkbox',
                			'defaultvalue' => $selectvalue,
		                	'title' =>  $group->name,
        		        	//'description' => '',
           			);

                $i++;
			}

	        $elements['nbitems'] = array(
                'type' => 'hidden',
                'value' => $i,
    	    );

			$elements['idtome'] = array(
		        'type' => 'hidden',
        		'value' => $idtome,
           	);

       		$elements['submit'] = array(
            	'type' => 'submitcancel',
            	'value' => array(get_string('savechecklist','artefact.booklet'), get_string('canceltab', 'artefact.booklet')),
                'goto' => get_config('wwwroot') . '/artefact/booklet/tabs.php?id=' . $idtome,
        	);

            $elements['delete'] = array(
                'type' => 'checkbox',
                'help' => false,
                'title' => get_string('grouperase','artefact.booklet'),
                'defaultvalue' => 0,
                'description' => get_string('grouperasedesc','artefact.booklet'),
        	);


    	    $choice = array(
                'name' => 'listchoice',
                'plugintype' => 'artefact',
                'pluginname' => 'booklet',
        	    // 'validatecallback' => 'validate_selectlist',
            	'successcallback' => 'groups_submit',
                'renderer' => 'table',
                'elements' => $elements,
            );
        	$compositeform['choice'] = pieform($choice);
	}

	//print_object($compositeform);
	//exit;
    return $compositeform;
}


// -------------------------------------
function groups_submit(Pieform $form, $values) {
    global $SESSION;
	global $USER;
    $groupsselected='';
    $t_groupsselected=array();      // Liste des enregistrement selectionnes
    $goto = get_config('wwwroot') . '/artefact/booklet/tabs.php?id=' . $values['idtome'];

	if (!empty($values['nbitems'])){
 		for ($i=0; $i<$values['nbitems']; $i++){
			if (!empty($values['select'.$i])){
				// Creer l'association
				$a_group = new stdclass();
        	   	$a_group->idgroup = $values['id'.$i];
            	//print_object($a_group);

            	$t_groupsselected[]=$a_group;
			}
		}
	}
    if (!empty($t_groupsselected)){
		//print_object($t_groupsselected);
		//exit;
		if ($values['delete']){	// SUPPRIMER
        	foreach($t_groupsselected as $a_group){
				if ($rec = get_record('artefact_booklet_group', 'idtome', $values['idtome'], 'idgroup', $a_group->idgroup )){
                    delete_records('artefact_booklet_group', 'idtome', $values['idtome'], 'idgroup', $a_group->idgroup);
				}
			}
		}
		else{ 	// Formater la liste des modifications
        	foreach($t_groupsselected as $a_group){
				//print_object($a_group);
				//exit;
    	        // mettre à jour la table
        	    $a_rec = new stdclass();
        		$a_rec->idgroup = $a_group->idgroup;
	            $a_rec->idtome = $values['idtome'];

				if ($rec = get_record('artefact_booklet_group', 'idtome', $values['idtome'], 'idgroup', $a_rec->idgroup )){
        	        $a_rec->id=$rec->id;
            	    update_record('artefact_booklet_group', $a_rec);
				}
				else{
    	      		$id = insert_record('artefact_booklet_group', $a_rec, 'id', true);
				}
			}
		}
	}
	redirect($goto);
}

