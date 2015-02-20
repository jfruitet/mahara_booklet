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
require_once(get_config('docroot') . 'artefact/lib.php');

$id = param_integer('id');
$type = param_alpha('type');
$direction = param_alpha('direction');
if ($type == 'visualization') {
    $table = 'artefact_booklet_resulttext';
}
else {
    $table = 'artefact_booklet_'.$type;
}

$item = get_record($table, 'id', $id); // item modifie

if ($type == 'tab') {
    $ids = get_column_sql('SELECT id FROM {'.$table.'}
                          WHERE idtome = ?
                          ORDER BY displayorder', array($item->idtome));
}
else if ($type == 'frame') {
// MODIF JF
//    $ids = get_column_sql('SELECT id FROM {'.$table.'}  WHERE idtab = ? ORDER BY displayorder', array($item->idtab));
		// Ordonner les frames selon leur frame parent et leur ordre d'affichage
	    $recframes = get_records_sql_array('SELECT ar.id, ar.displayorder, ar.idparentframe FROM {artefact_booklet_frame} ar WHERE ar.idtab = ? ORDER BY ar.idparentframe ASC, ar.displayorder ASC', array($item->idtab));
		// DEBUG
        //echo "<br />CADRES<br />\n";
		//print_object( $recframes);

		// REORDONNER sous forme d'arbre parcours en profondeur d'abord
        $tabaff_ids = array();
        $tabaff_parendids = array();
        $tabaff_displayorders = array();
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
				//  debug
                $tabaff_ids[$recframe->id] = $recframe->id;
                $tabaff_parentids[$recframe->id] = $recframe->idparentframe;
                $tabaff_displayorders[$recframe->id] = $recframe->displayorder;
                $ordre_courant++;
			}
		}
        asort($tabaff_codes);

//		echo "<br />DEBUG :: compositemove.json.php :: 83 <br />\n";
//        echo "<br /><table border=\"1\" width=\"50%\">\n";
//        echo "<tr><th>ID</td><td>CODE</td><td>PARENTID</td><td>DISPLAYORDER</td></tr>\n";

//		foreach ($tabaff_codes as $key => $val){
//            echo "<tr><td>".$key."</td><td>".$val."</td><td>".$tabaff_parentids[$key]."</td><td>".$tabaff_displayorders[$key]."</td></tr>\n";
//		}
//        echo "</table><br />\n";

		// recuperer les frames concernées par le changement de position
		$parentid= $item->idparentframe;

        $ids = array();
		foreach ($tabaff_codes as $key => $val){
            // echo "<br />DEBUG :: ".$key."=".$val."\n";
			if ($tabaff_parentids[$key] == $parentid){
                $ids[] = $key;
			}
		}
//        echo "<br />DEBUG :: compositemove.json.php :: 92 <br />IDS2 : <br />\n";
//		print_object( $ids);

}
else if ($type == 'object') {
    $ids = get_column_sql('SELECT id FROM {'.$table.'}
                          WHERE idframe = ?
                          ORDER BY displayorder', array($item->idframe));
}
else if ($type == 'visualization') {
    $table = 'artefact_booklet_resultdisplayorder';
    $ids = get_column_sql('SELECT id FROM {'.$table.'}
                          WHERE idowner = ? AND
                          idrecord IN (SELECT idrecord
                                       FROM {artefact_booklet_resulttext}
                                       WHERE idowner = ? AND idobject = ?
                                       )
                          ORDER BY displayorder', array($USER->get('id'), $USER->get('id'), $item->idobject));
    // $temp = get_record('artefact_booklet_resultdisplayorder', 'idrecord', $item->idrecord, 'idowner', $USER->get('id'));
    $temp = get_record('artefact_booklet_resultdisplayorder', 'idrecord', $item->idrecord, 'idowner', $USER->get('id'));
    $id = $temp->id;
    // item est un élément de resulttext
    // temp est un element de resultdisplayorder
}


foreach ($ids as $k => $v) {
    if ($v == $id) {
        $oldorder = $k;
        break;
    }
}

if ($direction == 'up' && $oldorder > 0) {
    $neworder = array_merge(array_slice($ids, 0, $oldorder - 1),
                            array($id, $ids[$oldorder-1]),
                            array_slice($ids, $oldorder+1));
}
else if ($direction == 'down' && ($oldorder + 1 < count($ids))) {
    $neworder = array_merge(array_slice($ids, 0, $oldorder),
                            array($ids[$oldorder+1], $id),
                            array_slice($ids, $oldorder+2));
}

if (isset($neworder)) {
    foreach ($neworder as $k => $v) {
        set_field($table, 'displayorder', $k, 'id', $v);
        // modifie displayorder à k pour l enregistrement de id v
    }
    if ($direction == 'up') {
        $item->displayorder = $oldorder - 1;
    }
    else {
        $item->displayorder = $oldorder + 1;
    }
    // update_record($table, $item);
}

json_reply(null, true);
