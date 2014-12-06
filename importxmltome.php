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
define('SECTION_PAGE', 'tabs');
defined('INTERNAL') || die();
require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'booklet');

class ArtefactTypeImporttome extends ArtefactTypebooklet {
    public static function is_singular() { return true;  }
    public static function get_form() {
        $importtomeform = pieform(array(
            'name'        => 'importtomeform',
            'plugintype'  => 'artefact',
            'successcallback' => 'importtome_submit',
            'pluginname'  => 'booklet',
            'method'      => 'post',
            'elements'    => array(
                'filename' => array(
                    'type' => 'file',
                    'title' => get_string('filename', 'artefact.booklet'),
                    'rules' => array('required' => true),
                    'maxfilesize'  => get_max_upload_size(false),
                ),
                'save' => array(
                    'type' => 'submit',
                    'value' => get_string('importtome', 'artefact.booklet'),
                ),
            ),
        ));
        return $importtomeform;
    }
}


/**
 *  Modif JF
 *  Create author record if exists
 *
 *
 */
function create_author ($doc, $idtome) {
	$id=0;
	if ($authors = $doc->getElementsByTagName('author')){
        if ($author = $authors->item(0)){
        	$authormail = $author->getAttribute('authormail');
		    $authorfirstname = $author->getAttribute('authorfirstname');
    		$authorlastname = $author->getAttribute('authorlastname');
	    	$authorinstitution = $author->getAttribute('authorinstitution');
	    	$authorurl = $author->getAttribute('authorurl');
		    $key = $author->getAttribute('key');
    		$version = $author->getAttribute('version');
		    $timestamp = $author->getAttribute('timestamp');
    		$copyright = $author->firstChild;
    		$cdata = $copyright->firstChild;

			$data = new StdClass;
    		$data->idtome = $idtome;
    		$data->authormail = $authormail;
	    	$data->authorfirstname = $authorfirstname;
    		$data->authorlastname = $authorlastname;
    		$data->authorinstitution = $authorinstitution;
			$data->authorurl = $authorurl;
			$data->key = $key;
			$data->version = $version;
			$data->timestamp = $timestamp;
    		$data->copyright = $cdata->wholeText;
    		$id = insert_record('artefact_booklet_author', $data, 'id' , true);
		}
	}
	return $id;
}

function create_tome ($doc, $owner) {
    $booklets = $doc->getElementsByTagName('booklet');
    $booklet = $booklets->item(0);
    $title = $booklet->getAttribute('title');
	// Modif JF
	$status = $booklet->getAttribute('status');
    $help = $booklet->firstChild;
    $cdata = $help->firstChild;
    $classname = 'ArtefactTypeTome';
    $a = new $classname(0, array(
        'owner' => $owner,
        'title' => 'tome',
        )
    );
    $a->commit();
    $data = new StdClass;
    $data->artefact = $a->get('id');
    $data->title = $title;
    $data->help = $cdata->wholeText;
    $data->public = $status;      // Modif JF
    $data->status = $status;      // Modif JF
    $idtome = insert_record('artefact_booklet_tome', $data, 'id' , true);
	// Modif JF
    $start=1;
    if (create_author($doc, $idtome)){
  		$start=2;
    }
	$tabs = $booklet->childNodes;
    $k=1;
	// start to node $start
	for ($i = $start; $i < $tabs->length; ++$i) {
        $tab = $tabs->item($i);
        create_tab($tab, $idtome, $k);
		$k++;
    }
    return $idtome;
}


function create_tab ($tab, $idparent, $order) {
    $title = $tab->getAttribute('title');
    $help = $tab->firstChild;
    $cdata = $help->firstChild;
    $data = new StdClass;
    $data->title = $title;
    $data->help = $cdata->wholeText;
    $data->idtome = $idparent;
    $data->displayorder = $order;
    $idtab = insert_record('artefact_booklet_tab', $data, 'id' , true);
    $frames = $tab->childNodes;
    for ($i = 1; $i < $frames->length; ++$i) {
        $frame = $frames->item($i);
        create_frame($frame, $idtab, $i);
    }
}

function create_frame ($frame, $idparent, $order) {
    $title = $frame->getAttribute('title');
    $list = $frame->getAttribute('list');
    $help = $frame->firstChild;
    $cdata = $help->firstChild;
    $data = new StdClass;
    $data->title = $title;
    $data->list = $list;
    $data->help = $cdata->wholeText;
    $data->idtab = $idparent;
    $data->displayorder = $order;
    $idframe = insert_record('artefact_booklet_frame', $data, 'id' , true);
    $objects = $frame->childNodes;
    for ($i = 1; $i < $objects->length; ++$i) {
        $object = $objects->item($i);
        create_object($object, $idframe, $i, $idparent);
    }
}

function create_object ($object, $idparent, $order, $idtab) {
    $title = $object->getAttribute('title');
    $type = $object->getAttribute('type');
    $name = $object->getAttribute('name');
    $help = $object->firstChild;
    $cdata = $help->firstChild;
    $data = new StdClass;
    $data->title = $title;
    $data->type = $type;
    $data->name = $name;
    $data->help = $cdata->wholeText;
    $data->idframe = $idparent;
    $data->displayorder = $order;
    $idobject = insert_record('artefact_booklet_object', $data, 'id' , true);
    $options = $object->childNodes;
    if ($type == "radio") {
        for ($i = 1; $i < $options->length; ++$i) {
            $option = $options->item($i);
            create_option($option, $idobject);
        }
    }
    if ($type == "synthesis") {
        for ($i = 1; $i < $options->length; ++$i) {
            $option = $options->item($i);
            create_linked($option, $idobject, $idtab);
        }
    }
}

function create_option ($object, $idparent) {
    $option = $object->textContent;
    $data = new StdClass;
    $data->option = $option;
    $data->idobject = $idparent;
    insert_record('artefact_booklet_radio', $data);
}

// ne rechercher l'objet lié que parmi les objets du meme idtab
function create_linked ($object, $idparent, $idtab) {
    global $SESSION;
    $name = $object->nodeValue;
    $sql="SELECT ob.id as id FROM {artefact_booklet_object} ob
           JOIN {artefact_booklet_frame} fr ON fr.id = ob.idframe
           WHERE ob.name LIKE ?
           AND fr.idtab = ?";
    $objectlinked = get_record_sql($sql, array($name,$idtab));
    if ($objectlinked) {
        $idobjectlinked = $objectlinked->id;
        $data = new StdClass;
        $data->idobjectlinked = $idobjectlinked;
        $data->idobject = $idparent;
        insert_record('artefact_booklet_synthesis', $data);
    }
    else {
        $SESSION->add_error_msg(get_string('noforwardref', 'artefact.booklet'));
    }
}

function importtome_submit (Pieform $form, $values) {
    global $USER, $SESSION;
    $filename = $values['filename']['tmp_name'];
    $doc = new DOMDocument();
    $doc->preserveWhiteSpace = false;
    $x = libxml_disable_entity_loader(false);
    if ($doc->load($filename)) {
        $idtome=create_tome($doc, $USER->get('id'));
    }
    else {
        $SESSION->add_error_msg(get_string('loadxmlfailed', 'artefact.booklet'));
    }
    $goto = get_config('wwwroot') . '/artefact/booklet/tomes.php';
    redirect($goto);
}

define('TITLE', get_string('importtome', 'artefact.booklet'));
$importtomeform = ArtefactTypeimporttome::get_form();
$smarty = smarty(array('tablerenderer','jquery'));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('importtomeform', $importtomeform);
$smarty->display('artefact:booklet:importtome.tpl');
