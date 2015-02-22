<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-booklet
 * @author     Christophe DECLERCQ - christophe.declercq@univ-nantes.fr
 * @author     Jean FRUITET - jean.fruitet@univ-nantes.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', true);
define('MENUITEM', 'content/booklet');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'booklet');
define('SECTION_PAGE', 'tabs');
defined('INTERNAL') || die();
require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'booklet');

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
	// Niveau hierarchique � traiter
    $frames = get_frames($idtab);
	if (!empty($frames)){
    	foreach ($frames as $frame) {
			if (!empty($frame)){
        		xml_frame($doctab, $frame->id);
			}
		}
    }
}

function xml_frame ($doctab, $idframe) {
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
				xml_object($docframe, $object->id);
			}
    	}
	}
}

function xml_object ($docframe, $idobject) {
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
            get_records_array('artefact_booklet_synthesis', 'idobject', $idobject);
			if (!empty($objectlinked)){
				foreach ($objectlinked as $objectlinked) {
	        	    if ($obj = get_record('artefact_booklet_object', 'id', $objectlinked->idobjectlinked)){
    	        		$doclinked = $doc->createElement('linked', $obj->name);
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


$doc = new DOMDocument();
$doc->version = '1.0';
$doc->encoding = 'UTF-8';

xml_tome($idtome);
$xml = $doc->saveXML();
print($xml);
