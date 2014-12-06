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

$type = param_alpha('type');
$id = param_integer('id');
if ($type == 'tome') {
    $tome = get_record('artefact_booklet_tome', 'id', $id);
    $a = artefact_instance_from_id($tome->artefact);
    if ($a->get('owner') != $USER->get('id')) {
        throw new AccessDeniedException(get_string('notartefactowner', 'error'));
    }

	// Modif JF
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
                }
            }
            delete_records('artefact_booklet_frame', 'idtab', $tab->id);
        }
    }
    delete_records('artefact_booklet_tab', 'idtome', $id);
    delete_records('artefact_booklet_tome', 'id', $id);
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
