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

define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'booklet');
global $USER;

$limit = param_integer('limit', null);
$offset = param_integer('offset', 0);
$type = param_alpha('type');
$id = param_integer('id', null);

$data = array();
$count = 0;
$othertable = 'artefact_booklet_' . $type;
if ($type == 'visualization') {
    $objects = get_records_array('artefact_booklet_object', 'idframe', $id);
    $item = null;
    if ($objects) {
        foreach ($objects as $object) {
            if ((substr($object->name, 0, 5) == 'Title' || substr($object->name, 0, 5) == 'title') &&
                ($object->type == 'area' || $object->type == 'shorttext' || $object->type == 'longtext' || $object->type == 'htmltext' || $object->type == 'synthesis')) {
                $item = $object;
                break;
            }
        }
    }
    if (is_null($item)) {
        $sql = "SELECT *
                FROM {artefact_booklet_object}
                WHERE idframe = ?
                AND displayorder = (SELECT MIN(displayorder)
                                    FROM {artefact_booklet_object}
                                    WHERE idframe = ?
                                    AND (type='area'
                                         OR type='shorttext'
                                         OR type='longtext'
                                         OR type='synthesis'
                                         OR type='htmltext'))";
        $item = get_record_sql($sql, array($id, $id));
    }
    // Modif JF 2015/01/12
	// do est un mot protege sur Postgres
	$sql = "SELECT DISTINCT t.value, t.id FROM {artefact_booklet_resulttext} t
             JOIN {artefact_booklet_resultdisplayorder} d ON t.idrecord = d.idrecord AND t.idowner = d.idowner
             WHERE t.idobject = ?
             AND t.idowner = ?
             ORDER BY d.displayorder";

	if (!$data = get_records_sql_array($sql, array((($item)?$item->id:0), $USER->get('id')))) {
        $data = array();
    }
    $count = count($data);
}
// type <> visualization
else if ($type == 'listskills') {   // MODIF JF
    $sql = 'SELECT ar.* FROM {artefact_booklet_list} ar WHERE ar.idobject = ?';
    if (!$data = get_records_sql_array($sql, array($id))) {
        $data = array();
    }
    $count = count_records('artefact_booklet_list', 'idobject', $id);
}
else if ($type == 'synthesis') {
    if (!$data = get_records_array($othertable, 'idobject', $id)) {
        $data = array();
    }
    else {
        foreach ($data as $item) {
            $temp = get_record('artefact_booklet_object', 'id', $item->idobjectlinked);
            $item->title = $temp->title;
        }
    }
    $count = count_records($othertable, 'idobject', $id);
}
else if ($type == 'radio') {
    $sql = 'SELECT ar.* FROM {' . $othertable . '} ar WHERE ar.idobject = ?';
    if (!$data = get_records_sql_array($sql, array($id))) {
        $data = array();
    }
    $count = count_records($othertable, 'idobject', $id);
}
else if ($type == 'object') {
    $sql = 'SELECT ar.* FROM {' . $othertable . '} ar WHERE ar.idframe = ? ORDER BY ar.displayorder';
    if (!$data = get_records_sql_array($sql, array($id))) {
        $data = array();
    }
    foreach ($data as $item) {
        $item->type = get_string($item->type, 'artefact.booklet');
    }
    $count = count_records($othertable, 'idframe', $id);
}
else if ($type == 'frame') {
    /*
	$sql = 'SELECT ar.* FROM {' . $othertable . '} ar WHERE ar.idtab = ? ORDER BY ar.displayorder';
    if (!$data = get_records_sql_array($sql, array($id))) {
        $data = array();
    }
    else {
        foreach ($data as $item) {
            if ($item->list == 1) {
                $item->list = get_string('yes', 'artefact.booklet');
            }
            else {
                $item->list = get_string('no', 'artefact.booklet');
            }
        }
    }
    $count = count_records($othertable, 'idtab', $id);  // idtome
	*/
        // liste des frames du tab ordonnes par displayorder
        // $frames = get_records_array('artefact_booklet_frame', 'idtab', $tab->id, 'displayorder');
		// MODIF JF
		// Ordonner les frames selon leur frame parent et leur ordre d'affichage
	    $recframes = get_records_sql_array('SELECT ar.* FROM {artefact_booklet_frame} ar WHERE ar.idtab = ? ORDER BY ar.idparentframe ASC, ar.displayorder ASC', array($id));
		// DEBUG
		//print_object( $frames);
		//exit;
		// REORDONNER sous forme d'arbre parcours en profondeur d'abord
        $tabaff_niveau = array();
        $tabaff_codes = array();
		// 52 branches possibles a chauque noeud, cela devrait suffire ...
		$tcodes = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
        $niveau_courant = 0;
        $ordre_courant = 0;
        $parent_courant = 0;

		// Reordonner
        if ($recframes) {
			foreach ($recframes as $recframe) {
				if ($recframe->idparentframe == 0){
                    $niveau_courant = 0;
				}
				else if ($recframe->idparentframe != $parent_courant){
					// changement de niveau
					$niveau_courant = $tabaff_niveau[$recframe->idparentframe] + 1;
                    $ordre_courant = 0;
				}
				$tabaff_niveau[$recframe->id] = $niveau_courant;
				$parent_courant = $recframe->idparentframe;

                $code='';
				if ($niveau_courant>0){
					$code =  $tabaff_codes[$recframe->idparentframe];
				}
                $code.=$tcodes[$ordre_courant];
                $tabaff_codes[$recframe->id] = $code;
                $ordre_courant++;
			}
		}
        asort($tabaff_codes);
        /*
		 echo "<br />DEBUG :: lib.php :: 2016 <br />\n";
		foreach ($tabaff_codes as $key => $val){
            echo "<br />DEBUG :: ".$key."=".$val."\n";
		}
		exit;
		*/
        $data = array();
        foreach ($tabaff_codes as $key => $val){
            // echo "<br />DEBUG :: ".$key."=".$val."\n";
            $data[] = get_record('artefact_booklet_frame', 'id', $key);
		}
        if ($data){
			foreach ($data as $item) {
            	if ($item->list == 1) {
                	$item->list = get_string('yes', 'artefact.booklet');
	            }
    	        else {
        	        $item->list = get_string('no', 'artefact.booklet');
            	}
			}
        }
        $count = count_records('artefact_booklet_frame', 'idtab', $id);  // idtome
}
else if ($type == 'tab') {
    $sql = 'SELECT ar.* FROM {' . $othertable . '} ar WHERE ar.idtome = ? ORDER BY ar.displayorder';
    if (!$data = get_records_sql_array($sql, array($id))) {
        $data = array();
    }
    $count = count_records($othertable, 'idtome', $id);
}
else if ($type == 'tome') {
    if (!$data = get_records_select_array($othertable)) {
        $data = array();
    }
    $count = count_records($othertable);
}

json_reply(false, array(
    'data' => $data,
    'limit' => $limit,
    'offset' => $offset,
    'count' => $count,
    'type' => $type,
    'id' => $id,
));
