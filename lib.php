<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @author     Christophe DECLERCQ - christophe.declercq@univ-nantes.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginArtefactbooklet extends PluginArtefact {
    /* Classe pour edition d'un booklet */
    public static function get_artefact_types() {
        return array(
            'tome',
            'tab',
            'frame',
            'object',
            'radio',
            'synthesis',
            'visualization'
        );
    }
    public static function get_block_types() { return array(); }
    public static function get_plugin_name() { return 'booklet'; }
    public static function menu_items() {
        return array(
            'content/booklet' => array(
                'path' => 'content/booklet',
                'title' => get_string('booklet', 'artefact.booklet'),
                'url' => 'artefact/booklet/',
                'weight' => 50,
            ),
        );
    }

    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            $sort = (get_record_sql('SELECT MAX(sort) AS maxsort FROM {blocktype_category}')->maxsort) + 1;
            insert_record('blocktype_category', (object)array('name' => 'booklet', 'sort' => $sort));
            /* log_warn('installation de la categorie booklet'); */
        }
        else {
            /* log_warn('pas d installation necessaire de la categorie booklet'); */
        }
    }
}

class ArtefactTypebooklet extends ArtefactType {
    /* classe pour fonctions JS communes */
    public static function get_icon($options=null) {
    }
    public static function is_singular() { return false; }
    public static function get_links($id) {}
    public function commit() { parent::commit(); }

    public static function get_js($compositetype, $id = null) {
        global $THEME;
        $imagemoveblockup   = json_encode($THEME->get_url('images/btn_moveup.png'));
        $imagemoveblockdown = json_encode($THEME->get_url('images/btn_movedown.png'));
        $upstr = get_string('moveup','artefact.booklet');
        $downstr = get_string('movedown','artefact.booklet');
        $js = self::get_common_js();
        $js .= <<<EOF
tableRenderers.{$compositetype} = new TableRenderer(
    '{$compositetype}list',
    'composite.json.php',
    [
EOF;
        if ($compositetype!='tome' && $compositetype!='synthesis' && $compositetype!='radio') {
            $js .= <<<EOF
        function (r, d) {
            var buttons = [];
            if (r._rownumber > 1) {
                var up = A({'href': ''}, IMG({'src': {$imagemoveblockup}, 'alt':'{$upstr}'}));
                connect(up, 'onclick', function (e) {
                    e.stop();
                    return moveComposite(d.type, r.id, r.artefact, 'up');
                });
                buttons.push(up);
            }
            if (!r._last) {
                var down = A({'href': '', 'class':'movedown'}, IMG({'src': {$imagemoveblockdown}, 'alt':'{$downstr}'}));
                connect(down, 'onclick', function (e) {
                    e.stop();
                    return moveComposite(d.type, r.id, r.artefact, 'down');
                });
                buttons.push(' ');
                buttons.push(down);
            }
            return TD({'class':'movebuttons'}, buttons);
        },
EOF;
        }
        $js .= call_static_method(generate_artefact_class_name($compositetype), 'get_tablerenderer_js');
        $js .= call_static_method(generate_artefact_class_name($compositetype), 'get_editdel_js');
		if (isset($id)) {
            settype($id, 'integer');
            $js .= <<<EOF
tableRenderers.{$compositetype}.id = '{$id}';
tableRenderers.{$compositetype}.statevars.push('id');
EOF;
        }
        $js .= <<<EOF
tableRenderers.{$compositetype}.type = '{$compositetype}';
tableRenderers.{$compositetype}.statevars.push('type');
tableRenderers.{$compositetype}.emptycontent = '';
tableRenderers.{$compositetype}.updateOnLoad();
EOF;
        return $js;
    }

    public static function get_common_js() {
        $cancelstr = get_string('cancel','artefact.booklet');
        $addstr = get_string('add','artefact.booklet');
        $confirmdelstr = get_string('compositedeleteconfirm','artefact.booklet');
        $js = <<<EOF
var tableRenderers = {};
function toggleCompositeForm(type) {
    var elemName = '';
    elemName = type + 'form';
    if (hasElementClass(elemName, 'hidden')) {
        removeElementClass(elemName, 'hidden');
        $('add' + type + 'button').innerHTML = '{$cancelstr}';
    }
    else {
        $('add' + type + 'button').innerHTML = '{$addstr}';
        addElementClass(elemName, 'hidden');
    }
}
function compositeSaveCallback(form, data) {
    key = form.id.substr(3);
    tableRenderers[key].doupdate();
    toggleCompositeForm(key);
    // Can't reset() the form here, because its values are what were just submitted,
    // thanks to pieforms
    forEach(form.elements, function(element) {
        if (hasElementClass(element, 'text') || hasElementClass(element, 'textarea')) {
            element.value = '';
        }
    });
}
function deleteComposite(type, id) {
    if (confirm('{$confirmdelstr}')) {
        sendjsonrequest('compositedelete.json.php',
            {'id': id, 'type': type},
            'GET',
            function(data) {
                tableRenderers[type].doupdate();
            },
            function() {
                // @todo error
            }
        );
    }
    return false;
}
function moveComposite(type, id, artefact, direction) {
    sendjsonrequest('compositemove.json.php',
        {'id': id, 'type': type, 'direction':direction},
        'GET',
        function(data) {
            tableRenderers[type].doupdate();
        },
        function() {
            // @todo error
        }
    );
    return false;
}


EOF;
        $js .= self::get_showhide_composite_js();
        return $js;
    }

    static function get_showhide_composite_js() {
        return "
            function showhideComposite(r, content) {
                // get the reference for the title we just clicked on
                var titleTD = $('composite-' + r.artefact + '-' + r.id);
                var theRow = titleTD.parentNode;
                var bodyRow = $('composite-body-' + r.artefact +  '-' + r.id);
                if (bodyRow) {
                    if (hasElementClass(bodyRow, 'hidden')) {
                        removeElementClass(bodyRow, 'hidden');
                    }
                    else {
                        addElementClass(bodyRow, 'hidden');
                    }
                    return false;
                }
                // we have to actually create the dom node too
                var colspan = theRow.childNodes.length;
                var newRow = TR({'id': 'composite-body-' + r.artefact + '-' + r.id},
                    TD({'colspan': colspan}, content));
                insertSiblingNodesAfter(theRow, newRow);
            }
        ";
    }
}

class ArtefactTypeTome extends ArtefactTypebooklet {
    /* classe pour pieforms et fonctions JS propres a un tome */
    public static function is_singular() { return true;  }
    public static function get_form() {
        $tomeform = pieform(array(
            'name'        => 'tomeform',
            'plugintype'  => 'artefact',
            'successcallback' => 'addtome_submit',
            'pluginname'  => 'booklet',
            'method'      => 'post',
            'renderer'      => 'oneline',
            'elements'    => array(
                'save' => array(
                    'type' => 'submitcancel',
                    'value' => array(get_string('addtome', 'artefact.booklet'), get_string('importtome', 'artefact.booklet')),
                    'goto' => get_config('wwwroot') . '/artefact/booklet/importxmltome.php',
                ),
            ),
            'autofocus'  => false,
        ));
        return $tomeform;
    }

    public static function get_tablerenderer_js() {
        return "'title',";
    }

/**
 *  Modif JF
 *
 */

    public static function get_editdel_js() {
        $image = get_config('wwwroot') . 'theme/raw/static/images/btn_export.png';
        $imageinfo  = get_config('wwwroot') . 'theme/raw/static/images/btn_info.png';
        $editstr = get_string('edit','artefact.booklet');
        $copyrightstr = get_string('copyright','artefact.booklet');
		$exportstr = get_string('export','artefact.booklet');
        $delstr = get_string('del','artefact.booklet');
        $js = <<<EOF
          function (r, d) {
         	var copyrightlink = A({'href': 'copyright.php?id=' + r.id, 'title': '{$copyrightstr}'}, IMG({'src': '{$imageinfo}', 'alt':'{$copyrightstr}'}));
    		var editlink = A({'href': 'tabs.php?id=' + r.id, 'title': '{$editstr}'}, IMG({'src': config.theme['images/btn_edit.png'], 'alt':'{$editstr}'}));
            var exportlink = A({'href': 'exportxmltome.php?id=' + r.id, 'title': '{$exportstr}'}, IMG({'src': '{$image}', 'alt':'{$exportstr}'}));
            var dellink = A({'href': '', 'title': '{$delstr}'}, IMG({'src': config.theme['images/btn_deleteremove.png'], 'alt': '[x]'}));
            connect(dellink, 'onclick', function (e) {
                e.stop();
                return deleteComposite(d.type, r.id);
            });
            return TD({'class':'right'}, null, copyrightlink, ' ',  editlink, ' ', exportlink, ' ', dellink);
        }
    ]
);
EOF;

        return $js;
    }


    public static function ensure_composite_value($values, $owner) {
        $compositetype = 'tome';
        $classname = 'ArtefactTypeTome';
        $a = new $classname(0, array(
            'owner' => $owner,
            'title' => 'tome',
            ));
        $a->commit();
        $values['artefact'] = $a->get('id');
        $table = 'artefact_booklet_tome';
        if (!empty($values['id'])) {
            update_record($table, (object)$values, 'id');
        }
        else {
            insert_record($table, (object)$values);
        }
        $id = get_record($table, 'artefact', $a->get('id'));
        $goto = get_config('wwwroot') . '/artefact/booklet/tabs.php?id='.$id->id;
        return $goto;
    }

}

function addtome_submit (Pieform $form, $values) {
    global $USER;
    try {
        $goto = call_static_method('ArtefactTypeTome',
            'ensure_composite_value', $values, $USER->get('id'));
    }
    catch (Exception $e) {
        $goto = get_config('wwwroot') . '/artefact/booklet/tomes.php';
        $SESSION->add_error_msg(get_string('tomesavefailed', 'artefact.booklet'));
        redirect($goto);
    }
    redirect($goto);
}

// *********************************************************************
/**
 * Modif JF 2014/12/01
 * collect author status
 * @input: tome id
 * @output: record
 */

function get_author($idtome) {
	$table = 'artefact_booklet_author';
       if ($author = get_record($table, 'idtome', $idtome)){
       	// $goto = get_config('wwwroot') . '/artefact/booklet/author.php?id='.$author->id;
		// return $goto;
       	return $author;
	}
	return null;
}

/**
 * Modif JF 2014/12/01
 * return tome editing status
 * @input tome id
 * @output true: not any restriction, false: editing forbidden
 */
function get_edition_status($idtome) {
    $table = 'artefact_booklet_tome';
    if ($tome = get_record($table, 'idtome', $idtome)){
		// DEBUG
		// echo "<br />lib.php :: 339 :: EDITION status<br />\n";
		// print_object($tome);
		// exit;
        return $tome->status; // editing forbidden if status > 0
	}
	return 0;   // by default editing allowed
}



// *********************************************************************

class ArtefactTypeTab extends ArtefactTypebooklet {
    /* classe pour pieforms et fonctions JS propres a un tome */
    public static function is_singular() { return true;  }

	// Modif JF
    public static function get_form_status($idtome) {
        $tome = get_record('artefact_booklet_tome', 'id', $idtome);

		$tabform = pieform(array(
            'name'        => 'tabform',
            'plugintype'  => 'artefact',
            'successcallback' => 'tomestatus_submit',
            'pluginname'  => 'booklet',
            'method'      => 'post',
            'renderer'      => 'table',
            'elements'    => array(
				'msg1' => array(
                    'type' => 'html',
                    'title' => get_string('tomename', 'artefact.booklet'),
                    'value' => ((!empty($tome)) ? $tome->title : NULL),
                ),
				'msg2' => array(
                    'type' => 'html',
                    'title' => get_string('helptome', 'artefact.booklet'),
                    'value' => ((!empty($tome)) ? $tome->help : NULL),
                ),
				'msg3' => array(
                    'type' => 'html',
                    'title' => get_string('statusmodif', 'artefact.booklet'),
                    'value' =>  ((!empty($tome)) ? ((!empty($tome->status)) ? '<i>'.get_string('forbidden', 'artefact.booklet').'</i>' : '<i>'.get_string('allowed', 'artefact.booklet').'</i>')  : '</i>'.get_string('allowed', 'artefact.booklet').'</i>'),
                ),

                'public' => array(
                    'type' => 'checkbox',
                    'title' => get_string('public', 'artefact.booklet'),
                    'defaultvalue' => ((!empty($tome)) ? $tome->public : NULL)
                ),

                'title' => array(
                    'type' => 'hidden',
                    'value' => ((!empty($tome)) ? $tome->title : NULL),
                ),
                'help' => array(
                    'type'=>'hidden',
                    'value' => ((!empty($tome)) ? $tome->help : NULL),
                ),
                'status' => array(
                    'type' => 'hidden',
                    'value' => ((!empty($tome)) ? $tome->status : NULL)
                ),

                'save' => array(
                    'type' => 'submitcancel',
                    'value' => array(get_string('savetome', 'artefact.booklet'),
                                     get_string('canceltab', 'artefact.booklet')),
                    'goto' => get_config('wwwroot') . '/artefact/booklet/tomes.php',
                ),
                'idtome' => array(
                    'type' => 'hidden',
                    'value' => $idtome,
                )
            ),
            'autofocus'  => false,
        ));
        $visuaform = pieform(array(
            'name' => 'visuaform',
            'plugintype'  => 'artefact',
            'pluginname'  => 'booklet',
            'successcallback' => 'visualizetome_submit',
            'method'      => 'post',
            'renderer'      => 'oneline',
            'elements'    => array(
                    'save' => array(
                    'type' => 'submit',
                    'value' => get_string('visualizetome', 'artefact.booklet'),
                ),
                'idtome' => array(
                    'type' => 'hidden',
                    'value' => $idtome,
                )
            ),
        ));
        $tabsform['tabname'] = $tabform;
        $tabsform['visua'] = $visuaform;
        return $tabsform;
    }

    public static function get_form($idtome) {
        $tome = get_record('artefact_booklet_tome', 'id', $idtome);
		//if (!empty($tome->status)){
        if (isset($tome->status)){
			$status= array(
                    'type' => 'hidden',
                    'value' => $tome->status,
                );
			$msg= array(
                    'type' => 'html',
                    'title' => get_string('statusmodif', 'artefact.booklet'),
                    'value' => ((!empty($tome)) ? ((!empty($tome->status)) ? '<i>'.get_string('forbidden', 'artefact.booklet').'</i>' : '<i>'.get_string('allowed', 'artefact.booklet').'</i>')  : '</i>'.get_string('allowed', 'artefact.booklet').'</i>'),
                );

		}
  /*
			$status= array(
                    'type' => 'checkbox',
                    'title' => get_string('statusmodif', 'artefact.booklet'),
                    'defaultvalue' => ((!empty($tome)) ? $tome->status : NULL)
                );
			$msg= array(
                    'type' => 'html',
                    'title' => get_string('statusmodif', 'artefact.booklet'),
                    'value' => ((!empty($tome)) ? ((!empty($tome->status)) ? '<i>'.get_string('forbidden', 'artefact.booklet').'</i>' : '<i>'.get_string('allowed', 'artefact.booklet').'</i>')  : '</i>'.get_string('allowed', 'artefact.booklet').'</i>'),
                );
		}
*/
        $tabform = pieform(array(
            'name'        => 'tabform',
            'plugintype'  => 'artefact',
            'successcallback' => 'tomename_submit',
            'pluginname'  => 'booklet',
            'method'      => 'post',
            'renderer'      => 'table',
            'elements'    => array(
                'title' => array(
                    'type' => 'text',
                    'title' => get_string('tomename', 'artefact.booklet'),
                    'defaultvalue' => ((!empty($tome)) ? $tome->title : NULL),
                ),
                'help' => array(
                    'type'=>'wysiwyg',
                    'rows' => 16,
                    'cols' => 60,
                    'title' => get_string('helptome', 'artefact.booklet'),
                    'defaultvalue' => ((!empty($tome)) ? $tome->help : NULL),
                ),
                'public' => array(
                    'type' => 'checkbox',
                    'title' => get_string('public', 'artefact.booklet'),
                    'defaultvalue' => ((!empty($tome)) ? $tome->public : NULL)
                ),
				'msg' => $msg,
				'status' => $status,

                'save' => array(
                    'type' => 'submitcancel',
                    'value' => array(get_string('savetome', 'artefact.booklet'),
                                     get_string('canceltab', 'artefact.booklet')),
                    'goto' => get_config('wwwroot') . '/artefact/booklet/tomes.php',
                ),
                'idtome' => array(
                    'type' => 'hidden',
                    'value' => $idtome,
                )
            ),
            'autofocus'  => false,
        ));
        $addtab = pieform(array(
            'name'        => 'addtab',
            'plugintype'  => 'artefact',
            'successcallback' => 'addtab_submit',
            'pluginname'  => 'booklet',
            'method'      => 'post',
            'renderer'      => 'oneline',
            'elements'    => array(
                'save' => array(
                    'type' => 'submit',
                    'value' => get_string('addttab', 'artefact.booklet'),
                ),
                'id' => array(
                    'type' => 'hidden',
                    'value' => $idtome,
                )
            ),
            'autofocus'  => false,
        ));
        $visuaform = pieform(array(
            'name' => 'visuaform',
            'plugintype'  => 'artefact',
            'pluginname'  => 'booklet',
            'successcallback' => 'visualizetome_submit',
            'method'      => 'post',
            'renderer'      => 'oneline',
            'elements'    => array(
                    'save' => array(
                    'type' => 'submit',
                    'value' => get_string('visualizetome', 'artefact.booklet'),
                ),
                'idtome' => array(
                    'type' => 'hidden',
                    'value' => $idtome,
                )
            ),
        ));
        $tabsform['tabname'] = $tabform;
        $tabsform['addtab'] = $addtab;
        $tabsform['visua'] = $visuaform;
        return $tabsform;
    }

    public static function get_tablerenderer_js() {
        return "'title',";
    }

    public static function get_editdel_js() {
        $editstr = get_string('edit','artefact.booklet');
        $delstr = get_string('del','artefact.booklet');
        $js = <<<EOF
          function (r, d) {
            var editlink = A({'href': 'frames.php?id=' + r.id, 'title': '{$editstr}'}, IMG({'src': config.theme['images/btn_edit.png'], 'alt':'{$editstr}'}));
            var dellink = A({'href': '', 'title': '{$delstr}'}, IMG({'src': config.theme['images/btn_deleteremove.png'], 'alt': '[x]'}));
            connect(dellink, 'onclick', function (e) {
                e.stop();
                return deleteComposite(d.type, r.id);
            });
            return TD({'class':'right'}, null, editlink, ' ', dellink);
        }
    ]
);
EOF;
        return $js;
    }

    public static function ensure_composite_value($values, $owner) {
        $compositetype = 'tab';
        $count = count_records('artefact_booklet_tab', 'idtome', $values['id']);
        $val = array(
            'id' => '',
            'title' => '',
            'idtome' => $values['id'],
            'displayorder' => $count + 1
        );
        $table = 'artefact_booklet_tab';
        $id = insert_record($table, (object)$val, 'id', true);
        $goto = get_config('wwwroot') . '/artefact/booklet/frames.php?id='.$id;
        return $goto;
    }
}

// Modif JF
function tomestatus_submit (Pieform $form, $values) {
    $tome = get_record('artefact_booklet_tome', 'id', $values['idtome']);
    $tome->title = $values['title'];
    $tome->help = $values['help'];
    $tome->public = (!empty($values['public']) ? 1 : 0);
    $tome->status = (!empty($values['status']) ? 1 : 0);
    update_record('artefact_booklet_tome', $tome);
    // $goto = get_config('wwwroot') . '/artefact/booklet/tomes.php';
    $goto = get_config('wwwroot') . '/artefact/booklet/tabs.php?id=' . $tome->id;
    redirect($goto);
}


function tomename_submit (Pieform $form, $values) {
    $tome = get_record('artefact_booklet_tome', 'id', $values['idtome']);
    $tome->title = $values['title'];
    $tome->help = $values['help'];
    $tome->public = (!empty($values['public']) ? 1 : 0);
    $tome->status = (!empty($values['status']) ? 1 : 0);
	update_record('artefact_booklet_tome', $tome);
    // $goto = get_config('wwwroot') . '/artefact/booklet/tomes.php';
    $goto = get_config('wwwroot') . '/artefact/booklet/tabs.php?id=' . $tome->id;
    redirect($goto);
}

function addtab_submit(Pieform $form, $values) {
    global $USER;
    try {
        $goto = call_static_method('ArtefactTypeTab',
            'ensure_composite_value', $values, $USER->get('id'));
    }
    catch (Exception $e) {
        $goto = get_config('wwwroot') . '/artefact/booklet/tomes.php';
        $SESSION->add_error_msg(get_string('tomesavefailed', 'artefact.booklet'));
        redirect($goto);
    }
    redirect($goto);
}

class ArtefactTypeFrame extends ArtefactTypebooklet {
    /* classe pour pieforms et fonctions JS propres a un tab et ses frames */
    public static function is_singular() { return true; }
    public static function get_form($idtab) {
        $tab = get_record('artefact_booklet_tab', 'id', $idtab);
        $tabname = pieform(array(
            'name'        => 'tabname',
            'plugintype'  => 'artefact',
            'successcallback' => 'tabname_submit',
            'pluginname'  => 'booklet',
            'method'      => 'post',
            'renderer'      => 'table',
            'elements'    => array(
                'name' => array(
                    'type' => 'text',
                    'title' => get_string('tabname', 'artefact.booklet'),
                    'defaultvalue' => ((!empty($tab)) ? $tab->title : NULL),
                ),
                'help' => array(
                    'type'=>'wysiwyg',
                    'rows' => 16,
                    'cols' => 60,
                    'title' => get_string('helptab', 'artefact.booklet'),
                    'defaultvalue' => ((!empty($tab)) ? $tab->help : NULL),
                ),
                'save' => array(
                    'type' => 'submitcancel',
                    'value' => array(get_string('savetab', 'artefact.booklet'),
                                     get_string('cancelframe', 'artefact.booklet')),
                    'goto' => get_config('wwwroot') . '/artefact/booklet/tabs.php?id='.$tab->idtome,
                ),
                'id' => array(
                    'type' => 'hidden',
                    'value' => $idtab,
                )
            ),
            'autofocus'  => false,
        ));
        $addframe = pieform(array(
            'name'        => 'addframe',
            'plugintype'  => 'artefact',
            'successcallback' => 'addframe_submit',
            'pluginname'  => 'booklet',
            'method'      => 'post',
            'renderer'      => 'oneline',
            'elements'    => array(
                'save' => array(
                    'type' => 'submit',
                    'value' => get_string('addframe', 'artefact.booklet'),
                ),
                'id' => array(
                    'type' => 'hidden',
                    'value' => $idtab,
                )
            ),
            'autofocus'  => false,
        ));
        $visuaform = pieform(array(
                'name' => 'visuaform',
                'plugintype'  => 'artefact',
                'pluginname'  => 'booklet',
                'successcallback' => 'visualizetome_submit',
                'method'      => 'post',
                'renderer'      => 'oneline',
                'elements'    => array(
                    'save' => array(
                        'type' => 'submit',
                        'value' => get_string('visualizetab', 'artefact.booklet'),
                    ),
                    'idtab' => array(
                        'type' => 'hidden',
                        'value' => $idtab,
                    )
                ),
            ));
        $framesform['addframe'] = $addframe;
        $framesform['tabname'] = $tabname;
        $framesform['visuaform'] = $visuaform;
        return $framesform;
    }

    public static function get_tablerenderer_js() {

        return "
                'title',
                'list',
                ";
    }

    public static function get_editdel_js() {
        $editstr = get_string('edit','artefact.booklet');
        $delstr = get_string('del','artefact.booklet');
        $js = <<<EOF
          function (r, d) {
            var editlink = A({'href': 'objects.php?id=' + r.id, 'title': '{$editstr}'}, IMG({'src': config.theme['images/btn_edit.png'], 'alt':'{$editstr}'}));
            var dellink = A({'href': '', 'title': '{$delstr}'}, IMG({'src': config.theme['images/btn_deleteremove.png'], 'alt': '[x]'}));
            connect(dellink, 'onclick', function (e) {
                e.stop();
                return deleteComposite(d.type, r.id);
            });
            return TD({'class':'right'}, null, editlink, ' ', dellink);
        }
    ]
);
EOF;
        return $js;
    }

    public static function ensure_composite_value($values, $owner) {
        $compositetype = 'frame';
        $count = count_records('artefact_booklet_frame', 'idtab', $values['id']);
        $val = array(
            'id' => '',
            'title' => '',
            'idtab' => $values['id'],
            'displayorder' => $count + 1
        );

        $table = 'artefact_booklet_frame';
        $id = insert_record($table, (object)$val, 'id', true);
        $goto = get_config('wwwroot') . '/artefact/booklet/objects.php?id=' . $id;
        return $goto;
    }
}

function tabname_submit(Pieform $form, $values) {
    $tab = get_record('artefact_booklet_tab', 'id', $values['id']);
    $tab->title = $values['name'];
    $tab->help = $values['help'];
    update_record('artefact_booklet_tab', $tab);
    // $goto = get_config('wwwroot') . '/artefact/booklet/tabs.php?id='.$tab->idtome;
    $goto = get_config('wwwroot') . '/artefact/booklet/frames.php?id=' . $tab->id;
    redirect($goto);
}

function addframe_submit(Pieform $form, $values) {
    global $USER;
    try {
        $goto = call_static_method('ArtefactTypeframe',
            'ensure_composite_value', $values, $USER->get('id'));
    }
    catch (Exception $e) {
        $goto = get_config('wwwroot') . '/artefact/booklet/tab.php';
        $SESSION->add_error_msg(get_string('tabsavefailed', 'artefact.booklet'));
        redirect($goto);
    }
    redirect($goto);
}

class ArtefactTypeObject extends ArtefactTypebooklet {
    /* classe pour pieforms et fonctions JS propres a un objet */
    public static function is_singular() { return true; }
    public static function get_form($idframe) {
        $frame = get_record('artefact_booklet_frame', 'id', $idframe);
        $framevide = count_records('artefact_booklet_object', 'idframe', $idframe)==0;
        $framename = pieform(array(
            'name'        => 'framename',
            'plugintype'  => 'artefact',
            'successcallback' => 'framename_submit',
            'pluginname'  => 'booklet',
            'method'      => 'post',
            'renderer'      => 'table',
            'elements'    => array(
                'name' => array(
                    'type' => 'text',
                    'title' => get_string('framename', 'artefact.booklet'),
                    'defaultvalue' => ((!empty($frame)) ? $frame->title : NULL),
                    'help' => true,
                ),
                'list' => array(
                    'type' => 'checkbox',
                    'title' => get_string('islist', 'artefact.booklet'),
                    'help' => true,
                    'defaultvalue' => ((!empty($frame)) ? $frame->list : NULL)
                ),
                'help' => array(
                    'type'=>'wysiwyg',
                    'rows' => 16,
                    'cols' => 60,
                    'title' => get_string('helpframe', 'artefact.booklet'),
                    'defaultvalue' => ((!empty($frame)) ? $frame->help : NULL),
                ),
                'save' => array(
                    'type' => 'submitcancel',
                    'value' => array(get_string('saveframe', 'artefact.booklet'),
                                     get_string('cancelobject', 'artefact.booklet')),
                    'goto' => get_config('wwwroot') . '/artefact/booklet/frames.php?id='.$frame->idtab,
                ),
                'id' => array(
                    'type' => 'hidden',
                    'value' => $idframe,
                )
            ),
            'autofocus'  => false,
        ));
        $addobject = pieform(array(
            'name'        => 'addobject',
            'plugintype'  => 'artefact',
            'successcallback' => 'addobject_submit',
            'pluginname'  => 'booklet',
            'method'      => 'post',
            'renderer'      => 'oneline',
            'elements'    => array(
                'typefield' => array(
                    'type' => 'select',
                    'options' => ((($frame->list == 0) && $framevide) ? array(
                        'longtext' => get_string('longtext', 'artefact.booklet'),
                        'shorttext' => get_string('shorttext', 'artefact.booklet'),
                        'htmltext' => get_string('htmltext', 'artefact.booklet'),
                        'area' => get_string('area', 'artefact.booklet'),
                        'radio' => get_string('radio', 'artefact.booklet'),
                        'checkbox' => get_string('checkbox', 'artefact.booklet'),
                        'date' => get_string('date', 'artefact.booklet'),
                        'synthesis' => get_string('synthesis', 'artefact.booklet'),
                        'attachedfiles' => get_string('attachedfiles', 'artefact.booklet'),
                    ) : array(
                        'longtext' => get_string('longtext', 'artefact.booklet'),
                        'shorttext' => get_string('shorttext', 'artefact.booklet'),
                        'htmltext' => get_string('htmltext', 'artefact.booklet'),
                        'area' => get_string('area', 'artefact.booklet'),
                        'radio' => get_string('radio', 'artefact.booklet'),
                        'checkbox' => get_string('checkbox', 'artefact.booklet'),
                        'date' => get_string('date', 'artefact.booklet'),
                        'attachedfiles' => get_string('attachedfiles', 'artefact.booklet'),
                    )),
                    'title' => get_string('typefield', 'artefact.booklet'),
                ),
                'save' => array(
                    'type' => 'submit',
                    'value' => get_string('addobject', 'artefact.booklet'),
                ),
                'id' => array(
                    'type' => 'hidden',
                    'value' => $idframe,
                )
            ),
            'autofocus'  => false,
        ));
        $visuaform = pieform(array(
                'name' => 'visuaform',
                'plugintype'  => 'artefact',
                'pluginname'  => 'booklet',
                'successcallback' => 'visualizetome_submit',
                'method'      => 'post',
                'renderer'      => 'oneline',
                'elements'    => array(
                    'save' => array(
                        'type' => 'submit',
                        'value' => get_string('visualizetab', 'artefact.booklet'),
                    ),
                    'idtab' => array(
                      'type' => 'hidden',
                      'value' => $frame->idtab,
                    )
                ),
            ));
        $objectsform['addobject'] = $addobject;
        $objectsform['framename'] = $framename;
        $objectsform['visuaform'] = $visuaform;
        return $objectsform;
    }

    public static function get_tablerenderer_js() {
        return "
                'title',
                'type',
                'name',
                ";
    }

    public static function get_editdel_js() {
        $editstr = get_string('edit','artefact.booklet');
        $delstr = get_string('del','artefact.booklet');
        $js = <<<EOF
          function (r, d) {
            var editlink = A({'href': 'options.php?id=' + r.id, 'title': '{$editstr}'}, IMG({'src': config.theme['images/btn_edit.png'], 'alt':'{$editstr}'}));
            var dellink = A({'href': '', 'title': '{$delstr}'}, IMG({'src': config.theme['images/btn_deleteremove.png'], 'alt': '[x]'}));
            connect(dellink, 'onclick', function (e) {
                e.stop();
                return deleteComposite(d.type, r.id);
            });
            return TD({'class':'right'}, null, editlink, ' ', dellink);
        }
    ]
);
EOF;
         return $js;
    }

    public static function ensure_composite_value($values, $owner) {
        $compositetype = 'object';
        $count = count_records('artefact_booklet_object', 'idframe', $values['id']);
        $val = array(
            'id' => '',
            'title' => '',
            'idframe' => $values['id'],
            'type' => $values['typefield'],
            'displayorder' => $count + 1
        );
        $table = 'artefact_booklet_object';
        $id = insert_record($table, (object)$val, 'id', true);
        $goto = get_config('wwwroot') . '/artefact/booklet/options.php?id='.$id;
        return $goto;
    }
}

function framename_submit(Pieform $form, $values) {
    $frame = get_record('artefact_booklet_frame', 'id', $values['id']);
    $frame->title = $values['name'];
    $frame->help = $values['help'];
    $frame->list = (!empty($values['list']) ? 1 : 0);
    update_record('artefact_booklet_frame', $frame);
    // $goto = get_config('wwwroot') . '/artefact/booklet/frames.php?id='.$frame->idtab;
    $goto = get_config('wwwroot') . '/artefact/booklet/objects.php?id=' . $frame->id;
    redirect($goto);
}

function addobject_submit(Pieform $form, $values) {
    global $USER;
    try {
        $goto = call_static_method('ArtefactTypeObject',
            'ensure_composite_value', $values, $USER->get('id'));
    }
    catch (Exception $e) {
        $goto = get_config('wwwroot') . '/artefact/booklet/frames.php';
        $SESSION->add_error_msg(get_string('framesavefailed', 'artefact.booklet'));
        redirect($goto);
    }
    redirect($goto);
}

class ArtefactTypeOption extends ArtefactTypebooklet {
    /* classe pour pieforms et fonctions JS propres a une option */
    public static function is_singular() { return true; }
    public static function get_editdel_js() {
        $delstr = get_string('del','artefact.booklet');
        $js = <<<EOF
          function (r, d) {
            var dellink = A({'href': '', 'title': '{$delstr}'}, IMG({'src': config.theme['images/btn_deleteremove.png'], 'alt': '[x]'}));
            connect(dellink, 'onclick', function (e) {
                e.stop();
                return deleteComposite(d.type, r.id);
            });
            return TD({'class':'right'}, dellink);
        }
    ]
);
EOF;
         return $js;
    }

    public static function get_form($idobject) {
        $object = get_record('artefact_booklet_object', 'id', $idobject);
        $cform = array(
            'name' => 'modifform',
            'plugintype' => 'artefact',
            'pluginname' => 'booklet',
            'elements' => array(
                'title' => array(
                    'type' => 'text',
                    'title' => get_string('titleobject', 'artefact.booklet'),
                    'size' => 20,
                    'defaultvalue' => ((!empty($object)) ? $object->title : NULL),
                ),
                'name' => array(
                    'type' => 'text',
                    'title' => get_string('nameobject', 'artefact.booklet'),
                    'size' => 20,
                    'defaultvalue' => ((!empty($object)) ? $object->name : NULL),
                    'rules' => array(
                        'required' => true,
                    ),
                    'help' => true
                ),
                'help' => array(
                    'type'=>'wysiwyg',
                    'rows' => 20,
                    'cols' => 60,
                    'title' => get_string('helpobject', 'artefact.booklet'),
                        'defaultvalue' => ((!empty($object)) ? $object->help : NULL),
                ),
                'submit' => array(
                    'type' => 'submitcancel',
                    'value' => array(get_string('saveobject', 'artefact.booklet'),
                                     get_string('canceloption', 'artefact.booklet')),
                    'goto' => get_config('wwwroot') . '/artefact/booklet/objects.php?id='.$object->idframe,
                ),
                'compositetype' => array(
                    'type' => 'hidden',
                    'value' => $object->type,
                ),
                'id' => array(
                    'type' => 'hidden',
                    'value' => $idobject
                )
            ),
            'successcallback' => 'objectname_submit',
        );
        if ($object->type == 'radio') {
            $choice = array(
                'name' => 'radiochoice',
                'plugintype' => 'artefact',
                'pluginname' => 'booklet',
                'successcallback' => 'addchoice_submit',
                'renderer' => 'oneline',
                'elements' => array(
                    'option' => array(
                        'type' => 'text',
                        'title' => get_string('choice', 'artefact.booklet'),
                        'size' => 20
                    ),
                    'add' => array(
                        'type' => 'submit',
                        'value' => get_string('addchoice', 'artefact.booklet'),
                    ),
                    'idobject' => array(
                        'type' => 'hidden',
                        'value' => $idobject
                    )
                ),
            );
            $compositeform['choice'] = pieform($choice);
        }
        else if ($object->type == 'synthesis') {
            $sql = "SELECT abo.title, abo.name, abo.type
                     FROM {artefact_booklet_object} abo
                     JOIN {artefact_booklet_frame} abf ON abo.idframe = abf.id
                     WHERE abf.idtab = (SELECT fr.idtab
                                        FROM {artefact_booklet_object} obj
                                        JOIN {artefact_booklet_frame} fr ON obj.idframe = fr.id
                                        WHERE obj.id = ?) ";
            $items = get_records_sql_array($sql, array($idobject));
            $options = array();
            foreach ($items as $item) {
                if ($item->type != 'synthesis') {
                    $options[$item->name] = $item->title;
                }
            }
            $choice = array(
                'name' => 'synthesisfield',
                'plugintype' => 'artefact',
                'pluginname' => 'booklet',
                'successcallback' => 'addfield_submit',
                'renderer' => 'oneline',
                'elements' => array(
                    'option' => array(
                        'type' => 'select',
                        'options' => $options,
                        'title' => get_string('namefield', 'artefact.booklet'),
                    ),
                    'add' => array(
                        'type' => 'submit',
                        'value' => get_string('addfield', 'artefact.booklet'),
                    ),
                    'idobject' => array(
                        'type' => 'hidden',
                        'value' => $idobject
                    )
                ),
            );
            $compositeform['choice'] = pieform($choice);
        }
        $frame = get_record('artefact_booklet_frame', 'id', $object->idframe);
        $visuaform = pieform(array(
            'name' => 'visuaform',
            'plugintype'  => 'artefact',
            'pluginname'  => 'booklet',
            'successcallback' => 'visualizetome_submit',
            'method'      => 'post',
            'renderer'      => 'oneline',
            'elements'    => array(
                'save' => array(
                    'type' => 'submit',
                    'value' => get_string('visualizetab', 'artefact.booklet'),
                ),
                'idtab' => array(
                    'type' => 'hidden',
                    'value' => $frame->idtab,
                )
            ),
        ));
        $compositeform['form'] = pieform($cform);
        $compositeform['visuaform'] = $visuaform;
        return $compositeform;
    }
}

function objectname_submit(Pieform $form, $values){
    global $_SESSION;
    $object = get_record('artefact_booklet_object', 'id', $values['id']);
    $nameexist = get_record('artefact_booklet_object', 'name', $values['name']);
    if ($nameexist != false && $nameexist->id != $values['id']) {
        $form->reply(PIEFORM_ERR, array('message'=>get_string('objectsavefailed', 'artefact.booklet'),
                                        'goto' => $_SERVER['HTTP_REFERER']));
    }
    $object->title = $values['title'];
    $object->name = $values['name'];
    $object->help = $values['help'];
    update_record('artefact_booklet_object', $object);
    // $goto = get_config('wwwroot') . '/artefact/booklet/objects.php?id='.$object->idframe;
    $goto = get_config('wwwroot') . '/artefact/booklet/options.php?id=' . $object->id;
    redirect($goto);
}

function addfield_submit(Pieform $form, $values) {
    global $_SERVER;
    try {
        db_begin();
        $data = new stdClass;
        $data->idobject = $values['idobject'];
        $temp = get_record('artefact_booklet_object', 'name', $values['option']);
        $data->idobjectlinked = $temp->id;
        if (count_records('artefact_booklet_synthesis', 'idobject', $values['idobject'], 'idobjectlinked', $temp->id) == 0) {
            insert_record('artefact_booklet_synthesis', $data);
        }
        else {
            $errors = get_string('addfieldsavefailed', 'artefact.booklet');
        }
        db_commit();
    }
    catch(Exception $e) {
         $errors = $e;
    }
    if (empty($errors)) {
        $form->reply(PIEFORM_OK, array('message'=>'Ca marche!', 'goto' => $_SERVER['HTTP_REFERER']));
    }
    else {
        $form->reply(PIEFORM_ERR, array('message'=>$errors, 'goto' => $_SERVER['HTTP_REFERER']));
    }
}

function addchoice_submit(Pieform $form, $values) {
    global $_SERVER;
    try {
        db_begin();
        $data = new StdClass;
        $data->option = $values['option'];
        $data->id = null;
        $temp = $form->get_element('idobject');
        $data->idobject = $temp['value'];
        insert_record('artefact_booklet_radio', $data);
        db_commit();
    }
    catch(Exception $e) {
        $errors = $e;
    }
    if (empty($errors)) {
        $form->reply(PIEFORM_OK, array('message'=>'Ca marche!', 'goto' => $_SERVER['HTTP_REFERER']));
    }
    else {
        $message = '';
        $message .= 'fail : '.$errors."\n";
        $form->reply(PIEFORM_ERR, array('message'=>$message, 'goto' => $_SERVER['HTTP_REFERER']));
    }
}

class ArtefactTypeRadio extends ArtefactTypeOption {
    /* classe pour pieforms et fonctions JS propres a un bouton radio */
    public static function is_singular() { return true; }
    public static function get_tablerenderer_js() {
        return "
                'option',
                ";
    }
}

class ArtefactTypeSynthesis extends ArtefactTypeOption {
    /* classe pour pieforms et fonctions JS propres a une synthese */
    public static function is_singular() { return true; }
    public static function get_tablerenderer_js() {
        return "
                'title',
                ";
    }
}

/* Visualisation d'un tome en cours de modification */
function visualizetome_submit (Pieform $form, $values) {
    if (isset($values['idtome'])) {
        $options = 'tome='.$values['idtome'];
    }
    else if (isset($values['idtab'])) {
        $tab = get_record('artefact_booklet_tab', 'id', $values['idtab']);
        $options = 'tome='.$tab->idtome.'&tab='.$tab->displayorder;
    }
    $goto = get_config('wwwroot') . '/artefact/booklet/index.php?' . $options;
    redirect($goto);
}

/* classe pour pieforms et fonctions JS pour la visualisation d'un booklet */
class ArtefactTypeVisualization extends ArtefactTypebooklet {

    public function commit() {
        // le commit insere un artefact pour un cadre et ajoute dans view artefact mention de ce cadre pour tous les blockinstances concernés
        global $USER;
        parent::commit();

        $selectedtome = get_record('artefact_booklet_selectedtome', 'iduser', $USER->get('id'));
        if ($blockinstances = get_records_sql_array('
            SELECT id, "view", configdata
            FROM {block_instance}
            WHERE blocktype = \'entirebooklet\'
            AND "view" IN (
                SELECT id
                FROM {view}
                WHERE "owner" = ?)', array($this->owner))) {
            foreach ($blockinstances as $blockinstance) {
                $whereobject = (object)array(
                    'view' => $blockinstance->view,
                    'artefact' => $this->get('id'),
                    'block' => $blockinstance->id,);
                // pour savoir si le blockinstance concerne le meme livret, voir le champ note du 1er artefact de cette vue
                $arts = get_records_sql_array('
                    SELECT *
                    FROM {view_artefact} va
                    INNER JOIN {artefact} a
                    ON va.artefact = a.id
                    WHERE va.block = ? ', array($blockinstance->id));
                if ($arts[0] && $arts[0]->note == $selectedtome-> idtome) {
                    ensure_record_exists('view_artefact', $whereobject, $whereobject);
                }
            }
        }
    }
/**
 * Format liste ou format non liste
 *
 */

    public function render_self($options) {
        // Affichage d'un cadre : un champ du livret dans le blocktype, appele sur un artefact
        global $USER;
        require_once(get_config('docroot') . 'artefact/lib.php');

		// Modif JF
        $vertical=false;
        $separateur='';
        $intitules = array();
        $nbrubriques=0;
		$lastposition = array();

		$frame = get_record('artefact_booklet_frame', 'id', $this->description);
		if (!empty($frame)) {
        	$rslt = "\n<h3>".$frame->title."</h3>";

        	if ($frame-> list) { // le cadre est une liste
            	$objects = get_records_array('artefact_booklet_object', 'idframe', $frame->id, 'displayorder');

				// headers
                $pos=0;
				foreach ($objects as $object) {
                    $key=$object->id;
	            	$intitules[$key]= $object->title;
                    $lastposition[$key]=false;
            	}
                $lastposition[$key]=true;
                $nbrubriques=count($intitules);

    			$vertical = ($nbrubriques>5) ? true : false;
                $separateur=($vertical)? '</tr><tr>' : '';

            	$rslt .= "\n<fieldset>\n<table class=\"tablerenderer \">";
				if (!$vertical){
					$rslt .= "<thead>\n<tr>";
                    foreach ($objects as $object) {
                		$rslt .= "<th>". $object->title . "</th>";
					}
					$rslt .= "</tr></thead>";
				}

				// calcul du nombre d'elements de la liste
				switch ($objects[0]->type) {
                case 'longtext':
                case 'shorttext':
                case 'area':
                case 'htmltext':
                    $n = count_records('artefact_booklet_resulttext', 'idobject', $objects[0]->id, 'idowner', $this -> author);
                    $sql = "SELECT re.idrecord FROM {artefact_booklet_resulttext} re
                            JOIN {artefact_booklet_resultdisplayorder} do
                            ON (re.idrecord = do.idrecord AND re.idowner = do.idowner)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY do.displayorder";
                    $listidrecords = get_records_sql_array($sql, array($objects[0]->id, $this -> author));
                    break;
                case 'radio':
                    $n = count_records('artefact_booklet_resultradio', 'idobject', $objects[0]->id, 'idowner', $this -> author);
                    $sql = "SELECT re.idrecord FROM {artefact_booklet_resultradio} re
                            JOIN {artefact_booklet_resultdisplayorder} do
                            ON (re.idrecord = do.idrecord AND re.idowner = do.idowner)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY do.displayorder";
                    $listidrecords = get_records_sql_array($sql, array($objects[0]->id, $this -> author));
                    break;
                case 'checkbox':
                    $n = count_records('artefact_booklet_resultcheckbox', 'idobject', $objects[0]->id, 'idowner', $this -> author);
                    $sql = "SELECT re.idrecord FROM {artefact_booklet_resultcheckbox} re
                            JOIN {artefact_booklet_resultdisplayorder} do
                            ON (re.idrecord = do.idrecord AND re.idowner = do.idowner)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY do.displayorder";
                    $listidrecords = get_records_sql_array($sql, array($objects[0]->id, $this -> author));
                    break;
                case 'date':
                    $n = count_records('artefact_booklet_resultdate', 'idobject', $objects[0]->id, 'idowner', $this -> author);
                    $sql = "SELECT re.idrecord FROM {artefact_booklet_resultdate} re
                            JOIN {artefact_booklet_resultdisplayorder} do
                            ON (re.idrecord = do.idrecord AND re.idowner = do.idowner)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY do.displayorder";
                    $listidrecords = get_records_sql_array($sql, array($objects[0]->id, $this -> author));
                    break;
                case 'attachedfiles':
                    $n = count_records('artefact_booklet_resultattachedfiles', 'idobject', $objects[0]->id, 'idowner', $this -> author);
                    // TO DO : ne compter que les records ayant un idrecord different
                    break;
            	}

				// construction d'un tableau des lignes : une par élément, chaque ligne contient les valeurs de tous les objets
            	$ligne = array();
            	for ($i = 0; $i < $n; $i++) {
                	$ligne[$i] = "";
            	}
            	// pour chaque objet, on complete toutes les lignes
            	foreach ($objects as $object) {
                if ($object->type == 'longtext' || $object->type == 'shorttext' || $object->type == 'area' || $object->type == 'htmltext' || $object->type == 'synthesis') {
                    $sql = "SELECT * FROM {artefact_booklet_resulttext} re
                            JOIN {artefact_booklet_resultdisplayorder} do
                            ON (re.idrecord = do.idrecord AND re.idowner = do.idowner)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY do.displayorder";
                    $txts = get_records_sql_array($sql, array($object->id, $this -> author));
                    $i = 0;
                    foreach ($txts as $txt) {

						if ($vertical){
                            $ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
						}
   						$ligne[$i].= "<td>". $txt->value . "</td>";
						if ($vertical){
							if (!$lastposition[$object->id]){
								$ligne[$i].=$separateur;
							}
							else{
                                $ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
							}
						}
                        $i++ ;
                    }
                }
                else if ($object->type == 'radio') {
                    $sql = "SELECT * FROM {artefact_booklet_resultradio} re
                            JOIN {artefact_booklet_resultdisplayorder} do
                            ON (re.idrecord = do.idrecord AND re.idowner = do.idowner)
                            JOIN {artefact_booklet_radio} ra
                            ON (ra.id = re.idchoice)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY do.displayorder";
                    $radios = get_records_sql_array($sql, array($object->id, $this -> author));
                    $i = 0;
					if (!empty($radios)){
                    	foreach ($radios as $radio){
							if ($vertical){
        	                    $ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
							}
                        	$ligne[$i].= "<td>".$radio->option . "</td>";
							if ($vertical){
								if (!$lastposition[$object->id]){
									$ligne[$i].=$separateur;
								}
								else{
                                	$$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
								}
							}
                        	$i++ ;
                    	}
					}
                }
                else if ($object->type == 'checkbox') {
                    $sql = "SELECT * FROM {artefact_booklet_resultcheckbox} re
                            JOIN {artefact_booklet_resultdisplayorder} do
                            ON (re.idrecord = do.idrecord AND re.idowner = do.idowner)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY do.displayorder";
                    $checkboxes = get_records_sql_array($sql, array($object->id, $this -> author));
                    $i = 0;
                    foreach ($checkboxes as $checkbox) {
						if ($vertical){
       	                    $ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
						}
                        $ligne[$i].= "<td>".($checkbox->value ? get_string('true', 'artefact.booklet')  : get_string('false', 'artefact.booklet') ) . "</td>";
						if ($vertical){
							if (!$lastposition[$object->id]){
								$ligne[$i].=$separateur;
							}
							else{
                                $ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
							}
						}
                        $i++ ;
                    }
                }
                else if ($object->type == 'date') {
                    $sql = "SELECT * FROM {artefact_booklet_resultdate} re
                            JOIN {artefact_booklet_resultdisplayorder} do
                            ON (re.idrecord = do.idrecord AND re.idowner = do.idowner)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY do.displayorder";
                    $dates = get_records_sql_array($sql, array($object->id, $this -> author));
                    $i = 0;
                    foreach ($dates as $date) {
						if ($vertical){
       	                    $ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
						}
                        $ligne[$i].= "<td>".format_date(strtotime($date->value), 'strftimedate') . "</td>";
						if ($vertical){
							if (!$lastposition[$object->id]){
								$ligne[$i].=$separateur;
							}
							else{
                                $ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
							}
						}
                        $i++ ;
                    }
                }
                else if ($object->type == 'attachedfiles') {
                    $sql = "SELECT * FROM {artefact_booklet_resultattachedfiles} re
                            JOIN {artefact_booklet_resultdisplayorder} do
                            ON (re.idrecord = do.idrecord AND re.idowner = do.idowner)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY do.displayorder";
                    $attachedfiles = get_records_sql_array($sql, array($object->id, $this -> author));
                    for ($i = 0; $i < $n; $i++) {
						if ($vertical){
                            $ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
						}
                        $ligne[$i].= "<td><table>";
                    }
                    if (!empty($attachedfiles)){
                    	foreach ($attachedfiles as $attachedfile) {
                        	$f = artefact_instance_from_id($attachedfile->artefact);
                        	$j = 0;
                        	foreach ($listidrecords as $idrc) {
                            	if ($attachedfile->idrecord == $idrc->idrecord) {
                               		$i = $j;
                            	}
                            	$j++;
                        	}
                        	$ligne[$i].= "<tr><td><img src=" .
                        		$f->get_icon(array('id' => $attachedfile->artefact, 'viewid' => isset($options['viewid']) ? $options['viewid'] : 0)) .
                            	" alt=''></td><td><a href=" .
                            	get_config('wwwroot') . "artefact/file/download.php?file=" . $attachedfile->artefact .
                            	">" . $f->title . "</a> (" . $f->describe_size() . ")" . $f->description . "</td></tr>";
                    	}
					}
                    for ($i = 0; $i < $n; $i++) {
						$ligne[$i] .= "</table></td>";
						if ($vertical){
							if (!$lastposition[$object->id]){
								$ligne[$i].=$separateur;
							}
							else{
                                $ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
							}
						}
                    }
                	}
            	}
            	for ($i = 0; $i < $n; $i++) {
                	$rslt .= "\n<tr>" . $ligne[$i] . "</tr>";
            	}
				$rslt .= "\n</table>\n</fieldset> ";
        }
        // le cadre n'est pas une liste
        else {
            $rslt .= "\n<table class=\"resumepersonalinfo\">";
            $objects = get_records_array('artefact_booklet_object', 'idframe', $frame->id, 'displayorder');
			if (!empty($objects)) {
            foreach ($objects as $object) {
                if ($object->type == 'longtext' || $object->type == 'shorttext' || $object->type == 'area' || $object->type == 'htmltext' || $object->type == 'synthesis') {
                    $val = get_record('artefact_booklet_resulttext', 'idowner', $this -> author, 'idobject', $object->id);
                    if ($val && $val -> value) {
                        $rslt .= "\n<tr><th>". $object -> title . "</th>";
                        $rslt .= "<td>";
                        $rslt .= $val -> value;
                    }
                }
                else if ($object->type == 'radio') {
                    $radio = get_record('artefact_booklet_resultradio', 'idowner', $this -> author, 'idobject', $object->id);
                    if ($radio && $radio->idchoice) {
                        $val = get_record('artefact_booklet_radio', 'id', $radio->idchoice);
                        $rslt .= "\n<tr><th>". $object -> title . "</th>";
                        $rslt .= "<td>";
                        $rslt .= $val -> option;
                    }
                }
                else if ($object->type == 'checkbox') {
                    $coche = get_record('artefact_booklet_resultcheckbox', 'idowner', $this -> author, 'idobject', $object->id);
                    if ($coche) {
                        $rslt .= "\n<tr><th>". $object -> title . "</th>";
                        $rslt .= "<td>";
                        $rslt .= ($coche->value ? get_string('true', 'artefact.booklet')  : get_string('false', 'artefact.booklet') );
                    }
                }
                else if ($object->type == 'date') {
                    $date = get_record('artefact_booklet_resultdate', 'idowner', $this -> author, 'idobject', $object->id);
                    if ($date) {
                        $rslt .= "\n<tr><th>". $object -> title . "</th>";
                        $rslt .= "<td>";
                        $rslt .= format_date(strtotime($date->value), 'strftimedate') ;
                    }
                }
                else if ($object->type == 'attachedfiles') {
                    $sql = "SELECT * FROM {artefact_booklet_resultattachedfiles}
                            WHERE idobject = ?
                            AND idowner = ?";
                    $attachedfiles = get_records_sql_array($sql, array($object->id, $this -> author));
                    $rslt .= "\n<tr><th>". $object -> title . "</th>";
                    $rslt .= "<td> <table>";
                    if ($attachedfiles) foreach ($attachedfiles as $attachedfile) {
                        $f = artefact_instance_from_id($attachedfile->artefact);
                        $rslt .= "<tr><td class='iconcell'><img src=" .
                        $f->get_icon(array('id' => $attachedfile->artefact, 'viewid' => isset($options['viewid']) ? $options['viewid'] : 0)) .
                        " alt=''></td> <td><a href=" .
                        get_config('wwwroot') . "artefact/file/download.php?file=" . $attachedfile->artefact .
                        ">" . $f->title . "</a> (" . $f->describe_size() . ")" . $f->description . "</td></tr>";
                    }
                    $rslt .= "</table>";
                }
                $rslt .= "</td></tr>";
            }
			}
            $rslt .= "\n</table>";
        }
		//
		}
        // return array('html' => clean_html($rslt));
        if (isset($rslt)){
			return array('html' => $rslt);
		}
    }

    public static function is_singular() {
        return true;
    }

    public static function submenu_items($idtome) {
        global $USER;
        $tabs = get_records_array('artefact_booklet_tab', 'idtome', $idtome, 'displayorder');
        // liste des tabs du tome tries par displayorder
        $selectedtome = get_record('artefact_booklet_selectedtome', 'iduser', $USER->get('id'));
        $opt = null;
        if ($selectedtome && $idtome != $selectedtome->idtome) {
        // Cas ou un designer regarde un tome qui n'est pas celui qu'il a selectionné
            $opt = "&tome=" . $idtome;
        }
        $items = array();
        if ($tabs) {
            foreach ($tabs as $tab) {
                // cree un tableau : displayorder -> tableau (page url title)
                $items[$tab->displayorder] = array(
                    'page'    => $tab->displayorder,
                    'url'     => 'artefact/booklet/index.php?tab=' . $tab->displayorder . $opt,
                    'title'    => $tab->title,
                );
            }
        }
        if (defined('booklet_SUBPAGE') && isset($items[booklet_SUBPAGE])) {
        // pour différencier 1er et second appel depuis index.php
            $items[booklet_SUBPAGE]['selected'] = true;
        }
        return $items;
        // renvoit le tableau des tabs avec au 2nd appel mention de celui qui est sélectionné
    }

    public static function get_form($idtome, $idtab, $idmodifliste = null, $browse) {
        // idmodifliste est l'index dans artefact_booklet_resulttext
        global $USER;
        require_once(get_config('libroot') . 'pieforms/pieform.php');
        if (!is_null($idmodifliste)) {
            $record = get_record('artefact_booklet_resulttext', 'id', $idmodifliste);
            $objmodif = get_record('artefact_booklet_object', 'id', $record->idobject);
        }
        else {
            $record = null;
            $objmodif = null;
        }
        if (!$tome = get_record('artefact_booklet_tome', 'id', $idtome)) {
            return null;
        }
        foreach (get_records_array('artefact_booklet_tab', 'idtome', $idtome, 'displayorder') as $item) {
            // liste des tabs du tome triés par displayorder
            if ($item->displayorder == $idtab) {
                // parcours pour trouver le tab dont le displayorder est idtab
                $tab = $item;
            }
        }
        $frames = get_records_array('artefact_booklet_frame', 'idtab', $tab->id, 'displayorder');
        // liste des frames du tab ordonnés par displayorder
        $elements = array();
        $bookletform = array();
        $bookletform["entete"] = $tab->help;
        if ($frames) {
            foreach ($frames as $frame) {
                $components = array();
                $elements = null;
                $components = null;
                $pf = null;
                // Quatre conditions exclusives
                $notframelist = !$frame->list;
                $framelistnomodif = $frame->list && !$objmodif;
                $objmodifinframe = $objmodif && ($objmodif->idframe == $frame->id);
                $objmodifotherframe = $objmodif && ($objmodif->idframe != $frame->id);

                $objects = get_records_array('artefact_booklet_object', 'idframe', $frame->id, 'displayorder');
                // liste des objets du frame ordonnés par displayorder
                if ($objects) {
                    foreach ($objects as $object) {
                        $help = ($object->help != null);
                        if ($object->type == 'longtext') {
                            $val = null;
                            if ($notframelist) {
                                // ce n'est pas une liste : rechercher le contenu du champ texte
                                $sql = "SELECT * FROM {artefact_booklet_resulttext} WHERE idobject = ? AND idowner = ?";
                                $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
                                $val = $vals[0];
                            }
                            else if ($objmodifinframe) {
                                // modification d'un element de liste
                                $val = get_record('artefact_booklet_resulttext', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
                            }
                            if ($notframelist || !$objmodifotherframe) {
                                $components['lt' . $object->id] =  array(
                                    'type' => 'text',
                                    'title' => $object->title,
                                    'size' => 50,
                                    'help' => $help,
                                    'defaultvalue' => ((!empty($val)) ? $val->value : NULL),
                                );
                            }
                        }
                        else if ($object->type == 'area') {
                            $val = null;
                            if ($notframelist) {
                                $sql = "SELECT * FROM {artefact_booklet_resulttext} WHERE idobject = ? AND idowner = ?";
                                $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
                                $val = $vals[0];
                            }
                            else if ($objmodifinframe) {
                                $val = get_record('artefact_booklet_resulttext', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
                            }
                            if ($notframelist || !$objmodifotherframe) {
                                $components['ta' . $object->id] =  array(
                                    'type' => 'textarea',
                                    'rows' => 10,
                                    'cols' => 50,
                                    'title' => $object->title,
                                    'help' => $help,
                                    'resizable' => false,
                                    'defaultvalue' => ((!empty($val)) ? $val->value : NULL),
                                );
                            }
                        }
                        else if ($object->type == 'htmltext') {
                            $val = null;
                            if ($notframelist) {
                                $sql = "SELECT * FROM {artefact_booklet_resulttext} WHERE idobject = ? AND idowner = ?";
                                $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
                                $val = $vals[0];
                            }
                            else if ($objmodifinframe) {
                                $val = get_record('artefact_booklet_resulttext', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
                            }
                            if ($notframelist || !$objmodifotherframe) {
                                $components['ht' . $object->id] =  array(
                                    'type' => 'wysiwyg',
                                    'rows' => 20,
                                    'cols' => 60,
                                    'title' => $object->title,
                                    'help' => $help,
                                    'resizable' => false,
                                    'defaultvalue' => ((!empty($val)) ? $val->value : NULL),
                                    'rules' => array('maxlength' => 65536),
                               );
                            }
                        }
                        else if ($object->type == 'synthesis') {
                            $val = null;
                            if ($notframelist) {
                                $sql = "SELECT * FROM {artefact_booklet_resulttext} WHERE idobject = ? AND idowner = ?";
                                $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
                                $val = $vals[0];
                            }
                            else if ($objmodifinframe) {
                                $val = get_record('artefact_booklet_resulttext', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
                            }
                            $rec = false;
                            if (!is_null($record)) {
                                $rec = true;
                            }
                            if ($notframelist || !$objmodifotherframe) {
                                $components['ta' . $object->id] =  array(
                                    'type' => 'wysiwyg',
                                    'rows' => 20,
                                    'cols' => 60,
                                    'title' => $object->title,
                                    'help' => $help,
                                    'resizable' => false,
                                    'defaultvalue' => ((!empty($val)) ? $val->value : NULL),
                                );
                                $components['btn' . $object->id] = array(
                                    'type' => 'button',
                                    'value' => get_string('generate', 'artefact.booklet'),
                                    'onclick' => ($rec ? 'sendjsonrequest(\'compositegeneratesynthesis.php\',
                                                 {\'idsynthesis\': ' . $object->id . ', \'idrecord\': ' . $record->idrecord . '},
                                                     \'GET\',
                                                     function(data) {
                                                         location.reload(true)
                                                     },
                                                     function() {
                                                         // @todo error
                                                     })'
                                                     : 'sendjsonrequest(\'compositegeneratesynthesis.php\',
                                                     {\'idsynthesis\': ' . $object->id . '},
                                                     \'GET\',
                                                     function(data) {
                                                         location.reload(true)
                                                     },
                                                     function() {
                                                        // @todo error
                                                     })')
                                );
                            }
                        }
                        else if ($object->type == 'shorttext') {
                            $val = null;
                            if ($notframelist) {
                                $sql = "SELECT * FROM {artefact_booklet_resulttext} WHERE idobject = ? AND idowner = ?";
                                $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
                                $val = $vals[0];
                            }
                            else if ($objmodifinframe) {
                                $val = get_record('artefact_booklet_resulttext', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
                            }
                            if ($notframelist || !$objmodifotherframe) {
                                $components['st' . $object->id] =  array(
                                    'type' => 'text',
                                    'title' => $object->title,
                                    'help' => $help,
                                    'size' => 16,
                                    'defaultvalue' => ((!empty($val)) ? $val->value : NULL),
                                );
                            }
                        }
                        else if ($object->type == 'radio') {
                            $val = null;
                            if (count_records('artefact_booklet_radio', 'idobject', $object->id) != 0) {
                                $res = get_records_array('artefact_booklet_radio', 'idobject', $object->id);
                                if ($notframelist) {
                                    $sql = "SELECT * FROM {artefact_booklet_resultradio} WHERE idobject = ? AND idowner = ?";
                                    $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
                                    $val = $vals[0];
                                }
                                else if ($objmodifinframe) {
                                    $val = get_record('artefact_booklet_resultradio', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
                                }
                                $table = array();
                                foreach ($res as $value) {
                                    $table[$value->id] = $value->option;
                                }
                                if ($notframelist || !$objmodifotherframe) {
                                    $components['ra' . $object->id] = array(
                                        'type' => 'radio',
                                        'options' => $table,
                                        'help' => $help,
                                        'title' => $object->title,
                                        'defaultvalue' => ((!empty($val)) ? $val->idchoice : NULL),
                                    );
                                }
                            }
                        }
                        else if ($object->type == 'checkbox') {
                            $val = null;
                            if ($notframelist) {
                                $sql = "SELECT * FROM {artefact_booklet_resultcheckbox} WHERE idobject = ? AND idowner = ?";
                                $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
                                $val = $vals[0];
                            }
                            else if ($objmodifinframe) {
                                $val = get_record('artefact_booklet_resultcheckbox', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
                            }
                            if ($notframelist || !$objmodifotherframe) {
                                $components['cb' . $object->id] = array(
                                    'type' => 'checkbox',
                                    'help' => $help,
                                    'title' => $object->title,
                                    'defaultvalue' => ((!empty($val)) ? $val->value : NULL),
                                );
                            }
                        }
                        else if ($object->type == 'date') {
                            $val = null;
                            if ($notframelist) {
                                $sql = "SELECT * FROM {artefact_booklet_resultdate} WHERE idobject = ? AND idowner = ?";
                                $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
                                $val = $vals[0];
                            }
                            else if ($objmodifinframe) {
                                $val = get_record('artefact_booklet_resultdate', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
                            }
                            if ($notframelist || !$objmodifotherframe) {
                                $components['da' . $object->id] = array(
                                    'type' => 'calendar',
                                    'caloptions' => array(
                                        'showsTime' => false,
                                        'ifFormat' => get_string('strfdateofbirth', 'langconfig')
                                    ),
                                    'defaultvalue' => ((!empty($val)) ? strtotime($val->value) : time()),
                                    'title' => $object->title,
                                    'description' => get_string('dateofbirthformatguide'),
                                );
                            }
                        }
                        else if ($object->type == 'attachedfiles') {
                            $vals = array();
                            if ($notframelist) {
                                $vals = get_column('artefact_booklet_resultattachedfiles', 'artefact',  'idobject', $object->id, 'idowner', $USER->get('id'));
                            }
                            else if ($objmodifinframe) {
                                $vals = get_column('artefact_booklet_resultattachedfiles', 'artefact',  'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
                            }
                            if ($notframelist || !$objmodifotherframe) {
                                $components['af' . $object->id] =  array(
                                    'type' => 'filebrowser',
                                    'title' => $object->title,
                                    'help' => $help,
                                    'folder' => 0,
                                    'highlight' => null,
                                    'browse' => $browse,
                                    'page' => get_config('wwwroot') . 'artefact/booklet/index.php?' . 'tome=' . $tab->idtome . '&tab=' . $tab->displayorder . '&browse=1',
                                    'browsehelp' => 'browsemyfiles',
                                    'config' => array(
                                        'upload' => true,
                                        'uploadagreement' => get_config_plugin('artefact', 'file', 'uploadagreement'),
                                        'resizeonuploaduseroption' => get_config_plugin('artefact', 'file', 'resizeonuploaduseroption'),
                                        'resizeonuploaduserdefault' => $USER->get_account_preference('resizeonuploaduserdefault'),
                                        'createfolder' => false,
                                        'edit' => false,
                                        'select' => true,
                                    ), 
                                    'defaultvalue' => $vals,
                                    'selectlistcallback' => 'artefact_get_records_by_id',
                                    // 'selectcallback' => 'add_attachment',
                                    // 'unselectcallback' => 'delete_attachment',
                                );
                            }
                        }
                    } // fin de foreach objects
                }
                if (count($components) != 0 && $notframelist) {
                    $elements['idtab'] = array(
                        'type' => 'hidden',
                        'value' => $idtab
                    );
                    $elements['idtome'] = array(
                        'type' => 'hidden',
                        'value' => $idtome
                    );
                    $components['save' . $frame->id] = array(
                        'type' => 'submit',
                        'value' => get_string('valid', 'artefact.booklet'),
                    );
                    $elements['list'] = array(
                        'type' => 'hidden',
                        'value' => false
                    );
                    $elements[$frame->id] = array(
                        'type' => 'fieldset',
                        'legend' => $frame->title,
                        'help' => ($frame->help != null),
                        'elements' => $components
                    );
                }
                if ($frame->list) {
                    if ($objmodif) {
                         $components['idrecord'] = array(
                            'type' => 'hidden',
                            'value' => $record->idrecord
                         );
                    }
                    $components['idtab'] = array(
                        'type' => 'hidden',
                        'value' => $idtab
                    );
                    $components['idtome'] = array(
                        'type' => 'hidden',
                        'value' => $idtome
                    );
                    $components['list'] = array(
                        'type' => 'hidden',
                        'value' => true
                    );
                    if ($objmodifinframe) {
                        $components['save' . $frame->id] = array(
                           'type' => 'submit',
                           'value' => get_string('modify', 'artefact.booklet'),
                        );
                    }
                    if ($framelistnomodif) {
                        $components['save' . $frame->id] = array(
                            'type' => 'submit',
                            'value' => get_string('save', 'artefact.booklet'),
                        );
                    }
                    $elements = $components;
                }
                $pf = pieform(array(
                    'name'        => 'pieform'.$frame->id,
                    'plugintype'  => 'artefact',
                    'pluginname'  => 'booklet',
                    'configdirs'  => array(get_config('libroot') . 'form/', get_config('docroot') . 'artefact/file/form/'),
                    'method'      => 'post',
                    'renderer'    => 'table',
                    'successcallback' => 'visualization_submit',
                    'elements'    => $elements,
                    'autofocus'   => false,
                ));
                if ($frame->list) {
                    if ($framelistnomodif) {
                        $pf = "<div id='pieform".$frame->id."form' class='hidden'>". $pf. "</div>" .
                              "<button id='addpieform".$frame->id."button' class='cancel' onclick='toggleCompositeForm(&quot;pieform".$frame->id."&quot;);'>".get_string('add','artefact.booklet')."</button>" ;
                    }
                    if ($objmodifinframe) {
                        $pf = $pf . "<button id='addpieform".$frame->id."button' onclick='javascript:history.back()' class='cancel'>". get_string('cancel','artefact.booklet')."</button>";
                    }

                    $objects = get_records_array('artefact_booklet_object', 'idframe', $frame->id);
                    $item = null;
                    if ($objects) {
                        foreach ($objects as $object) {
                            if ((substr($object->name, 0, 5) == 'Title' || substr($object->name, 0, 5) == 'title') &&
                                ($object->type == 'area' || $object->type == 'shorttext' || $object->type == 'longtext'
                                 || $object->type == 'synthesis' || $object->type == 'htmltext')) {
                                $item = $object;
                                break;
                            }
                        }
                    }
                    if (is_null($item)) {
                        $sql = "SELECT * FROM {artefact_booklet_object}
                                WHERE idframe = ?
                                AND displayorder = (SELECT MIN(displayorder)
                                                    FROM {artefact_booklet_object}
                                                    WHERE idframe = ?
                                                    AND (type='area'
                                                         OR type='shorttext'
                                                         OR type='longtext'
                                                         OR type='htmltext'
                                                         OR type='synthesis')
                                                    )";
                        $item = get_record_sql($sql, array($frame->id, $frame->id));
                    }
                    if ($frame->help != null) {
                        $aide = '<span class="help"><a href="" onclick="contextualHelp(&quot;pieform'.$frame->id.'&quot;,&quot;'.$frame->id.'&quot;,&quot;artefact&quot;,&quot;booklet&quot;,&quot;&quot;,&quot;&quot;,this); return false;"><img src="'.get_config('wwwroot').'/theme/raw/static/images/help.png" alt="Help" title="Help"></a></span>';
                    }
                    else {
                        $aide = null;
                    }
                    $pf = '<fieldset class="pieform-fieldset"><legend>' . $frame->title . ' ' . $aide . '</legend>
                           <table id="visualization'.$frame->id.'list" class="tablerenderer visualizationcomposite">
                               <thead>
                                   <tr>
                                       <th class="visualizationcontrols"></th>
                                       <th class="nom">' . (($item) ? $item->title : "") . '</th>
                                       <th class="visualizationcontrols"></th>
                                   </tr>
                               </thead>
                               <tbody>
                                   <tr>
                                       <td class="buttonscell"></td>
                                       <td class="toggle"></td>
                                       <td></td>
                                       <td class="buttonscell"></td>
                                   </tr>
                               </tbody>
                           </table>
                           ' . $pf . '
                           </fieldset>';
                }
                $bookletform[$frame->title] = $pf;
            } // fin de foreach frames
        }
        return $bookletform;
    }
    // fin de get_form

    public static function get_js($compositetype, $ids, $tab) {
        // genere la liste des fonctions pour chaque frame
        $js = self::get_common_js();
        foreach ($ids as $id) {
            $js .= self::get_artefacttype_js($compositetype, $id, $tab);
        }
        return $js;
    }

    public static function get_artefacttype_js($compositetype, $id, $tab) {
        // genere les fonctions js pour une frame
        global $THEME;
        $imagemoveblockup = json_encode($THEME->get_url('images/btn_moveup.png'));
        $imagemoveblockdown = json_encode($THEME->get_url('images/btn_movedown.png'));
        $upstr = get_string('moveup','artefact.booklet');
        $downstr = get_string('movedown','artefact.booklet');
        $editstr = get_string('edit','artefact.booklet');
        $delstr = get_string('del','artefact.booklet');
        $js = <<<EOF
tableRenderers.{$compositetype}{$id} = new TableRenderer(
    '{$compositetype}{$id}list',
    'composite.json.php',
    [
EOF;
        $js .= <<<EOF
        function (r, d) {
            var buttons = [];
            if (r._rownumber > 1) {
                var up = A({'href': ''}, IMG({'src': {$imagemoveblockup}, 'alt':'{$upstr}'}));
                connect(up, 'onclick', function (e) {
                    e.stop();
                    return moveComposite(d.type, r.id, r.artefact, 'up', d.id);
                });
                buttons.push(up);
            }
            if (!r._last) {
                var down = A({'href': '', 'class':'movedown'}, IMG({'src': {$imagemoveblockdown}, 'alt':'{$downstr}'}));
                connect(down, 'onclick', function (e) {
                    e.stop();
                    return moveComposite(d.type, r.id, r.artefact, 'down', d.id);
                });
                buttons.push(' ');
                buttons.push(down);
            }
            return TD({'class':'movebuttons'}, buttons);
        },
EOF;
        $js .= self::get_tablerenderer_js();
        $js .= <<<EOF
          function (r, d) {
            var editlink = A({'href': 'index.php?tab={$tab}&idmodifliste=' + r.id, 'title': '{$editstr}'}, IMG({'src': config.theme['images/btn_edit.png'], 'alt':'{$editstr}'}));
            var dellink = A({'href': '', 'title': '{$delstr}'}, IMG({'src': config.theme['images/btn_deleteremove.png'], 'alt': '[x]'}));
            connect(dellink, 'onclick', function (e) {
                e.stop();
                return deleteComposite(d.type, r.id, d.id);
            });
            return TD({'class':'right'}, null, editlink, ' ', dellink);
        }
    ]
);
EOF;
        $js .= <<<EOF
tableRenderers.{$compositetype}{$id}.type = '{$compositetype}';
tableRenderers.{$compositetype}{$id}.statevars.push('type');
tableRenderers.{$compositetype}{$id}.emptycontent = '';
tableRenderers.{$compositetype}{$id}.updateOnLoad();
tableRenderers.{$compositetype}{$id}.id = '{$id}';
tableRenderers.{$compositetype}{$id}.statevars.push('id');
EOF;
        return $js;
    }

    public static function get_common_js() {
        $cancelstr = get_string('cancel','artefact.booklet');
        $addstr = get_string('add','artefact.booklet');
        $confirmdelstr = get_string('compositedeleteconfirm','artefact.booklet');
        $js = <<<EOF
var tableRenderers = {};
function toggleCompositeForm(type) {
    var elemName = '';
    elemName = type + 'form';
    if (hasElementClass(elemName, 'hidden')) {
        removeElementClass(elemName, 'hidden');
        $('add' + type + 'button').innerHTML = '{$cancelstr}';
    }
    else {
        $('add' + type + 'button').innerHTML = '{$addstr}';
        addElementClass(elemName, 'hidden');
    }
}
function compositeSaveCallback(form, data) {
    key = form.id.substr(3);
    tableRenderers[key].doupdate();
    toggleCompositeForm(key);
    // Can't reset() the form here, because its values are what were just submitted,
    // thanks to pieforms
    forEach(form.elements, function(element) {
        if (hasElementClass(element, 'text') || hasElementClass(element, 'textarea')) {
            element.value = '';
        }
    });
}
function deleteComposite(type, id, idfr) {
    if (confirm('{$confirmdelstr}')) {
        sendjsonrequest('compositedelete.json.php',
            {'id': id, 'type': type},
            'GET',
            function(data) {
                tableRenderers[type+idfr].doupdate();
            },
            function() {
                // @todo error
            }
        );
    }
    return false;
}
function moveComposite(type, id, artefact, direction, idfr) {
    sendjsonrequest('compositemove.json.php',
        {'id': id, 'type': type, 'direction':direction},
        'GET',
        function(data) {
            tableRenderers[type+idfr].doupdate();
        },
        function() {
            // @todo error
        }
    );
    return false;
}
function contextualHelp(formName, helpName, pluginType, pluginName, page, section, ref) {
    var key;
    var target = $(formName + '_' + helpName + '_container');
    var url = config.wwwroot + 'artefact/booklet/help.php';
    var url_params = {
        'plugintype': pluginType,
        'pluginname': pluginName
    };
    var parentElement = 'messages';
    // deduce the key
    if (page) {
        key = pluginType + '/' + pluginName + '/' + page;
        url_params.page = page;
    }
    else if (section) {
        key = pluginType + '/' + pluginName + '/' + section;
        url_params.section = section;
    }
    else {
        key = pluginType + '/' + pluginName + '/' + formName + '/' + helpName;
        url_params.form = formName;
        url_params.element = helpName;
    }
    // close existing contextual help
    if (contextualHelpSelected) {
        removeElement(contextualHelpContainer);
        contextualHelpContainer = null;
        if (key == contextualHelpSelected) {
            // we're closing an already open one by clicking on the ? again
            contextualHelpSelected = null;
            contextualHelpOpened = false;
            return;
        }
        else {
            // we're closing a DIFFERENT one that's already open (we want to
            // continue and open the new one)
            contextualHelpSelected = null;
            contextualHelpOpened = false;
        }
    }
    // create and display the container
    contextualHelpContainer = DIV({
            'style': 'position: absolute;',
            'class': 'contextualHelp hidden'
        },
        IMG({'src': config.theme['images/loading.gif']})
    );
    appendChildNodes($(parentElement), contextualHelpContainer);
    var position = getElementPosition(ref);
    var dimensions = getElementDimensions(contextualHelpContainer);
    // Adjust the position. The element is moved towards the centre of the
    // screen, based on which quadrant of the screen the help icon is in
    screenDimensions = getViewportDimensions();
    if (position.x + dimensions.w < screenDimensions.w) {
        // Left of the screen - there's enough room for it
        position.x += 15;
    }
    else {
        position.x -= dimensions.w;
    }
    position.y -= 10;
    // Once it has been positioned, make it visible
    setElementPosition(contextualHelpContainer, position);
    removeElementClass(contextualHelpContainer, 'hidden');
    contextualHelpSelected = key;
    // load the content
    if (contextualHelpCache[key]) {
        buildContextualHelpBox(contextualHelpCache[key]);
        callLater(0, function() { contextualHelpOpened = true; });
        ensureHelpIsOnScreen(contextualHelpContainer, position);
    }
    else {
        if (contextualHelpDeferrable && contextualHelpDeferrable.cancel) {
            contextualHelpDeferrable.cancel();
        }
        badIE = true;
        sendjsonrequest(url, url_params, 'GET', function (data) {
            if (data.error) {
                contextualHelpCache[key] = data.message;
                replaceChildNodes(contextualHelpContainer, data.message);
            }
            else {
                contextualHelpCache[key] = data.content;
                buildContextualHelpBox(contextualHelpCache[key]);
            }
            contextualHelpOpened = true;
            ensureHelpIsOnScreen(contextualHelpContainer, position);
            processingStop();
        },
        function (error) {
            contextualHelpCache[key] = get_string('couldnotgethelp');
            contextualHelpContainer.innerHTML = contextualHelpCache[key];
            processingStop();
            contextualHelpOpened = true;
        },
        true, true);
    }
    contextualHelpContainer.focus();
}
EOF;
        $js .= self::get_showhide_composite_js();
        return $js;
    }

    static function get_showhide_composite_js() {
        return "
            function showhideComposite(r, content) {
                // get the reference for the title we just clicked on
                var titleTD = $('composite-' + r.artefact + '-' + r.id);
                var theRow = titleTD.parentNode;
                var bodyRow = $('composite-body-' + r.artefact +  '-' + r.id);
                if (bodyRow) {
                    if (hasElementClass(bodyRow, 'hidden')) {
                        removeElementClass(bodyRow, 'hidden');
                    }
                    else {
                        addElementClass(bodyRow, 'hidden');
                    }
                    return false;
                }
                // we have to actually create the dom node too
                var colspan = theRow.childNodes.length;
                var newRow = TR({'id': 'composite-body-' + r.artefact + '-' + r.id},
                    TD({'colspan': colspan}, content));
                insertSiblingNodesAfter(theRow, newRow);
            }
        ";
    }

    public static function get_tablerenderer_js() {
        return "
                'value',
                ";
    }
}

// fin de la classe : ArtefactTypeVisualization

function visualization_submit(Pieform $form, $values) {
    // appelé lors de la soumission de données d'un cadre en ajout ou modification
    // values est un vecteur qui contient les valeurs des champs du formulaire
    // quelques champs cachés permettent de transmettre le contexte
    // idrecord est transmis quand c'est une modification d'un element de liste
    // idtab est le displayorder du tab soumis
    // idtome est l'id du tome selectionné ou en visualisation

    global $USER, $SESSION, $_SERVER;
    $goto = get_config('wwwroot') . '/artefact/booklet/index.php?tab='.$values['idtab'];
    if (isset($values['idrecord'])) {
        $idrecord = $values['idrecord'];
        $newidrecord = 0;
        $modifelementlist = 1;
    }
    else {
        $idrecord = null;
        $newidrecord = 1;
        $modifelementlist = 0;
    }
    $temp = $form->get_element('list');
    $list = $temp['value'];
    $temp = $form->get_element('idtab');
    $idtab = $temp['value'];
    $temp = $form->get_element('idtome');
    $idtome = $temp['value'];
    if (!$idtab) {
        $idtab = $values['idtab'];
    }
    $sql1 = "SELECT MAX(idrecord) as ir FROM {artefact_booklet_resulttext} WHERE idowner = ?";
    $maxtext = get_record_sql($sql1, array($USER->get('id')));
    $sql2 = "SELECT MAX(idrecord) as ir FROM {artefact_booklet_resultradio} WHERE idowner = ?";
    $maxrad= get_record_sql($sql2, array($USER->get('id')));
    $sql3 = "SELECT MAX(idrecord) as ir FROM {artefact_booklet_resultcheckbox} WHERE idowner = ?";
    $maxcb = get_record_sql($sql3, array($USER->get('id')));
    $sql4 = "SELECT MAX(idrecord) as ir FROM {artefact_booklet_resultdate} WHERE idowner = ?";
    $maxda = get_record_sql($sql4, array($USER->get('id')));
    $sql5 = "SELECT MAX(idrecord) as ir FROM {artefact_booklet_resultattachedfiles} WHERE idowner = ?";
    $maxaf = get_record_sql($sql5, array($USER->get('id')));
    $max = max(array($maxcb->ir, $maxrad->ir, $maxtext->ir, $maxda->ir, $maxaf->ir)) + 1;
    settype($max, 'integer');
    // $selectedtome = get_record('artefact_booklet_selectedtome', 'iduser', $USER->get('id'));
    // $tome = get_record('artefact_booklet_tome', 'id', $selectedtome->idtome);
    $tome = get_record('artefact_booklet_tome', 'id', $idtome);
    $temp = get_records_array('artefact_booklet_object');
    foreach ($form->get_elements() as $element) {
        if ($element['type']=='radio') {
            foreach ($temp as $object) {
                if ('ra' . $object->id == $element['name']) {
                    $idobject = $object->id;
                    continue;
                }
            }
            // idobject est l'id dans artefact_booklet_object du champ $element
            if (!$modifelementlist) {
                $count = count_records('artefact_booklet_resultradio', 'idowner', $USER->get('id'), 'idobject', $idobject);
            }
            else {
                $count = count_records('artefact_booklet_resultradio', 'idowner', $USER->get('id'),
                                       'idobject', $idobject, 'idrecord', $idrecord);
            }
            $data = new StdClass;
            $data->idobject = $idobject;
            $data->idowner = $USER->get('id');
            $data->idchoice = $values[$element['name']];
            if ($idrecord) {
                $data->idrecord = $idrecord;
            }
            try {
                if ($count == 0 || ($list && !$modifelementlist)) {
                    // pas encore de valeur enregistree ou ajout d'une valeur de liste
                    if (is_null($idrecord)) {
                        $data->idrecord = $max; $idrecord=$max;
                    }
                    insert_record('artefact_booklet_resultradio', $data);
                }
                else {
                    if (!$list) {
                        // n'est pas dans une liste
                        if (!$idrecord) {
                            $obj = get_record('artefact_booklet_resultradio', 'idowner', $USER->get('id'), 'idobject', $idobject);
                            $idrecord = $obj->idrecord;
                            $newidrecord = 0;
                        }
                        update_record('artefact_booklet_resultradio', $data,
                                      array('idobject'=> $idobject, 'idowner'=> $USER->get('id')));
                    }
                    else {
                        // est dans une liste
                        update_record('artefact_booklet_resultradio', $data,
                                      array('idobject'=> $idobject, 'idowner'=> $USER->get('id'), 'idrecord'=> $idrecord));
                    }
                }
            }
            catch(Exception $e) {
                $errors['test'] = true;
                $aff = $e;
            }
        }
        else if ($element['type'] == 'text' || $element['type'] == 'textarea' || $element['type'] == 'wysiwyg') {
            foreach ($temp as $object) {
                if ('lt' . $object->id == $element['name'] || 'st' . $object->id == $element['name'] || 'ht' . $object->id == $element['name'] || 'ta' . $object->id == $element['name']) {
                    $idobject = $object->id;
                    continue;
                }
            }
            if (!$modifelementlist) {
                $count = count_records('artefact_booklet_resulttext', 'idowner', $USER->get('id'), 'idobject', $idobject);
            }
            else {
                $count = count_records('artefact_booklet_resulttext', 'idowner', $USER->get('id'),
                                       'idobject', $idobject, 'idrecord', $idrecord);
            }
            $data = new StdClass;
            $data->idobject = $idobject;
            $data->idowner = $USER->get('id');
            $data->value = $values[$element['name']];
            if ($idrecord) {
                $data->idrecord = $idrecord;
            }
            try {
                if ($count == 0 || ($list && !$modifelementlist)) {
                    // pas encore de valeur enregistree ou ajout d'une valeur de liste
                    if (is_null($idrecord)) {
                        $data->idrecord = $max;
                        $idrecord = $max;
                    }
                    insert_record('artefact_booklet_resulttext', $data);
                }
                else {
                    if (!$list) {
                        if (!$idrecord) {
                            $obj = get_record('artefact_booklet_resulttext', 'idowner', $USER->get('id'), 'idobject', $idobject);
                            $idrecord = $obj->idrecord;
                            $newidrecord = 0;
                        }
                        update_record('artefact_booklet_resulttext', $data,
                                      array('idobject'=> $idobject, 'idowner'=> $USER->get('id')));
                    }
                    else {
                        update_record('artefact_booklet_resulttext', $data,
                                      array('idobject'=> $idobject, 'idowner'=> $USER->get('id'), 'idrecord'=> $idrecord));
                    }
                }
            }
            catch(Exception $e) {
                $errors['test'] = true;
                $aff = $e;
            }
        }
        else if ($element['type'] == 'checkbox') {
            foreach ($temp as $object) {
                if ('cb' . $object->id == $element['name']) {
                    $idobject = $object->id;
                    continue;
                }
            }
            if (!$modifelementlist) {
                $count = count_records('artefact_booklet_resultcheckbox', 'idowner', $USER->get('id'), 'idobject', $idobject);
            }
            else {
                $count = count_records('artefact_booklet_resultcheckbox', 'idowner', $USER->get('id'), 'idobject', $idobject, 'idrecord', $idrecord);
            }
            $data = new StdClass;
            $data->idobject = $idobject;
            $data->idowner = $USER->get('id');
            $data->value = $values[$element['name']];
            if ($idrecord) {
                $data->idrecord = $idrecord;
            }
            try {
                if ($count == 0 || ($list && !$modifelementlist)) {
                    // pas encore de valeur enregistree ou ajout d'une valeur de liste
                    if (is_null($idrecord)) {
                        $data->idrecord = $max;
                        $idrecord = $max;
                    }
                    insert_record('artefact_booklet_resultcheckbox', $data);
                }
                else {
                    if (!$list) {
                        if (!$idrecord) {
                            $obj = get_record('artefact_booklet_resultcheckbox', 'idowner', $USER->get('id'), 'idobject', $idobject);
                            $idrecord = $obj->idrecord;
                            $newidrecord = 0;
                        }
                        update_record('artefact_booklet_resultcheckbox', $data,
                                      array('idobject'=> $idobject, 'idowner'=> $USER->get('id')));
                    }
                    else {
                        update_record('artefact_booklet_resultcheckbox', $data,
                                      array('idobject'=> $idobject, 'idowner'=> $USER->get('id'), 'idrecord'=> $idrecord));
                    }
                }
            }
            catch(Exception $e) {
                $errors['test'] = true;
                $aff = $e;
            }
        }
        else if ($element['type'] == 'calendar') {
            foreach ($temp as $object) {
                if ('da' . $object->id == $element['name']) {
                    $idobject = $object->id;
                    continue;
                }
            }
            if (!$modifelementlist) {
                $count = count_records('artefact_booklet_resultdate', 'idowner', $USER->get('id'), 'idobject', $idobject);
            }
            else {
                $count = count_records('artefact_booklet_resultdate', 'idowner', $USER->get('id'), 'idobject', $idobject, 'idrecord', $idrecord);
            }
            $data = new StdClass;
            $data->idobject = $idobject;
            $data->idowner = $USER->get('id');
            $data->value = db_format_timestamp($values[$element['name']]);
            if ($idrecord) {
                $data->idrecord = $idrecord;
            }
            try {
                if ($count == 0 || ($list && !$modifelementlist)) {
                    // pas encore de valeur enregistree ou ajout d'une valeur de liste
                    if (is_null($idrecord)) {
                        $data->idrecord = $max;
                        $idrecord=$max;
                    }
                    insert_record('artefact_booklet_resultdate', $data);
                }
                else {
                    if (!$list) {
                        if (!$idrecord) {
                            $obj = get_record('artefact_booklet_resultdate', 'idowner', $USER->get('id'), 'idobject', $idobject);
                            $idrecord = $obj->idrecord;
                            $newidrecord = 0;
                        }
                        update_record('artefact_booklet_resultdate', $data,
                                      array('idobject'=> $idobject, 'idowner'=> $USER->get('id')));
                    }
                    else {
                        update_record('artefact_booklet_resultdate', $data,
                                      array('idobject'=> $idobject, 'idowner'=> $USER->get('id'), 'idrecord'=> $idrecord));
                    }
                }
            }
            catch(Exception $e) {
                $errors['test'] = true;
                $aff = $e;
            }
        }
        else if ($element['type'] == 'filebrowser') {
            foreach ($temp as $object) {
                if ('af'.$object->id == $element['name']) {
                    $idobject = $object->id;
                    continue;
                }
            }
            if (!$modifelementlist) {
                $count = count_records('artefact_booklet_resultattachedfiles', 'idowner', $USER->get('id'), 'idobject', $idobject);
            }
            else {
                $count = count_records('artefact_booklet_resultattachedfiles', 'idowner', $USER->get('id'), 'idobject', $idobject, 'idrecord', $idrecord);
            }
            $data = new StdClass;
            $data->idobject = $idobject;
            $data->idowner = $USER->get('id');
            $newaf = $values[$element['name']];
            if ($idrecord) {
                $data->idrecord = $idrecord;
            }
            try {
                if ($count == 0 || ($list && !$modifelementlist)) {
                    if (is_null($idrecord)) {
                        $data->idrecord = $max;
                        $idrecord=$max;
                    }
                }
                else {
                    if (!$list) {
                        if (!$idrecord) {
                            $obj = get_records('artefact_booklet_resultattachedfiles', 'idowner', $USER->get('id'), 'idobject', $idobject);
                            $idrecord = $obj[0]->idrecord;
                            $newidrecord = 0;
                        }
                    }
                }
                delete_records('artefact_booklet_resultattachedfiles', 'idobject',$idobject , 'idowner', $USER->get('id'), 'idrecord', $idrecord);
                if ($newaf) {
                    foreach ($newaf as $n) {
                        $data->artefact = $n;
                        insert_record('artefact_booklet_resultattachedfiles', $data);
                    }
                }
            }
            catch(Exception $e) {
                $errors['test'] = true;
                $aff = $e;
            }
        }
    }
    // fin du foreach element
    $obj = get_record('artefact_booklet_object', 'id', $idobject);
    $idframe = $obj -> idframe;
    if ($newidrecord) {
        // ajout d'un idrecord
        $displayorder = new StdClass;
        $displayorder->idrecord = $max;
        $displayorder->displayorder = (($list) ? $max : 0);
        $displayorder->idowner = $USER->get('id');
        insert_record('artefact_booklet_resultdisplayorder', $displayorder);
        // insertion du frame dans la table artefact pour le blocktype : un seul artefact par frame meme si plusieurs idrecord
        $n = count_records_sql("SELECT COUNT(*) from {artefact}
                                WHERE artefacttype = 'visualization'
                                AND description = ?
                                AND note = ?
                                AND owner = ?",array($idframe,$tome->id,$USER->get('id')));
        if ($n == 0) {
            $frame = get_record('artefact_booklet_frame', 'id', $idframe);
            $classname = 'ArtefactTypeVisualization';
            $a = new $classname(0, array(
                'owner' => $USER->get('id'),
                'description' => $idframe,
                'title' => $tome->title . " - " . $frame->title,
                'note' => $tome->id,
                )
            );
            $a->commit();
        }
    }
    if (empty($errors)) {
        $SESSION->add_ok_msg(get_string('datasaved', 'artefact.booklet'));
        redirect($goto);
    }
    else {
        $SESSION->add_error_msg(get_string('datasavefailed', 'artefact.booklet'));
        redirect($goto);
    }
}
// Fin de visualization_submit
