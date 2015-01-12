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

function getidframe ($data){return $data->idframe;}

safe_require('artefact', 'booklet');

$idsynthesis = param_integer('idsynthesis');
$idrecord = param_integer('idrecord', null);
global $USER;
if ($idrecord) {
    log_warn("Synthese avec idrecord  : $idrecord");
}

$sql="SELECT ob.id as id, ob.type as type, ob.title as title, ob.idframe as idframe, fr.list as list
       FROM {artefact_booklet_synthesis} sy
       JOIN {artefact_booklet_object} ob ON sy.idobjectlinked = ob.id
       JOIN {artefact_booklet_frame} fr ON fr.id = ob.idframe
       WHERE sy.idobject = ?
       ORDER BY fr.displayorder, ob.displayorder";
$objectslinked = get_records_sql_array($sql, array($idsynthesis));
// obtient pour le champ de synthese la liste des objets liés avec les champs ; id, type, title, idframe, list trié par frame
$synthesis = get_record('artefact_booklet_resulttext', 'idobject', $idsynthesis, 'idowner', $USER->get('id'));
// valeur du champ de synthese si déjà enregistré ou synthétisé
$frameslinked = array_unique(array_map('getidframe',$objectslinked));
$datasynthesis = "";
if ($frameslinked) {
    foreach ($frameslinked as $i =>$framelinked) {
        // $datasynthesis .= "Frame : $framelinked";
        $objectsframelinked = array();
        $j = 0;
        foreach ($objectslinked as $objectlinked) {
            if ($objectlinked->idframe == $framelinked) {
                $objectsframelinked[$j++] = $objectlinked;
            }
        }
        // $datasynthesis .= var_export($objectsframelinked, true) . "<br>";
        $rslt = "<ul>";
        // le cadre est une liste
        if ($objectsframelinked[0]-> list) {
            // $rslt .= "<table class=\"tablerenderer \"><thead>\n<tr>";
            // ligne d'entete
            // foreach ($objectsframelinked as $object) {
            //     $rslt .= "<th>". $object -> title . "</th>";
            // }
            //$rslt .= "</tr></thead>";
            // calcul du nombre d'elements de la liste
            switch ($objectsframelinked[0]->type) {
                case 'longtext':
                case 'shorttext':
                case 'area':
                case 'htmltext':
                    $n = count_records('artefact_booklet_resulttext', 'idobject', $objectsframelinked[0]->id, 'idowner', $USER->get('id'));
                    break;
                case 'radio':
                    $n = count_records('artefact_booklet_resultradio', 'idobject', $objectsframelinked[0]->id, 'idowner', $USER->get('id'));
                    break;
                case 'checkbox':
                    $n = count_records('artefact_booklet_resultcheckbox', 'idobject', $objectsframelinked[0]->id, 'idowner', $USER->get('id'));
                    break;
                case 'date':
                    $n = count_records('artefact_booklet_resultdate', 'idobject', $objectsframelinked[0]->id, 'idowner', $USER->get('id'));
                    break;
            }
            // construction d'un tableau des lignes : une par élément, chaque ligne contient les valeurs de tous les objets
            $ligne = array();
            for ($i = 0; $i < $n; $i++) {
                $ligne[$i] = "";
            }
            // pour chaque objet, on complete toutes les lignes
            foreach ($objectsframelinked as $object) {
                if ($object->type == 'longtext' || $object->type == 'shorttext' || $object->type == 'area' || $object->type == 'htmltext' || $object->type == 'synthesis') {
					// MODIF JF 2015/01/22
					// do est un mot réserve pour PostGres : do -> rd
					$sql="SELECT * FROM {artefact_booklet_resulttext} re
                           JOIN {artefact_booklet_resultdisplayorder} rd ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
                           WHERE re.idobject = ?
                           AND re.idowner = ?
                           ORDER BY rd.displayorder";


                    $txts = get_records_sql_array($sql, array($object->id, $USER->get('id')));
                    $i = 0;
                    foreach ($txts as $txt) {
                        $ligne[$i].= $txt -> value . " ";
                        $i++ ;
                    }
                }
                else if ($object->type == 'radio') {
					// MODIF JF 2015/01/22
					// do est un mot réserve pour PostGres : do -> rd
                    $sql="SELECT * FROM {artefact_booklet_resultradio} re
                           JOIN {artefact_booklet_resultdisplayorder} rd ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
                           JOIN {artefact_booklet_radio} ra ON (ra.id = re.idchoice)
                           WHERE re.idobject = ?
                           AND re.idowner = ?
                           ORDER BY rd.displayorder";

                    $radios = get_records_sql_array($sql, array($object->id, $USER->get('id')));
                    $i = 0;
                    foreach ($radios as $radio) {
                        $ligne[$i].= $radio->option . " ";
                        $i++ ;
                    }
                }
                else if ($object->type == 'checkbox') {
					// MODIF JF 2015/01/22
					// do est un mot réserve pour PostGres : do -> rd
                    $sql="SELECT * FROM {artefact_booklet_resultcheckbox} re
                           JOIN {artefact_booklet_resultdisplayorder} rd ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
                           WHERE re.idobject = ?
                           AND re.idowner = ?
                           ORDER BY rd.displayorder";
                    $checkboxes = get_records_sql_array($sql, array($object->id, $USER->get('id')));
                    $i = 0;
                    foreach ($checkboxes as $checkbox) {
                        $ligne[$i].= ($checkbox->value ? $object -> title : "" ) . " ";
                        $i++ ;
                    }
                }
                else if ($object->type == 'date') {
					// MODIF JF 2015/01/22
					// do est un mot réserve pour PostGres :  do -> rd
					$sql="SELECT * FROM {artefact_booklet_resultdate} re
                           JOIN {artefact_booklet_resultdisplayorder} rd ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
                           WHERE re.idobject = ?
                           AND re.idowner = ?
                           ORDER BY rd.displayorder";
                    $dates = get_records_sql_array($sql, array($object->id, $USER->get('id')));
                    $i = 0;
                    foreach ($dates as $date) {
                        $ligne[$i].= $date->value . " ";
                        $i++ ;
                    }
                }
            }
            for ($i = 0; $i < $n; $i++) {
                $rslt .= "<li>" . $ligne[$i] . "</li>";
            }
            $rslt .= "</ul>";
        }
        // le cadre n'est pas une liste
        else {
            // $rslt .= "\n<table class=\"resumepersonalinfo\">";
            // $rslt .= "<ul>";
            foreach ($objectsframelinked as $object) {
                if ($object->type == 'longtext' || $object->type == 'shorttext' || $object->type == 'area' || $object->type == 'htmltext' || $object->type == 'synthesis') {
                    $val = get_record('artefact_booklet_resulttext', 'idowner', $USER->get('id'), 'idobject', $object->id);
                    if ($val -> value) {
                        $rslt .= "<li>";
                        $rslt .= $object -> title;
                        $rslt .= " : ";
                        $rslt .= $val -> value;
                        $rslt .= "</li>";
                    }
                }
                else if ($object->type == 'radio') {
                    $radio = get_record('artefact_booklet_resultradio', 'idowner', $USER->get('id'), 'idobject', $object->id);
                    $val = get_record('artefact_booklet_radio', 'id', $radio->idchoice);
                    $rslt .= "<li>";
                    $rslt .= $object -> title ;
                    $rslt .= " : ";
                    $rslt .= $val -> option;
                    $rslt .= "</li>";
                }
                else if ($object->type == 'checkbox') {
                    $coche = get_record('artefact_booklet_resultcheckbox', 'idowner', $USER->get('id'), 'idobject', $object->id);
                    $rslt .= ($coche->value ? "<li>".$object -> title ."</li>" : "" );
                }
                else if ($object->type == 'date') {
                    $date = get_record('artefact_booklet_resultdate', 'idowner', $USER->get('id'), 'idobject', $object->id);
                    $rslt .= "<li>";
                    $rslt .= $object -> title;
                    $rslt .= " : ";
                    $rslt .= $date->value ;
                    $rslt .= "</li>";
                }
            }
            $rslt .= "</ul>";
        }
        $datasynthesis .= $rslt . "<br>";
    }
}

// mise a jour du champ de synthese si déja enregistré ou synthétisé
if ($synthesis != false) {
    $datasynthesis = $synthesis->value . $datasynthesis;
    $data = new StdClass;
    $data->value = $datasynthesis;
    $data->idobject = $idsynthesis;
    $data->idowner = $USER->get('id');
    $temp = get_record('artefact_booklet_resulttext', 'idobject', $idsynthesis, 'idowner', $USER->get('id'));
    $data->idrecord = $temp->idrecord;
    update_record('artefact_booklet_resulttext', $data, array('idobject'=> $idsynthesis, 'idowner'=> $USER->get('id')));
}
// création d'un enregistrement, d'un displayorder et d'un artefact si jamais enregistré ni synthétisé
else {
    $data = new StdClass;
    $data->value = $datasynthesis;
    $data->idobject = $idsynthesis;
    $data->idowner = $USER->get('id');
    $sql1 = "SELECT MAX(idrecord) as ir
             FROM {artefact_booklet_resulttext}
             WHERE idowner = ?";
    $maxtext = get_record_sql($sql1, array($USER->get('id')));
    $sql2 = "SELECT MAX(idrecord) as ir
             FROM {artefact_booklet_resultradio}
             WHERE idowner = ?";
    $maxrad= get_record_sql($sql2, array($USER->get('id')));
    $sql3 = "SELECT MAX(idrecord) as ir
             FROM {artefact_booklet_resultcheckbox}
             WHERE idowner = ?";
    $maxcb = get_record_sql($sql3, array($USER->get('id')));
    $sql4 = "SELECT MAX(idrecord) as ir
             FROM {artefact_booklet_resultdate}
             WHERE idowner = ?";
    $maxda = get_record_sql($sql4, array($USER->get('id')));
    $max = max(array($maxcb->ir, $maxrad->ir, $maxtext->ir, $maxda->ir));
    settype($max, 'integer');
    $idrecord = $max + 1;
    $data->idrecord = $idrecord;
    insert_record('artefact_booklet_resulttext', $data);
    $displayorder = new StdClass;
    $displayorder->idrecord = $idrecord;
    $displayorder->displayorder = 0;
    $displayorder->idowner = $USER->get('id');
    insert_record('artefact_booklet_resultdisplayorder', $displayorder);
    $obj = get_record('artefact_booklet_object', 'id', $idsynthesis);
    $idframe = $obj -> idframe;
    $frame = get_record('artefact_booklet_frame', 'id', $idframe);
    $selectedtome = get_record('artefact_booklet_selectedtome', 'iduser', $USER->get('id'));
    $tome = get_record('artefact_booklet_tome', 'id', $selectedtome->idtome);
    $classname = 'ArtefactTypeVisualization';
    $a = new $classname(0, array(
        'owner' => $USER->get('id'),
        'description' => $idframe,
        'title' => $tome->title . " - ". $frame ->title,
        'note' => $tome->id,
        )
    );
    $a->commit();
}

json_reply(null, "bof");
