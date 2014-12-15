<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Christophe DECLERCQ - christophe.declercq@univ-nantes.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('docroot') . 'artefact/lib.php');

$type = param_alpha('type');
$id = param_integer('id');
if ($type == 'tome') {
    $tome = get_record('artefact_booklet_tome', 'id', $id);
    //$a = artefact_instance_from_id($tome->artefact);
    $a = artefact_instance_from_id($tome->artefact, true); // modif JF
    if ($a->get('owner') != $USER->get('id')) {
        throw new AccessDeniedException(get_string('notartefactowner', 'error'));
    }


	// Modif JF
    delete_records('artefact_booklet_selectedtome', 'idtome', $id);
	delete_records('artefact_booklet_author', 'idtome', $id);

    $tabs = get_records_array('artefact_booklet_tab', 'idtome', $id);
    if ($tabs) {
        foreach ($tabs as $tab) {
            $frames = get_records_array('artefact_booklet_frame', 'idtab', $tab->id);
            if ($frames) {
                foreach ($frames as $frame) {
                    $items = get_records_array('artefact_booklet_object', 'idframe', $frame->id);
                    if ($items) {
                        foreach ($items as $item) {
                            if ($item->type == "area" || $item->type == "shorttext" || $item->type == "longtext" || $item->type == "synthesis" || $item->type == "htmltext") {
                                $typeobj = 'text';
                            }
                            else {
                                $typeobj = $item->type;
                            }
                            delete_records('artefact_booklet_result'.$typeobj, 'idobject', $item->id);
                            if ($item->type == 'radio' || $item->type == 'synthesis') {
                                delete_records('artefact_booklet_'.$item->type, 'idobject', $item->id);
                            }
                        }
                    }
                    delete_records('artefact_booklet_object', 'idframe', $frame->id);


					// Modif JF
					// Comment reconnaitre les block_instance qui sont dues au tome a supprimer ?
					// Cette information se trouve dans le champ 'note' de l'artefact 'artefacttype' = 'visualization'
				    $blocks = get_records_array('block_instance', 'blocktype', 'bookletfield');
				    if ($blocks) {
				        foreach ($blocks as $b) {
				        	$configdata = unserialize($b->configdata);
				            if (!isset($configdata['artefactid'])) {
            					continue;
				            }
							if ($viz = get_record('artefact', 'id', $configdata['artefactid'])){ // , 'artefacttype', 'visualization' )){
            					if (($viz->artefacttype=='visualization') && !empty($viz->note) && ($viz->note==$tome->id)){
                                    delete_records('view_artefact', 'artefact', $viz->id);      // delete artefact visualization
									delete_records('artefact', 'id', $viz->id);      // delete artefact visualization
                                    delete_records('block_instance', 'id', $b->id);  // delete block instance
								}
							}
        				}
					}
                }
            }
            delete_records('artefact_booklet_frame', 'idtab', $tab->id);
        }
    }
    delete_records('artefact_booklet_tab', 'idtome', $id);
    delete_records('artefact_booklet_tome', 'id', $id);

	if ($viz = get_record('artefact', 'artefacttype', 'visualization', 'note', $tome->id)){ // , 'artefacttype', 'visualization' )){
		delete_records('view_artefact', 'artefact', $viz->id);      // delete artefact visualization
		delete_records('artefact', 'id', $viz->id);      // delete artefact visualization
	}

	if ($arte = get_record('artefact', 'id', $tome->artefact)){   // a priori ce ne devrait pas etre utile...
    	delete_records('view_artefact', 'artefact', $arte->id);
        delete_records('artefact', 'id', $arte->id);
	}
}
else if ($type == 'tab') {
    $frames = get_records_array('artefact_booklet_frame', 'idtab', $id);
    if ($frames) {
        foreach ($frames as $frame) {
            $items = get_records_array('artefact_booklet_object', 'idframe', $frame->id);
            if ($items) {
                foreach ($items as $item) {
                    if ($item->type == "area" || $item->type == "shorttext" || $item->type == "longtext" || $item->type == "synthesis"|| $item->type == "htmltext") {
                        $typeobj = 'text';
                    }
                    else {
                        $typeobj = $item->type;
                    }
                    delete_records('artefact_booklet_result'.$typeobj, 'idobject', $item->id);
                    if ($item->type == 'radio' || $item->type == 'synthesis') {
                        delete_records('artefact_booklet_'.$item->type, 'idobject', $item->id);
                    }
                }
            }
            delete_records('artefact_booklet_object', 'idframe', $frame->id);
        }
    }
    delete_records('artefact_booklet_frame', 'idtab', $id);
    delete_records('artefact_booklet_tab', 'id', $id);
}
else if ($type == 'frame') {
    $items = get_records_array('artefact_booklet_object', 'idframe', $id);
    if ($items) {
        foreach ($items as $item) {
            if ($item->type == "area" || $item->type == "shorttext" || $item->type == "longtext" || $item->type == "synthesis"|| $item->type == "htmltext") {
                $typeobj = 'text';
            }
            else {
                $typeobj = $item->type;
            }
            delete_records('artefact_booklet_result'.$typeobj, 'idobject', $item->id);
            if ($item->type == 'radio' || $item->type == 'synthesis') {
                delete_records('artefact_booklet_'.$item->type, 'idobject', $item->id);
            }
        }
    }
    delete_records('artefact_booklet_object', 'idframe', $id);
    delete_records('artefact_booklet_frame', 'id', $id);
}
else if ($type == 'object') {
    $object = get_record('artefact_booklet_object', 'id', $id);
    if ($object->type == "area" || $object->type == "shorttext" || $object->type == "longtext" || $object->type == "synthesis"|| $object->type == "htmltext") {
        $typeobj = 'text';
    }
    else {
        $typeobj = $object->type;
    }
    delete_records('artefact_booklet_result'.$typeobj, 'idobject', $id);
    if ($object->type == 'radio' || $object->type == 'synthesis') {
        delete_records('artefact_booklet_'.$object->type, 'idobject', $id);
    }
    delete_records('artefact_booklet_object', 'id', $id);
}
else if ($type == 'radio') {
    delete_records('artefact_booklet_result'.$type, 'idchoice', $id);
    delete_records('artefact_booklet_'.$type, 'id', $id);
}
else if ($type == 'synthesis') {
    delete_records('artefact_booklet_'.$type, 'id', $id);
}
else if ($type == 'visualization') {
    $rslt = get_record('artefact_booklet_resulttext', 'id', $id);
    delete_records('artefact_booklet_resultdisplayorder', 'idrecord', $rslt->idrecord, 'idowner', $USER->get('id'));
    delete_records('artefact_booklet_resulttext', 'idrecord', $rslt->idrecord, 'idowner', $USER->get('id'));
    delete_records('artefact_booklet_resultradio', 'idrecord', $rslt->idrecord, 'idowner', $USER->get('id'));
    delete_records('artefact_booklet_resultcheckbox', 'idrecord', $rslt->idrecord, 'idowner', $USER->get('id'));
}
json_reply(null, get_string('compositedeleted', 'artefact.booklet'));
