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
defined('INTERNAL') || die();
require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
//safe_require('artefact', 'booklet');  // ATTENTION l'inclusion de la librairie provoque une erreur XML


$idtome = param_integer('id', null);
$tome = get_record('artefact_booklet_tome', 'id', $idtome);
$titre = $tome->title;
$name = "export_" . implode('_', explode(' ',trim($titre . date("_Y_m_d"))));
header("Content-Type: application/force-download");
header("Content-Disposition: attachment; filename=$name.xml");

function xml_author($doctome, $idtome){
    // Modif JF
    global $doc;
	$author = get_record('artefact_booklet_author', 'idtome', $idtome);
	if (!empty($author)){
		$docauthor = $doc->createElement('author');
        $docauthor->setAttribute('authormail', $author->authormail);
        $docauthor->setAttribute('authorfirstname', $author->authorfirstname);
        $docauthor->setAttribute('authorlastname', $author->authorlastname);
        $docauthor->setAttribute('authorinstitution', $author->authorinstitution);
        $docauthor->setAttribute('authorurl', $author->authorurl);
        $docauthor->setAttribute('key', $author->key);
        $docauthor->setAttribute('version', $author->version);
        $docauthor->setAttribute('timestamp', $author->timestamp);
		$copyright = $doc->createCDATASection($author->copyright);
        $doccopyright = $doc->createElement('copyright');
		$doccopyright->appendChild($copyright);
        $docauthor->appendChild($doccopyright);
        $doctome->appendChild($docauthor);
	}
}

function xml_tome ($idtome) {
    global $doc;
    $tome = get_record('artefact_booklet_tome', 'id', $idtome);
    $doctome = $doc->createElement('booklet');
    $doctome->setAttribute('title', $tome->title);
    $doctome->setAttribute('public', $tome->public);
	// Modif JF
	if (isset($tome->status)){
        $doctome->setAttribute('status', $tome->status);
	}
    $help = $doc->createCDATASection($tome->help);
    $dochelp = $doc->createElement('help');
    $dochelp->appendChild($help);
    $doctome->appendChild($dochelp);
    $doc->appendChild($doctome);
	// Modif JF
	xml_author($doctome, $idtome);
	$tabs = get_records_array('artefact_booklet_tab', 'idtome', $idtome, 'displayorder');
	if (!empty($tabs)){
        foreach ($tabs as $tab) {
			if (!empty($tab)){
        		xml_tab($doctome, $tab->id);
			}
		}
    }

}


function xml_tab ($doctome, $idtab) {
    global $doc;
    $tab = get_record('artefact_booklet_tab', 'id', $idtab);
    $doctab = $doc->createElement('tab');
    $doctab->setAttribute('title', $tab->title);
    $help = $doc->createCDATASection($tab->help);
    $dochelp = $doc->createElement('help');
    $dochelp->appendChild($help);
    $doctab->appendChild($dochelp);
    $doctome->appendChild($doctab);
    // $frames = get_records_array('artefact_booklet_frame', 'idtab', $idtab, 'displayorder');
	// Niveau hierarchique à traiter
    $frames = get_frames($idtab);
	if (!empty($frames)){
    	foreach ($frames as $frame) {
			if (!empty($frame)){
        		xml_frame($doctab, $frame->id, $idtab);
			}
		}
    }
}


function xml_frame ($doctab, $idframe, $idtab) {
    global $doc;
    $frame = get_record('artefact_booklet_frame', 'id', $idframe);
    $docframe = $doc->createElement('frame');
    $docframe->setAttribute('id', '@SPECIAL@'.$frame->id.'@SPECIAL@');
	$docframe->setAttribute('title', $frame->title);
    $docframe->setAttribute('list', $frame->list);
    $docframe->setAttribute('idparentframe', '@SPECIAL@'.$frame->idparentframe.'@SPECIAL@');
    $help = $doc->createCDATASection($frame->help);
    $dochelp = $doc->createElement('help');
    $dochelp->appendChild($help);
    $docframe->appendChild($dochelp);
    $doctab->appendChild($docframe);
    $objects=get_records_array('artefact_booklet_object', 'idframe', $idframe, 'displayorder');
	if (!empty($objects)){
		foreach($objects as $object) {
        	if (!empty($object)){
				xml_object($docframe, $object->id, $idtab);
			}
    	}
	}
}


function xml_object ($docframe, $idobject, $idtab) {
    global $doc;
    $object = get_record('artefact_booklet_object', 'id', $idobject);
	if (!empty($object)){
    	$docobject = $doc->createElement('object');
	    $docobject->setAttribute('name', $object->name);
    	$docobject->setAttribute('title', $object->title);
	    $docobject->setAttribute('type', $object->type);
    	$help = $doc->createCDATASection($object->help);
	    $dochelp = $doc->createElement('help');
    	$dochelp->appendChild($help);
	    $docobject->appendChild($dochelp);

	    if ($object->type == 'radio') {
    	    $radios = get_records_array('artefact_booklet_radio', 'idobject', $idobject);
			if (!empty($radios)){
			 	foreach ($radios as $radio) {
					if (!empty($radio)){
						$docoption = $doc->createElement('option', $radio->option);
					    $docobject->appendChild($docoption);
					}
				}
			}
        }

    	if ($object->type == 'synthesis') {
            $objectlinked=get_records_array('artefact_booklet_synthesis', 'idobject', $idobject);
			if (!empty($objectlinked)){
				foreach ($objectlinked as $objectlinked) {
	        	    if ($obj = get_record('artefact_booklet_object', 'id', $objectlinked->idobjectlinked)){
						// Purger l'id de la tab pour l'export
                        $pos = strrpos($obj->name, $idtab);
						if (!empty($pos)){
							$substrname = substr($obj->name, 0, $pos-1);
							// DEBUG
							//echo "<br />export :: 171 :: $substrname\n";
							//exit;
						}
						else{
                            $substrname = $obj->name;
						}
    	        		$doclinked = $doc->createElement('reference', $substrname);
        	    		$docobject->appendChild($doclinked);
					}
			   }
        	}
    	}
    	if ($object->type == 'reference') {
            $objectlinked=get_records_array('artefact_booklet_reference', 'idobject', $idobject);
			if (!empty($objectlinked)){
				foreach ($objectlinked as $objectlinked) {
	        	    if ($obj = get_record('artefact_booklet_object', 'id', $objectlinked->idobjectlinked)){
						// Purger l'id de la tab pour l'export
                        $pos = strrpos($obj->name, $idtab);
						if (!empty($pos)){
							$substrname = substr($obj->name, 0, $pos-1);
							// DEBUG
							//echo "<br />export :: 171 :: $substrname\n";
							//exit;
						}
						else{
                            $substrname = $obj->name;
						}
    	        		$doclinked = $doc->createElement('reference', $substrname);
        	    		$docobject->appendChild($doclinked);
					}
			   }
        	}
    	}

	    if ($object->type == 'listskills') {
    	    if ($list = get_record('artefact_booklet_list', 'idobject', $idobject)){
            	$description = $doc->createCDATASection($list->description);
	    		$docdesc = $doc->createElement('description');
    			$docdesc->appendChild($description);
	    		$docobject->appendChild($docdesc);

                if ($itemskills = get_records_array('artefact_booklet_listofskills', 'idlist', $list->id)){
			 		foreach ($itemskills as $itemskill) {
						if (!empty($itemskill)){
                    		if ($skill = get_record('artefact_booklet_skill', 'id', $itemskill->idskill)){
                                $docitemskill = $doc->createElement('item');
						    	$docitemskill->setAttribute('domain', $skill->domain);
                            	$docitemskill->setAttribute('code', $skill->code);
                                $docitemskill->setAttribute('description', strip_tags($skill->description));
								$docitemskill->setAttribute('scale', $skill->scale);
                            	$docitemskill->setAttribute('threshold', $skill->threshold);
                                $docobject->appendChild($docitemskill);
							}
						}
					}
				}
			}
        }
        $docframe->appendChild($docobject);
	}
}

/**
 * Genere une liste de frames en profondeur d'abord
 * @input : $idtab
 * @output : frame lsit
 *
 */
// version locale  car l(inclusion de la librairie booklet provoque une erreur sous forme d'une ligne vide en tete de fichier XML.
// Super penible … deboguer
function get_frames($idtab){
    $result = array();

    $tabaff_parentids = array();
    $tabaff_codes = array();

	// REORDONNER sous forme d'arbre parcours en profondeur d'abord
	// 52 branches possibles a chaque niveau de l'arbre, cela devrait suffire ...
	$tcodes = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');

    $tabaff_ids = array();
    $tabaff_niveau = array();
    // Initialisation
	$n=0;
    $niveau_courant = 0;
    $ordre_courant = 0;
    $parent_courant = 0;
	// Ordonner les frames selon leur frame parent et leur ordre d'affichage
	$recframes = get_records_sql_array('SELECT ar.* FROM {artefact_booklet_frame} ar WHERE ar.idtab = ? ORDER BY ar.idparentframe ASC, ar.displayorder ASC', array($idtab));

	if ($recframes){
		foreach ($recframes as $recframe) {
        	if ($recframe){
            	$tabaff_niveau[$recframe->id] = 0;
			}
		}

		foreach ($recframes as $recframe) {
       		if ($recframe){
           		$tabaff_codes[$recframe->id] =$tcodes[$n];
				$n++;
			}
		}

		// Reordonner

		foreach ($recframes as $recframe) {
			if ($recframe){
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
           	    $tabaff_ids[$recframe->id] = $recframe->id;
               	$tabaff_parentids[$recframe->id] = $recframe->idparentframe;
	            $tabaff_displayorders[$recframe->id] = $recframe->displayorder;
                $ordre_courant++;
			}
		}

		asort($tabaff_codes);
   		foreach ($tabaff_codes as $key => $val){
			$result[] = get_record('artefact_booklet_frame', 'id', $key);
		}
		return $result;
	}
	return false;
}


$doc = new DOMDocument();
$doc->version = '1.0';
$doc->encoding = 'UTF-8';
xml_tome($idtome);
$xml = $doc->saveXML();
print($xml);
