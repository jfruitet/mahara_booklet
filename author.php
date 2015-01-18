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
	if (empty($author)){
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
					'defaultvalue' => NULL,
                ),
                'authorfirstname' => array(
                    'type' => 'text',
                    'title' => get_string('firstname'),
                    'size' => 30,
					'defaultvalue' => NULL,
                ),
                'authorlastname' => array(
                    'type' => 'text',
                    'title' => get_string('lastname'),
                    'size' => 40,
					'defaultvalue' => NULL,
                ),

                'authorinstitution' => array(
                    'type' => 'text',
                    'title' => get_string('institution'),
                    'size' => 40,
					'defaultvalue' => NULL,
                ),

                'authorurl' => array(
                    'type' => 'text',
                    'title' => get_string('url','artefact.booklet'),
                    'size' => 80,
					'defaultvalue' => NULL,
                    'description' => get_string('urldesc','artefact.booklet')
                ),

                'key' => array(
                    'type' => 'password',
                    'title' => get_string('password'),
                    'rules' => array(
						'required' => 0,
                	),
                	'defaultvalue' => '',
				),
                'nokey' => array(
                    'type' => 'hidden',
                    'value' => 1,
                ),

                'version' => array(
                    'type' => 'text',
                    'title' => get_string('version','artefact.booklet'),
					'defaultvalue' => '',
                ),

                'copyright' => array(
                    'type'=>'wysiwyg',
                    'rows' => 5,
                    'cols' => 60,
                    'title' => get_string('copyright', 'artefact.booklet'),
					'defaultvalue' => get_string('copyright_cc', 'artefact.booklet'),
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
        )
		);
		}
		else{
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
                    //'rules' => array('required' => true),
					'defaultvalue' => ((!empty($author)) ? $author->authormail : NULL),
                ),
                'authorfirstname' => array(
                    'type' => 'text',
                    'title' => get_string('firstname'),
                    'size' => 30,
                    //'rules' => array('required' => true),
					'defaultvalue' => ((!empty($author)) ? $author->authorfirstname : NULL),
                ),
                'authorlastname' => array(
                    'type' => 'text',
                    'title' => get_string('lastname'),
                    'size' => 40,
					//'rules' => array('required' => true),
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
					'defaultvalue' => '',
                    'description' => get_string('passdesc','artefact.booklet'),
                ),
                'key2' => array(
                    'type' => 'password',
                    'title' => get_string('newpassword', 'artefact.booklet'),
					'defaultvalue' => '',
                    'description' => get_string('newpassdesc','artefact.booklet'),
                ),

                'nokey' => array(
                    'type' => 'hidden',
                    'value' => 0,
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
					'defaultvalue' => ((!empty($author) && (!empty($author->copyright)) ) ? $author->copyright : get_string('copyright_cc', 'artefact.booklet')),
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
        )
		);
		}
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

	if (!empty($values['idtome'])){
		// Tome data
		$datatome= new stdClass();
    	$datatome->id = $values['idtome'];

		// Author data
        $datatome= new stdClass();
    	$datatome->id = $values['idtome'];

		// Author data
        $dataauthor= new stdClass();
        $dataauthor->idtome=$datatome->id;
    	$dataauthor->authormail = !empty($values['authormail'])?$values['authormail']:'';
	    $dataauthor->authorfirstname = !empty($values['authorfirstname'])?$values['authorfirstname']:'';
    	$dataauthor->authorlastname = !empty($values['authorlastname'])?$values['authorlastname']:'';
    	$dataauthor->authorinstitution = !empty($values['authorinstitution'])?$values['authorinstitution']:'';
	    $dataauthor->authorurl = !empty($values['authorurl'])?$values['authorurl']:'';
	    $dataauthor->version = !empty($values['version'])?$values['version']:'';

    	$dataauthor->copyright = !empty($values['copyright'])?$values['copyright']:'';

        $access=false;
        // password
		if (!empty($values['nokey'])){  // Data Author Initialisation
			 if (empty($values['key'])){ // No input
                $access=true;
                $dataauthor->key = '';
			 }
			 else {  // some imput
                $access=true;
                $dataauthor->key = md5($values['key']);
			 }
		}
		else { // Data Author Modification
		 	if (!empty($values['key']))  {  // Some input
				if (
					(!empty($values['old']) && ($values['old']==md5($values['key'])))
					||
               		(empty($values['old']))
				){  // Pass succes
    				$access=true;
                    $dataauthor->key = md5($values['key']);
				}
				else { // Pass fail
                    $access=false;
                    $dataauthor->key = $values['old']; // nothing to modify
				}
			}
			else { // No input
     			if (empty($values['old'])){
                    $access=true;
					$dataauthor->key = '';
				}
				else{
                	$access=false;
                    $dataauthor->key = $values['old']; // nothing to modify
				}
			}
		}
		// Access
        if ($access){
		 	if (!empty($values['key2']))  {  // Some input
                $dataauthor->key = md5($values['key2']);
			}
		}
		// save author data
        if ($author = get_record('artefact_booklet_author', 'idtome', $datatome->id)){
            $dataauthor->id=$author->id;
            $dataauthor->timestamp =  db_format_timestamp(time());
		    update_record('artefact_booklet_author', (object)$dataauthor, 'id');
        }
        else {
        	$dataauthor->timestamp = db_format_timestamp(time());
            $id=insert_record('artefact_booklet_author', (object)$dataauthor);
        }

		// Access
        if ($access){
    		$datatome->status = !empty($values['status'])?$values['status']:0;
        	set_field('artefact_booklet_tome', 'status', $datatome->status, 'id', $datatome->id);
        }
        else {
			$SESSION->add_error_msg(get_string('passerror', 'artefact.booklet'));
	    }
	}
   	redirect(get_config('wwwroot') . '/artefact/booklet/tomes.php');
}



$idtome = param_integer('id', null);
$tome = get_record('artefact_booklet_tome', 'id', $idtome);
$author = get_record('artefact_booklet_author', 'idtome', $idtome);
$authorform = ArtefactTypeAuthor ::get_form($tome, $author);

if (!empty($tome)){
    define('TITLE', $tome->title);
    $smarty = smarty(array('tablerenderer','jquery'));
 	$smarty->assign('title', $tome->title);
	$smarty->assign('PAGEHEADING', TITLE);
	$smarty->assign('authorform', $authorform);
	$smarty->display('artefact:booklet:author.tpl');
}
else{
    redirect(get_config('wwwroot') . '/artefact/booklet/index.php');
}

