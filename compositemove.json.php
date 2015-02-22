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
safe_require('artefact', 'booklet');

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
	// $ids = get_column_sql('SELECT id FROM {'.$table.'}  WHERE idtab = ? ORDER BY displayorder', array($item->idtab));
	// Ordonner les frames selon leur frame parent et leur ordre d'affichage en profondeur
    // Get only frames ids which parent is $item->idparentframe
	$ids = get_frames($item->idtab, true, $item->idparentframe);
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

$oldorder = 0;
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
