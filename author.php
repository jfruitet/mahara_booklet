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
define('SECTION_PAGE', 'tomes');
defined('INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'booklet');


class ArtefactTypeAuthor extends ArtefactTypebooklet {
    public static function is_singular() { return true;  }
    public static function get_form($tome, $author) {

        $authorform = pieform(array(
            'name'        => 'authorform',
            'plugintype'  => 'artefact',
            'successcallback' => 'authorform_submit',
            'pluginname'  => 'booklet',
            'method'      => 'post',
            'renderer'      => 'table',
            'elements'    =>  array(
              'authormail' => array(
                    'type' => 'text',
                    'title' => get_string('mail','artefact.booklet'),
                    'size' => 60,
                    'rules' => array('required' => true),
					'defaultvalue' => ((!empty($author)) ? $author->authormail : NULL),
                ),
                'authorfirstname' => array(
                    'type' => 'text',
                    'title' => get_string('firstname'),
                    'size' => 30,
                    'rules' => array('required' => true),
					'defaultvalue' => ((!empty($author)) ? $author->authorfirstname : NULL),
                ),
                'authorlastname' => array(
                    'type' => 'text',
                    'title' => get_string('lastname'),
                    'size' => 40,
					'rules' => array('required' => true),
					'defaultvalue' => ((!empty($author)) ? $author->authorlastname : NULL),
                ),

                'authorinstitution' => array(
                    'type' => 'text',
                    'title' => get_string('institution'),
                    'size' => 40,
					'defaultvalue' => ((!empty($author)) ? $author->authorinstitution : NULL),
                ),

                'authorurl' => array(
                    'type' => 'text',
                    'title' => get_string('url','artefact.booklet'),
                    'size' => 80,
					'defaultvalue' => ((!empty($author)) ? $author->authorurl : NULL),
                    'description' => get_string('urldesc','artefact.booklet'),

                ),

                'key' => array(
                    'type' => 'password',
                    'title' => get_string('password'),
                    'rules' => array('required' => ((!empty($author)) ? ((!empty($author->key)) ? 1 : 0) : 0)),
					'defaultvalue' => '*************',
                ),
                'old' => array(
                    'type' => 'hidden',
                    'value' => ((!empty($author)) ? $author->key : NULL),
                ),

                'version' => array(
                    'type' => 'text',
                    'title' => get_string('version','artefact.booklet'),
					'defaultvalue' => ((!empty($author)) ? $author->version : NULL),
                ),

                'copyright' => array(
                    'type'=>'wysiwyg',
                    'rows' => 5,
                    'cols' => 60,
                    'title' => get_string('copyright', 'artefact.booklet'),
					'defaultvalue' => ((!empty($author)) ? $author->copyright : NULL),
                    'help' => true,
                ),

    		    'status' => array(
        			'type'  => 'radio',
					'options' => array(
    	            	0 => get_string('allowed', 'artefact.booklet'),
	    	            1 => get_string('forbidden', 'artefact.booklet'),
		    	    ),
                    'defaultvalue' => ((empty($tome->status)) ? 0 : 1),
        		    'rules' => array(
                		'required' => true,
            		),
	            	'separator' => ' &nbsp; ',
    	           	'title' => get_string('selectstatus', 'artefact.booklet'),
        	       	'description' => get_string('selectstatusdesc','artefact.booklet'),
				),
                'title' => array(
                    'type' => 'hidden',
                    'value' => ((!empty($tome)) ? $tome->title : NULL),
                ),
                'help' => array(
                    'type'=>'hidden',
                    'value' => ((!empty($tome)) ? $tome->help : NULL),
                ),
                'public' => array(
                    'type' => 'hidden',
                    'value' => ((!empty($tome)) ? $tome->public : NULL)
                ),

                'idtome' => array(
                    'type' => 'hidden',
                    'value' => $tome->id,
                ),

                'save' => array(
                    'type' => 'submitcancel',
                    'value' => array(get_string('authorform', 'artefact.booklet'), get_string('canceltab', 'artefact.booklet')),
                    'goto' => get_config('wwwroot') . '/artefact/booklet/tomes.php',
                ),

            ),
            'autofocus'  => false,
        ));
        $tabsform['tabname'] = $authorform;
        return $tabsform;
    }
}


function authorform_submit(Pieform $form, $values) {
    global $USER, $SESSION, $DB;
	// DEBUG
	/*
	echo "<br />----------------------<br />DEBUG :: author.php:: FORM<br/>\n";
	print_object($form);
    echo "<br />----------------------<br />DEBUG :: VALUES<br/>\n";
	print_object($values);
	exit;
	*/
	/*********************
	 *
	 *   DEBUG :: VALUES

Array
(
    [authormail] => jean.fruitet@univ-nantes.fr
    [authorfirstname] => Jean
    [authorlastname] => FRUITET
    [authorinstitution] => Université de Nantes
    [authorurl] => https://www.pec-univ.fr/accueil-1.kjsp
    [key] => 1234
    [version] => 1.0
    [copyright] =>

Tous droits réservés. Diffusion soumise à autorisation du consortium PEC. Modification interdite.

    [status] => 1
    [title] => PEC_Bilan
    [help] =>

Ce Livret PEC_Bilan est une des composantes du Portfolio d'Expériences et de Compétences.
Il vous permet de présenter votre Bilan de formation.
Listez vos formations, travaux personnels, stages et expériences professionnelles, pratique des langues et atouts personnels.

Aides

    Aide en ligne sur un fichier de Mahara
    Aide en ligne sur un serveur Web dédié
    Aide en ligne sur un cours Moodle dédié


    [public] => 1
    [idtome] => 1
    [save] => Save author
    [sesskey] => wNclXdzEIbBG8DWs
)

**/
	if (!empty($values['idtome'])){
	// Tome data
		$datatome= new stdClass();
    	$datatome->id = $values['idtome'];
		//$datatome->title = !empty($values['title'])?$values['title']:'';
    	//$datatome->help = !empty($values['help'])?$values['help']:'';
	    //$datatome->public = !empty($values['public'])?$values['public']:0;
    	$datatome->status = !empty($values['status'])?$values['status']:0;
        set_field('artefact_booklet_tome', 'status', $datatome->status, 'id', $datatome->id);

		// Author data
        $dataauthor= new stdClass();
        $dataauthor->idtome=$datatome->id;
    	$dataauthor->authormail = !empty($values['authormail'])?$values['authormail']:'';
	    $dataauthor->authorfirstname = !empty($values['authorfirstname'])?$values['authorfirstname']:'';
    	$dataauthor->authorlastname = !empty($values['authorlastname'])?$values['authorlastname']:'';
    	$dataauthor->authorinstitution = !empty($values['authorinstitution'])?$values['authorinstitution']:'';
	    $dataauthor->authorurl = !empty($values['authorurl'])?$values['authorurl']:'';

        // password
        if ( !empty($values['key']) && ('*************'!=$values['key']))  {  // some imput
			if (!empty($values['old']) && ($values['old']!=md5($values['key']))){
            	// New key
				$dataauthor->key = md5($values['key']);
			}
		}
		else{
            $dataauthor->key = $values['old'];
		}
	    $dataauthor->version = !empty($values['version'])?$values['version']:'';
    	$dataauthor->copyright = !empty($values['copyright'])?$values['copyright']:'';
		// save
        /*
			echo "<br />----------------------<br />DEBUG :: DATAAUTHOR<br/>\n";
	print_object($dataauthor);
    echo "<br />----------------------<br />DEBUG :: DATATOME<br/>\n";
	print_object($datatome);
	exit;
		 */
        if ($author = get_record('artefact_booklet_author', 'idtome', $datatome->id)){
            $dataauthor->id=$author->id;
		    $id=update_record('artefact_booklet_author', (object)$dataauthor, 'id');
        }
        else {
            $id=insert_record('artefact_booklet_author', (object)$dataauthor);
        }
        if (!$id){
			$SESSION->add_error_msg(get_string('authorerror', 'artefact.booklet', $dataauthor->authormail));
	    }
	}
   	redirect(get_config('wwwroot') . '/artefact/booklet/tomes.php');

}

$idtome = param_integer('id', null);
$tome = get_record('artefact_booklet_tome', 'id', $idtome);
if (!empty($tome)){
   	define('TITLE', $tome->title);
}
$author = get_record('artefact_booklet_author', 'idtome', $idtome);
$authorform = ArtefactTypeAuthor ::get_form($tome, $author);

$smarty = smarty(array('tablerenderer','jquery'));
/*
$smarty->assign('title', $tome->title);
if (!empty($author)){
	$smarty->assign('author', 1);
	$smarty->assign('authortitle', get_string('author','artefact.booklet'));
	$smarty->assign('authorlastname', $author->authorlastname);
	$smarty->assign('authorfirstname', $author->authorfirstname);
	$smarty->assign('authormail', $author->authormail);
	$smarty->assign('authorinstitution', $author->authorinstitution);
	$smarty->assign('authorurl', '<a target="_blank" href="'. $author->authorurl .'">'.$author->authorurl.'</a>');
	$smarty->assign('version', get_string('version','artefact.booklet').' '.$author->version);
	$smarty->assign('dateversion', $author->timestamp);

	if (!empty($author) && !empty($author->copyright)){
		$smarty->assign('copyright', '<b>'.get_string('copyright','artefact.booklet')."</b>\n<pre>".$author->copyright."</pre>\n");
	}
	else{
		$smarty->assign('author', 0);
   		$smarty->assign('copyright', get_string('copyright','artefact.booklet').' '.get_string('copyright_ccnd','artefact.booklet'));
	}
}
*/
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('authorform', $authorform);
// $smarty->assign('SUBPAGENAV', PluginArtefactChecklist::submenu_items());
$smarty->display('artefact:booklet:author.tpl');


