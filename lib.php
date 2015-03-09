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

require_once(dirname(__FILE__) . '/lib_vizualisation.php');

class PluginArtefactbooklet extends PluginArtefact {
    /* Classe pour edition d'un booklet */
    public static function get_artefact_types() {
        return array(
            'tome',
            'tab',
            'frame',
            'object',
            'radio',
            'listskills',
            'synthesis',
            'visualization',
			'reference',
            'freeskills',
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

    public static function is_active() {
        return get_field('artefact_installed', 'active', 'name', 'booklet');
    }


}

class ArtefactTypebooklet extends ArtefactType {
    /* classe pour fonctions JS communes */

    /**
     * Returns a URL for an icon for the appropriate artefact
     *
     * @param array $options Options for the artefact. The array MUST have the
     *                       'id' key, representing the ID of the artefact for
     *                       which the icon is being generated. Other keys
     *                       include 'size' for a [width]x[height] version of
     *                       the icon, as opposed to the default 20x20, and
     *                       'view' for the id of the view in which the icon is
     *                       being displayed.
     * @abstract
     * @return string URL for the icon
     */
	public static function get_icon($options=null) {
        global $THEME;
        return $THEME->get_url('images/booklet.png', false, 'artefact/booklet');
	}


    /**
     * Returns a URL for an icon for the appropriate artefact
     *
     * @return string URL for the icon
     */
	public static function get_icon_checkpath($options=null) {
        global $THEME;
        return $THEME->get_url('images/btn_check.png', false, 'artefact/booklet');
	}


    /**
     * Returns a URL for an icon for the appropriate artefact
     *
     * @return string URL for the icon
     */
	public static function get_icon_showpath($options=null) {
        global $THEME;
        return $THEME->get_url('images/btn_show.png', false, 'artefact/booklet');
	}



    public static function is_singular() { return false; }
    public static function get_links($id) {}
    public function commit() { parent::commit(); }

    public static function get_js($compositetype, $id = null) {
        global $THEME;
        $imagemoveblockup   = json_encode($THEME->get_url('images/btn_moveup.png'));
        $imagemoveblockdown = json_encode($THEME->get_url('images/btn_movedown.png'));
        $imageincluded = json_encode($THEME->get_url('images/btn_included.png', false, 'artefact/booklet'));
        $imageempty = json_encode($THEME->get_url('images/btn_empty.png', false, 'artefact/booklet'));
        $imagenode = json_encode($THEME->get_url('images/btn_node.png', false, 'artefact/booklet'));
        $upstr = get_string('moveup','artefact.booklet');
        $downstr = get_string('movedown','artefact.booklet');
        $includedstr = get_string('included','artefact.booklet');
        $nodestr = get_string('node','artefact.booklet');
        $tomovestr = get_string('tomove','artefact.booklet');
		$videstr = '';

        $js = self::get_common_js();
		$js .= <<<EOF
tableRenderers.{$compositetype} = new TableRenderer(
    '{$compositetype}list',
    'composite.json.php',
    [
EOF;

        if ($compositetype=='frame') {
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
			else{
                var vide = IMG({'src': {$imageempty}, 'alt':'{$videstr}'});
                buttons.push(' ');
                buttons.push(vide);
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
			else{
                var vide = IMG({'src': {$imageempty}, 'alt':'{$videstr}'});
                buttons.push(' ');
                buttons.push(vide);
			}
            if (r.idparentframe!=0) {
                var included = A({'href': 'moveframenode.php?idframe='+r.id,'title': '{$tomovestr}'}, IMG({'src': {$imageincluded}, 'alt':'{$includedstr}'}));
				buttons.push(' ');
                buttons.push(included);
            }else{
				var node = A({'href': 'moveframenode.php?idframe='+r.id, 'title': '{$tomovestr}'}, IMG({'src': {$imagenode}, 'alt':'{$nodestr}'}));
				buttons.push(' ');
                buttons.push(node);
            }

            return TD({'class':'movebuttons'}, buttons);
        },
EOF;
        }

        else if ($compositetype!='tome' && $compositetype!='synthesis' && $compositetype!='radio') {
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
function moveNode(type, id, parentid, artefact) {
    sendjsonrequest('parentidselect.json.php',
        {'id': id, 'type': type, 'parentid': parentid},
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

        if (isset($tome->status)){
			$status= array(
                    'type' => 'hidden',
                    'value' => $tome->status,
                );
			$msg= array(
                    'type' => 'html',
                    'title' => get_string('statusmodif', 'artefact.booklet'),
                    'value' => ((!empty($tome)) ? ((!empty($tome->status)) ? '<i>'.get_string('forbidden', 'artefact.booklet').'</i>' : '<i>'.get_string('allowed', 'artefact.booklet').'</i>')  : '<i>'.get_string('allowed', 'artefact.booklet').'</i>'),
                );

		}
		else{
			$status= array(
                    'type' => 'hidden',
                    'value' => 0,
                );
			$msg= array(
                    'type' => 'html',
                    'title' => get_string('statusmodif', 'artefact.booklet'),
                    'value' => '<i>'.get_string('allowed', 'artefact.booklet').'</i>',
                );
		}

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
    public static function get_form($idtab, $idparentframe=0, $firslevelframe=true) {
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
                ),
                'idparentframe' => array(
                    'type' => 'hidden',
                    'value' => $idparentframe,
                ),
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
            var dellink = A({'href': 'frames.php?id=' + r.id, 'title': '{$delstr}'}, IMG({'src': config.theme['images/btn_deleteremove.png'], 'alt': '[x]'}));
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
            'displayorder' => $count + 1,
			'idparentframe' => $values['idparentframe'],
        );

        $table = 'artefact_booklet_frame';
        $id = insert_record($table, (object)$val, 'id', true);
        $goto = get_config('wwwroot') . '/artefact/booklet/objects.php?id=' . $id;
        return $goto;
    }


// ************************************************************************** GET MOVE FORM

    public static function get_movenodeform($idtab, $idframe) {
        $tab = get_record('artefact_booklet_tab', 'id', $idtab);

	    $itemframe = get_record('artefact_booklet_frame', 'id', $idframe);

		// Ordonner les frames selon leur frame parent et leur ordre d'affichage
		$recframes = get_records_sql_array('SELECT ar.* FROM {artefact_booklet_frame} ar WHERE ar.idtab = ? ORDER BY ar.idparentframe ASC, ar.displayorder ASC', array($idtab));

		// REORDONNER sous forme d'arbre parcours en profondeur d'abord
        $tabaff_niveau = array();
        $tabaff_codes = array();  // liste des cadres dns l'ordre parcours transverse
        $tabaff_codes_ordonnes = array();  // liste des cadres dans l'ordre parcours transverse
        $tabaff_codes_largeur = array();

		// 52 branches possibles a chaque noeud, Ã§a devrait suffire ...
		$tcodes = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
		// Initialisation
		$n=0;
		foreach ($recframes as $recframe) {
        	if ($recframe){
            	$tabaff_niveau[$recframe->id] =0;
            	$tabaff_codes[$recframe->id] =$tcodes[$n];
				$n++;
			}
		}

        $niveau_courant = 0;
        $ordre_courant = 0;
        $parent_courant = 0;
        $tab_ordre_par_position = array();
        $tab_ordre_par_niveau = array();
        $tab_ordre_par_niveau[0]=-1;
		// Reordonner
        if ($recframes) {
			foreach ($recframes as $recframe) {
				if ($recframe->idparentframe == 0){
                    $niveau_courant = 0;
                    $ordre_courant = $tab_ordre_par_niveau[$niveau_courant]+1;

				}
				else if ($recframe->idparentframe != $parent_courant){
					// changement de niveau
     				$niveau_courant = $tabaff_niveau[$recframe->idparentframe] + 1;
                    if (isset($tab_ordre_par_niveau[$niveau_courant])){
                    	$ordre_courant = $tab_ordre_par_niveau[$niveau_courant] + 1;
					}
					else{
                        $ordre_courant = 0;
						$tab_ordre_par_niveau[$niveau_courant] = 0;
					};
				}
                $tab_ordre_par_niveau[$niveau_courant] = $ordre_courant;

				$tabaff_niveau[$recframe->id] = $niveau_courant;
				$parent_courant = $recframe->idparentframe;

                $code='';
				if ($niveau_courant>0){
					$code =  $tabaff_codes[$recframe->idparentframe];
				}
                $code.=$tcodes[$ordre_courant];
                $tabaff_codes[$recframe->id] = $code;

                $position = $niveau_courant * 52 + $ordre_courant;
				$tab_ordre_par_position[$recframe->id] = $position;

				$a = new stdclass;
                $a->id = $recframe->id;
                $a->title = $recframe->title;
                $a->idparentframe = $recframe->idparentframe;
                $a->code = $code;
                $a->niveau = strlen($code);
                $a->rang = $ordre_courant;
                $a->position = $position;
				// A completer dans une seconde passe
                $a->nodelist = array();
                $a->nodenumber = 0;
                $a->nbfeuilles=0;
                // A completer dans une troisieme passe
                $a->colspan = 0;
                $a->col = 0;
                $tabaff_codes_ordonnes[$recframe->id] = $a;

                $ordre_courant++;
			}
		}

        asort($tab_ordre_par_position);

		// Reorganisation
        foreach ($tab_ordre_par_position as $key => $val){
            $tabaff_codes_largeur[$key] = $tabaff_codes[$key];
		}

        $tabaff_codes_profondeur = $tabaff_codes;
        asort($tabaff_codes_profondeur);

        // CREATION du tableau a afficher
		// Pour chaque cadre dans le parcours en profondeur completer
		// liste des noeuds (nodelist) et nombre de noeuds (nodenumber)
		// Nombre_feuille(code) = longeur_code(fils le plus profond) - longeur_code(code)
		// fils_le_plus_profond(code) = liste_des_fils[DERNIER]
		//

		foreach ($tabaff_codes_profondeur as $key => $val){
			$object = $tabaff_codes_ordonnes[$key];
			$code_courant = $object->code;
			// rechercher les fils
            $i=0;
			$ok=true;
   			foreach ($tabaff_codes_profondeur as $idframe => $code){
    			$pos = strpos($code, $code_courant, 0);
				// echo "<br />ID FRAME : $idframe, CODE CHERCHE : $code_courant, CODE : $code,  POS : $pos\n";
				// exit;
				// Notez notre utilisation de !==.  != ne fonctionnerait pas comme attendu
				// car la position de 'a' est la 0-ieme (premier) caractere.
				if ($pos !== false) {
				    //La chaine '$code_courant' a ete trouvee dans la chaine '$code' et debute a la position $pos<br />\n";
					if ($pos===0){
						// C'est un fils
                        $object->nodelist[]=$idframe;
                        $object->nodenumber++;
					}
				}
			}
		}


        // CREATION du tableau a afficher (Suite)
		// Pour chaque cadre dans le parcours en profondeur completer
		// nombre de feuilles et nombre de cases (colspan)

		foreach ($tabaff_codes_profondeur as $key => $val){
			$object = $tabaff_codes_ordonnes[$key];
			$code_courant = $object->code;
			// rechercher les fils
            $i=0;
			$ok=true;
   			foreach ($object->nodelist as $idnode){
    			if ($tabaff_codes_ordonnes[$idnode]->nodenumber == 1){    // Les noeuds feuilles ont un nodelist reduite à eux-memes
                    $object->nbfeuilles++;
				}
			}
            $object->colspan = $object->nbfeuilles;
		}

        // CREATION du tableau a afficher (Fin)
		// Pour chaque cadre dans le parcours en LARGEUR completer col
		// Si changement de niveau col = col du pere
		// Sinon col = colspan occupees + cospan du precedant
		$niveau_courant=0;
        $col_precedant=0;
        $nb_col=0;
		$max_lig=0;
		// Parcours en largeur
		foreach ($tabaff_codes_largeur as $idframe => $code){
			$object = $tabaff_codes_ordonnes[$idframe];
			$niveau = $object->niveau;
            //echo "<br />CODE : ".$object->code.", NIVEAU : ".$object->niveau.", NIVEAU_COURANT : $niveau_courant \n";
            if ($niveau == 1){
                $nb_col+=$object->colspan;
			}
			if ($niveau != $niveau_courant){
				// Changement de niveau
                //echo "<br />CHANGEMENT NIVEAU \n";
                $max_lig++;
                if (!empty($object->idparentframe)){
                    if ($object_pere = $tabaff_codes_ordonnes[$object->idparentframe]){
                        $object->col = $object_pere->col;
					}
				}
				else{
                    $object->col = $col_precedant;
				}
                $niveau_courant = $niveau;
			}
			else{
				$object->col = $object->colspan + $col_precedant;
			}
            $col_precedant = $object->col;
		}


		// RECOPIER LES DONNEES
		// Table en profondeur d'abord


		$table_affichee = "\n".'<table>'."\n";
		//."\n".'<tbody><tr><th colspan="'.$nb_lig.'">'.$tab->title.' <i>'.get_string('selectframe','artefact.booklet').'</i></th></tr></tbody>'."\n";
        $nouvelleligne=true;
		$niveau_courant=0;
        $col_courante=0;

		$options = array();
        $nboptions=0;
        $options[0] = get_string('root','artefact.booklet');

        $nboptions++;
        foreach ($tabaff_codes_profondeur as $idframe => $code){
			$object = $tabaff_codes_ordonnes[$idframe];
			// print_object($object);
            if ( $object->id != $itemframe->id){
				$options[$object->id] = $object->title;
                $nboptions++;
			}
            $parentid = $object->idparentframe;
            $colposition = 0;
			if ($nouvelleligne){
                //$table_affichee.='<tr class="niveau_'.$object->niveau.'">';
                $table_affichee.='<tr>';
                $nouvelleligne=false;
                //$col_courante = $colposition;
                $col_courante = 0;
			}

			if (!empty($colposition)){
                $colposition--;
			}

			//echo "<br />$object->code ::  COL_COURANTE : $col_courante -&gt; COLPOSITION : $colposition\n";

			if ($col_courante < $colposition){
                //echo "<br />AVANT : $object->code ALLER DE $col_courante A $colposition\n";
				for ($i=0; $i < $colposition; $i++){
            		// $table_affichee.='<td class="blank">&nbsp;</td>';
					$col_courante++;
				}
			}

            $cod = chr( (ord(strtoupper(substr($code,0,1))) - 64) % 8 + 64);
            $index_color = (($object->niveau - 1) % 4) + 1;
            $color="$cod$index_color";
            if ($object->id == $itemframe->id){
   				if ($object->colspan>1){
					$table_affichee.='<td class="special" rowspan="'.$object->colspan.'">'.$object->title.'d</th>';
				}
				else{
					$table_affichee.='<td class="special">'.$object->title.'</th>';
				}
			}
			else{
				if ($object->colspan>1){
					$table_affichee.='<td class="niveau_'.$color.'" rowspan="'.$object->colspan.'">'.$object->title.'</td>';
				}
				else{
					$table_affichee.='<td class="niveau_'.$color.'">'.$object->title.'</td>';
				}
			}

            $col_courante++;

			// Nouvelle ligne ?
			if ($object->nodenumber == 1){
                $table_affichee.='</tr>'."\n";
                $nouvelleligne=true;
			}
		}


        $table_affichee.='</table>'."\n";

        $frameform['menu'] = $table_affichee;


		$elements = array();

        $defaultvalue=0;

       	$elements['idframe'] = array(
        		        	'type' => 'hidden',
                			'value' => $itemframe->id,
    	);

       	$elements['idtab'] = array(
        		        	'type' => 'hidden',
                			'value' => $itemframe->idtab,
    	);

        $elements['choice'] = array(
        		'type' => 'radio',
            	'options' => $options,
                //'help' => $help,
                'title' => get_string('selectwheremove', 'artefact.booklet', $itemframe->title),
                'defaultvalue' => $defaultvalue,
                //'rowsize' => $nboptions,
                //'description' => get_string('selectwheremove', 'artefact.booklet'),
        );
/*
		$elements['help'] = array(
                    'type'=>'wysiwyg',
                    'rows' => 16,
                    'cols' => 60,
                    'title' => get_string('helptab', 'artefact.booklet'),
                    'defaultvalue' => ((!empty($tab)) ? $tab->help : NULL),
                );
*/
		$elements['save'] = array(
                    'type' => 'submitcancel',
                    'value' => array(get_string('savetab', 'artefact.booklet'),
                                     get_string('cancelframe', 'artefact.booklet')),
                    'goto' => get_config('wwwroot') . '/artefact/booklet/frames.php?id='.$tab->id,
                );

        $tabname = pieform(array(
            'name'        => 'tabname',
            'plugintype'  => 'artefact',
            'successcallback' => 'framenodemove_submit',
            'pluginname'  => 'booklet',
            'method'      => 'post',
            'renderer'      => 'table',
            'elements'    => $elements,
            'autofocus'  => false,
        ));
        $frameform['tabname'] = $tabname;
        return $frameform;
    }


}

function tabname_submit(Pieform $form, $values ) {
    $tab = get_record('artefact_booklet_tab', 'id', $values['id']);
    $tab->title = $values['name'];
    $tab->help = $values['help'];
    update_record('artefact_booklet_tab', $tab);
    // $goto = get_config('wwwroot') . '/artefact/booklet/tabs.php?id='.$tab->idtome;
    $goto = get_config('wwwroot') . '/artefact/booklet/frames.php?id=' . $tab->id;
    redirect($goto);
}

function framenodemove_submit(Pieform $form, $values ) {
	if (!empty($values)){
		//print_object($values);
		//exit;
  		if (isset($values['choice'])){
			$sql = "SELECT MAX(displayorder) AS max FROM {artefact_booklet_frame} WHERE idparentframe = ? ";
            $rec = get_record_sql ($sql, array($values['choice']));
			$displayorder=$rec->max+1;
			set_field('artefact_booklet_frame', 'idparentframe', $values['choice'], 'id', $values['idframe']);
           	set_field('artefact_booklet_frame', 'displayorder', $displayorder, 'id', $values['idframe']);
		}
    	$goto = get_config('wwwroot') . '/artefact/booklet/frames.php?id=' . $values['idtab'];
    	redirect($goto);
	}
}

function addframe_submit(Pieform $form, $values) {
    global $USER;
	try {
        $goto = call_static_method('ArtefactTypeFrame',
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
    if ($frame = get_record('artefact_booklet_frame', 'id', $idframe)){
    	$goto = get_config('wwwroot') . '/artefact/booklet/frames.php?id='.$frame->idtab;
		// MODIF JF for managing included frames
		if (!empty($frame->idparentframe)){
            $goto = get_config('wwwroot') . '/artefact/booklet/objects.php?id='.$frame->idparentframe;
		}
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
                    'goto' => $goto,
                ),
                'id' => array(
                    'type' => 'hidden',
                    'value' => $idframe,
                ),
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
                        'reference' => get_string('reference', 'artefact.booklet'), // referencer un champs d'une autre fiche
                        'synthesis' => get_string('synthesis', 'artefact.booklet'),          // seulement pour les cadres non liste et seulement lors de la creation
                        'attachedfiles' => get_string('attachedfiles', 'artefact.booklet'),
                        'listskills' => get_string('listskills', 'artefact.booklet'),
                        'freeskills' => get_string('freeskills', 'artefact.booklet'),
                    ) : array(
                        'longtext' => get_string('longtext', 'artefact.booklet'),
                        'shorttext' => get_string('shorttext', 'artefact.booklet'),
                        'htmltext' => get_string('htmltext', 'artefact.booklet'),
                        'area' => get_string('area', 'artefact.booklet'),
                        'radio' => get_string('radio', 'artefact.booklet'),
                        'checkbox' => get_string('checkbox', 'artefact.booklet'),
                        'date' => get_string('date', 'artefact.booklet'),
                        'reference' => get_string('reference', 'artefact.booklet'), // referencer un champs d'une autre fiche
                        'attachedfiles' => get_string('attachedfiles', 'artefact.booklet'),
                        'listskills' => get_string('listskills', 'artefact.booklet'),
						'freeskills' => get_string('freeskills', 'artefact.booklet'),
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
                ),
            ),
            'autofocus'  => false,
        ));
        // MODIF JF for managing included frames
        $successorframe = pieform(array(
            'name'        => 'addframe',
            'plugintype'  => 'artefact',
            'successcallback' => 'addsuccessorframe_submit',
            'pluginname'  => 'booklet',
            'method'      => 'post',
            'renderer'      => 'oneline',
            'elements'    => array(
                'save' => array(
                    'type' => 'submit',
                    'value' => get_string('addsuccessorframe', 'artefact.booklet'),
                ),
                'id' => array(
                    'type' => 'hidden',
                    'value' => 0,
                ),
                'idtab' => array(
                    'type' => 'hidden',
                    'value' => $frame->idtab,
                ),
                'idparentframe' => array(
                    'type' => 'hidden',
                    'value' => $idframe,
                ),
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
        $objectsform['successorframe'] = $successorframe;
        return $objectsform;
	}
	return NULL;
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

function addsuccessorframe_submit(Pieform $form, $values) {
    global $USER;
	try {
		$count = count_records('artefact_booklet_frame', 'idtab', $idtab, 'idparentframe', $idparentframe);
    	$rec = new stdclass();
 		$rec->title = '';
    	$rec->idtab = $values['idtab'];
    	$rec->help = '';
    	$rec->list = 0;
    	$rec->displayorder = $count + 1;
		$rec->idparentframe = $values['idparentframe'];
		//echo "<br />objects.php :: Ligne 55 :: NEWRECFRAME<br /> \n";
		//print_object($rec);
		//exit;
    	if ($id = insert_record('artefact_booklet_frame', $rec, 'id', true)){
        	if ($frame = get_record('artefact_booklet_frame', 'id', $id)){
                $goto = get_config('wwwroot') . '/artefact/booklet/objects.php?id=' . $id;
			}
		}
    }
    catch (Exception $e) {
        $goto = get_config('wwwroot') . '/artefact/booklet/tab.php';
        $SESSION->add_error_msg(get_string('framesavefailed', 'artefact.booklet'));
        redirect($goto);
    }
    redirect($goto);
}


/*****************************************************
 *
 *
 MODIF JF
 *
 *
 ****************************************************/

class ArtefactTypeListSkills extends ArtefactTypebooklet {
    /* classe pour pieforms et fonctions JS propres a une option */
    protected $domain = '';
    protected $code = '';
    protected $description = '';
    protected $scale = '';
    protected $type = '';
    protected $threshold = 0;
    protected $skillslist = '';

    public static function is_singular() { return true; }
    public static function get_tablerenderer_js() {
        return "
                'description',
				'skillslist',
                ";
    }

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


	//------------------------------------------------------------------------
    public static function get_form($idobject, $domainsselected=0) {
        $object = get_record('artefact_booklet_object', 'id', $idobject);
		//print_object($object);
		//exit;
        $elements = array();
		$tab_selected = array();
		$idlist=0;

		if (empty($domainsselected)){
            $domainsselected='any';
		}

		// Selected skills
		if ($skillslist = get_record('artefact_booklet_list', 'idobject', $idobject)){
            $idlist =  $skillslist->id;
			// items associes
			$items  = get_records_array('artefact_booklet_listofskills', 'idlist', $skillslist->id);
			if (!empty($items)){
				foreach ($items as $item){
					if ($skill = get_record('artefact_booklet_skill', 'id', $item->idskill)){
                        $tab_selected[$skill->id] = $item->id;
					}
				}
			}
		}

		// Domains
        // Skills
        $list_of_domains_selected = array();

        if (!empty($domainsselected) && ($domainsselected!='any')){
            $tab_domainsselected = explode('-', $domainsselected);
			//print_object($tab_domainsselected);
			//exit;
			foreach($tab_domainsselected as $index_domainselected){
				if (isset($index_domainselected)){
                    $list_of_domains_selected[] = trim($index_domainselected);
				}
			}
		}

		//print_object($list_of_domains_selected);
		//exit;
		$sql = "SELECT DISTINCT domain FROM {artefact_booklet_skill} ORDER BY domain ASC";
        $domains = get_records_sql_array($sql, array());
        //print_object($domains);
		//exit;

		if (!empty($domains)){
        	$nbdomains = count($domains);
			if ($nbdomains>1){
		    	$domain_options = array();
				$domain_selected = array();
				$d=0;

				if ($domainsselected=='any'){
					foreach ($domains as $domain){
	                	$domain_options[$d]=$domain->domain;
   		                $domain_selected[] = $d;
						$d++;
					}
				}
				else{
					foreach ($domains as $domain){
	                	$domain_options[$d]=$domain->domain;
						if (array_search($d, $list_of_domains_selected)){
    		                $domain_selected[] = $d;
						}
						$d++;
					}
				}
		        //print_object($domain_options);
				//exit;

    			$elementdomains['domainselect'] = array(
	        		'type' => 'select',
	    	    	'title' => '', //get_string('selectdomains','artefact.booklet'),
		        	'multiple' => true,
    		    	'options' => $domain_options,
        			'defaultvalue' => $domain_selected,
        			//'size' => count($domains),
	                'size' => 3,
    	            'description' => get_string('multiselect', 'artefact.booklet'),
	    		);

            	$elementdomains['submit'] = array(
            		'type' => 'submitcancel',
        	    	'value' => array(get_string('savedomainchoice','artefact.booklet'), get_string('cancel')),
    	         	'goto' => get_config('wwwroot') . '/artefact/booklet/objects.php?id='.$object->idframe,
	        	);

		        $elementdomains['idobject'] = array(
                        'type' => 'hidden',
                        'value' => $idobject,
    	        );

				$elementdomains['compositetype'] = array(
                    'type' => 'hidden',
                    'value' => $object->type,
        		);

   	    		$domainchoice = array(
            	    'name' => 'domainchoice',
                	'plugintype' => 'artefact',
	                'pluginname' => 'booklet',
    	    	    // 'validatecallback' => 'validate_selectlist',
        	    	'successcallback' => 'selectdomains_submit',
            	    'renderer' => 'table',
                	'elements' => $elementdomains,
	            );
    	    	$compositeform['domainchoice'] = pieform($domainchoice);
			}
		}

        // -------------------------------------
        if (!empty($list_of_domains_selected)){
            $where='';
			$params = array();
			foreach($list_of_domains_selected as $d){
				if (!empty($where)){
					$where.=' OR domain = ? ';
				}
				else{
                    $where.=' domain = ? ';
				}
				$params[]= $domain_options[$d];
			}
			$sql = "SELECT * FROM {artefact_booklet_skill} WHERE ".$where." ORDER BY code ASC";
		    $skills = get_records_sql_array($sql, $params);
		}
		else{
			$sql = "SELECT * FROM {artefact_booklet_skill} ORDER BY domain ASC, code ASC";
		    $skills = get_records_sql_array($sql, array());
		}

		// -------------------------------------
		if (!empty($skills) && !empty($idlist)){
			$i=0;
            $elementsskills = array();
        	foreach ($skills as $skill){
				if (!empty($tab_selected[$skill->id])){
                    $elementsskills['select'.$i] = array(
        		        	'type' => 'checkbox',
                			'defaultvalue' => $tab_selected[$skill->id],
		                	'title' => $skill->code,
        		        	//'description' => get_string('checked', 'artefact.booklet'),
           			);
				}
				else{
                    $elementsskills['select'.$i] = array(
        		        	'type' => 'checkbox',
                			'defaultvalue' => 0,
		                	'title' => $skill->code,
        		        	//'description' => '',
           			);
				}
                $elementsskills['html'.$i] = array(
                			'type' => 'html',
                			'value' => $skill->domain.'; '.$skill->description.'; ['.$skill->scale.'|'.$skill->threshold.']'."\n",
           		);
                $elementsskills['id'.$i] = array(
		                	'type' => 'hidden',
        		        	'value' => $skill->id,
           		);
                $elementsskills['type'.$i] = array(
		                	'type' => 'hidden',
        		        	'value' => $skill->type,
           		);

                $i++;
			}

	        $elementsskills['nbitems'] = array(
                	'type' => 'hidden',
                	'value' => $i,
    	    );
/*
       		$elementsskills['submit'] = array(
            	'type' => 'submitcancel',
            	'value' => array(get_string('savechecklist','artefact.booklet'), get_string('cancel')),
                'goto' => get_config('wwwroot') . '/artefact/booklet/objects.php?id='.$object->idframe,
        	);
*/
       		$elementsskills['submit'] = array(
            	'type' => 'submit',
            	'value' => get_string('savechecklist','artefact.booklet'),
        	);

			$elementsskills['delete'] = array(
                'type' => 'checkbox',
                'help' => false,
                'title' => get_string('deleteskills','artefact.booklet'),
                'defaultvalue' => 0,
                'description' => get_string('deleteskillsdesc','artefact.booklet'),
        	);

	        $elementsskills['idobject'] = array(
                        'type' => 'hidden',
                        'value' => $idobject,
            );
	        $elementsskills['idframe'] = array(
                        'type' => 'hidden',
                        'value' => $object->idframe,
            );

			$elementsskills['compositetype'] = array(
                    'type' => 'hidden',
                    'value' => $object->type,
        	);


	        $elementsskills['idlist'] = array(
                        'type' => 'hidden',
                        'value' => $idlist,
            );

            $elementsskills['domainsselected'] = array(
                    'type' => 'hidden',
                    'value' => $domainsselected,
            );

    	    $choice = array(
                'name' => 'listchoice',
                'plugintype' => 'artefact',
                'pluginname' => 'booklet',
        	    // 'validatecallback' => 'validate_selectlist',
            	'successcallback' => 'selectskilllist_submit',
                'renderer' => 'table',
                'elements' => $elementsskills,
            );
        	$compositeform['choice'] = pieform($choice);
		}


		// -------------------------------------
        //print_object($object);
		//exit;
        $cform = array(
            'name' => 'modifform',
            'plugintype' => 'artefact',
            'pluginname' => 'booklet',
            'renderer' => 'table',
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
                    'type' => 'wysiwyg',
                    'rows' => 5,
                    'cols' => 60,
                    'title' => get_string('helpobject', 'artefact.booklet'),
                        'defaultvalue' => ((!empty($object)) ? $object->help : NULL),
                ),
                'description' => array(
                    	'type' => 'wysiwyg',
                    	'rows' => 5,
                    	'cols' => 60,
                    	'title' => get_string('descriptionlist', 'artefact.booklet'),
                        'defaultvalue' => ((!empty($skillslist) && !empty($skillslist->description)) ? $skillslist->description : get_string('descriptionlistmodel', 'artefact.booklet')),
						'description' => get_string('descriptionlistdesc', 'artefact.booklet'),
                        'help' => true,
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
                ),


            ),
            'successcallback' => 'objectbaseskillslist_submit',
        );
        $compositeform['form'] = pieform($cform);

		if (!empty($idlist)){
 		    $sform = array(
		        'name' => 'inputskills',
        		'plugintype' => 'artefact',
		        'pluginname' => 'booklet',
    			'renderer' => 'table',
		        'method'      => 'post',
        		'successcallback' => 'objectskillslist_submit',
			    'elements' => array(
        			'optionnal' => array(
	        			'type' => 'fieldset',
				    	'name' => 'inputform',
						'title' => get_string ('inputnewskills', 'artefact.booklet'),
        				'collapsible' => true,
		            	'collapsed' => true,
	    		        'legend' => get_string('inputnewskills', 'artefact.booklet'),
                		'elements' => array(
            				'skillslist' => array(
		                    	'type' => 'textarea',
        		            	'rows' => 10,
                		    	'cols' => 100,
                    			'title' => get_string('listofskills', 'artefact.booklet'),
		                        'defaultvalue' => '',
        		                'description' => get_string('inputlistofskillsmodel', 'artefact.booklet'),
								'help' => true
            				),
		   		        	'submit' => array(
       			 	           'type' => 'submit',
           	    		        'value' => get_string('saveskills', 'artefact.booklet'),
		               		),
						),
					),

		           	'domainsselected' => array(
        		        'type' => 'hidden',
            			'value' => $domainsselected,
		        	),

	    		    'compositetype' => array(
	            		'type' => 'hidden',
		   		        'value' => $object->type,
       				),
           			'id' => array(
		       	    	'type' => 'hidden',
        		   	    'value' => $idobject,
    	    		),
				),
    		);

        	$compositeform['skillsform'] = pieform($sform);
		}

        $frame = get_record('artefact_booklet_frame', 'id', $object->idframe);
        $visuaform = pieform(array(
            'name' => 'visuaform',
            'plugintype'  => 'artefact',
            'pluginname'  => 'booklet',
            'successcallback' => 'visualizetome_submit',
            'method'      => 'post',
            'renderer'      => 'table',
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

        $compositeform['visuaform'] = $visuaform;

        //print_object($compositeform);
		//exit;
        return $compositeform;
    }
}

// -------------------------------------
function selectdomains_submit(Pieform $form, $values) {
    global $_SESSION;
	global $_SERVER;
	//print_object($values);
	//exit;
	// Domain selection
    $domainsselected='';

	if (isset($values['domainselect'])){
		foreach($values['domainselect'] as $a_domain){
	        $domainsselected.= $a_domain.'-';
		}
        $domainsselected=substr($domainsselected,0,strlen($domainsselected)-1);
		//echo "$domainsselected";
		//exit;

		if (!empty($domainsselected)){
            $goto = get_config('wwwroot') . '/artefact/booklet/options.php?id=' . $values['idobject'] . '&domainsselected='.$domainsselected;
		}
		else{
			$goto = get_config('wwwroot') . '/artefact/booklet/options.php?id=' . $values['idobject'];
		}

	}
	else{
        $goto = $_SERVER['HTTP_REFERER'];
	}
	redirect($goto);
}


// -------------------------------------
function selectskilllist_submit(Pieform $form, $values) {
    global $_SESSION;
    $displayorder = 0;
	$t_skillslist=array();
	$where='';
	$params = array();

	//print_object($values);
	//exit;
    if (!empty($values['idlist'])){
		// A priori inutile car les lignes sont reinitialisees plus bas
		//		if ($recslistofskills = get_records_array('artefact_booklet_listofskills', 'idlist', $values['idlist'])){
        //	$displayorder = count($recslistofskills);
		//}
        $select = ' (idlist = ?) ';
        $params[] = $values['idlist'];

		if (!empty($values['nbitems'])){

	 		for ($i=0; $i<$values['nbitems']; $i++){
				if (!empty($values['select'.$i])){
					// Creer l'association
					$a_listskill = new stdclass();
           			$a_listskill->idlist = $values['idlist'];
	            	$a_listskill->idskill = $values['id'.$i];
                    $t_skillslist[]=$a_listskill;
					if (empty($where)){
						$where .= ' (idskill = ?) ';
					}
					else{
        	    	    $where .= ' OR (idskill = ?) ';
					}
					$params[] = $values['id'.$i];
				}
			}
		}

		if (!empty($t_skillslist)){
			// Remettre à vide car il peut y avoir des de-selections
			if (!empty($where)){
				$select .= ' AND ('. $where . ') ';
			}
            delete_records_select('artefact_booklet_listofskills', $select, $params);
			if (!$values['delete']){
				// how many
				$rec_listofskills = get_records_array('artefact_booklet_listofskills', 'idlist',  $values['idlist']);

				$displayorder = count($rec_listofskills);
				foreach($t_skillslist as $a_listskill){
    	            $a_listskill->displayorder = $displayorder;
					//if ($rec_a_list_skill = get_record('artefact_booklet_listofskills', 'idlist',  $a_listskill->idlist, 'idskill', $a_listskill->idskill)){
				    //	$a_listskill->id = $rec_a_list_skill->id;
    				//    update_record('artefact_booklet_listofskills', $a_listskill);
					//}
					//else{
	    				insert_record('artefact_booklet_listofskills', $a_listskill);
					//}
                	$displayorder++;
				}
			}
		}
		if (!empty($values['domainsselected'])){
            $goto = get_config('wwwroot') . '/artefact/booklet/options.php?id=' . $values['idobject'] . '&domainsselected='.$values['domainsselected'];
		}
		else{
			$goto = get_config('wwwroot') . '/artefact/booklet/options.php?id=' . $values['idobject'] . '&domainsselected=';
		}

		redirect($goto);
	}
    redirect(get_config('wwwroot') . '/artefact/booklet/index.php');
}

function objectbaseskillslist_submit(Pieform $form, $values){
    global $_SESSION;
    if ($object = get_record('artefact_booklet_object', 'id', $values['id'])){
		//echo "<br />lib.php :: 1733 :: OBJECT <br >\n";
		//print_object($object);
		$nameexist = get_record('artefact_booklet_object', 'name', $values['name']);
	    if ($nameexist != false && $nameexist->id != $values['id']) {
    	    $form->reply(PIEFORM_ERR, array('message'=>get_string('objectsavefailed', 'artefact.booklet'),
                                        'goto' => $_SERVER['HTTP_REFERER']));
    	}
		$object->title = $values['title'];
	    $object->name = $values['name'];
    	$object->help = $values['help'];
	    update_record('artefact_booklet_object', $object);

		// List value
		$list = new stdclass();
    	$list->idobject = $object->id;

		if (!empty($values['description'])){
    	    $list->description = $values['description'];
		}

		// enregistrer la liste
		if (!empty($list)){
    		if ($rec_list = get_record('artefact_booklet_list', 'idobject', $object->id)){
				$idlist = $rec_list->id;
				$list->id = $rec_list->id;
    	        $list->idobject = $object->id;
	            update_record('artefact_booklet_list', $list);
			}
   			else{
    			if (empty($list->description)){
	        		$list->description = get_string('descriptionlistmodel', 'artefact.booklet');
				}
        	    $idlist=insert_record('artefact_booklet_list', $list, 'id', true);
			}
		}

  		$goto = get_config('wwwroot') . '/artefact/booklet/options.php?id=' . $object->id;
    	redirect($goto);
	}
}

function objectskillslist_submit(Pieform $form, $values){
    global $_SESSION;
    $t_skill = array();
    $unknown_domain = get_string('unknowndomain', 'artefact.booklet');
	$current_domain = $unknown_domain;
    srand();
    if ($object = get_record('artefact_booklet_object', 'id', $values['id'])){
		//echo "<br />lib.php :: 1781 :: OBJECT <br >\n";
		//print_object($object);

        if ($list = get_record('artefact_booklet_list', 'id', $values['idlist'])){
			if (!empty($values['skillslist'])){
    	    	//
				if ($tlist=explode("\n", strip_tags($values['skillslist']))){
					foreach ( $tlist as $line){
						if (!empty($line)){
							$skill = new stdclass();

							// "domain1;code1;description1;[scale_value_value11,scale_value12,scale_value13,...,scale_value1N;threshold1]
							if ($fields = explode("[", $line)){
		                        if (!empty($fields[0])){
    		                       	// "domain1;type1;code1;description1;
        		                    if ($domain_code_description = explode(";", $fields[0])){
                                        $nbfields=count($domain_code_description);
										if ( $nbfields<5){    // pas de type dans le donnees
											if (isset($domain_code_description[0])){
                		                		$skill->domain =  trim(str_replace("-","",$domain_code_description[0]));
												$current_domain = $skill->domain;
                    	    	       		}
											else{
                            	    	    	$skill->domain = $current_domain;
											}
			                                if (isset($domain_code_description[1])){
    			                                $skill->code = trim($domain_code_description[1]);
											}
											else{
                			                    $skill->code = strtoupper($current_domain).'_'.$object->id.'_'.rand();
											}
	                        		        if (isset($domain_code_description[2])){
    	                        		        $skill->description = trim($domain_code_description[2]);
											}
											else{
        	        	                    	$skill->description = get_string('unknown', 'artefact.booklet');
											}
               		                    	$skill->type = '1';
										}
										else{
											if (isset($domain_code_description[0])){
                		                		$skill->domain =  trim(str_replace("-","",$domain_code_description[0]));
												$current_domain = $skill->domain;
	                    	    	        }
											else{
        	                    	    	    $skill->domain = $current_domain;
											}
		        	                        if (isset($domain_code_description[1])){
    		        	                        $skill->type = trim($domain_code_description[1]);
											}
											else{
                		        	            $skill->type = '1';
											}
		                                	if (isset($domain_code_description[2])){
    		                                	$skill->code = trim($domain_code_description[2]);
											}
											else{
                		                    	$skill->code = strtoupper($current_domain).'_'.$object->id.'_'.rand();
											}
	                        	        	if (isset($domain_code_description[3])){
    	                        	        	$skill->description = trim($domain_code_description[3]);
											}
											else{
        		        	                    $skill->description = get_string('unknown', 'artefact.booklet');
											}
										}
									}
								}
    	                	    if (!empty($fields[1])){
	        	                    // scale_value_value11,scale_value12,scale_value13,...,scale_value1N|threshold1]
    	        	                if ($scale_threshold = explode("|", $fields[1])){
										if (!empty($scale_threshold[0])){
            	        	                // scale_value_value11,scale_value12,scale_value13,...,scale_value1N
                	        	        	$skill->scale = trim($scale_threshold[0]);
                    	        	    }
										else if (!empty($skill->type)){ // Type 1 necessite un bareme
	                        	            //$skill->scale = $values['scale'];
	                                        $skill->scale = get_string('generalscalemodel','artefact.booklet');
    	                                    $skill->threshold  = '3';
										}
        		                        if (!empty($scale_threshold[1])){
            		                        // threshold1]
											// Chasser ']'
                    		                $skill->threshold = trim(substr($scale_threshold[1],0,-1));
										}
									}
								}
							}
							$t_skill[] = $skill;
						}
					}
				}
			}
			// enregistrer les skills
			if (!empty($t_skill)){
				$displayorder=0;
            	if ($recslistofskills = get_records_array('artefact_booklet_listofskills', 'idlist',  $list->id)){
	                $displayorder= count($recslistofskills);
				}

				foreach ($t_skill as $a_skill){
					// Creer le skill
					if ($rec_skill = get_record('artefact_booklet_skill', 'code', $a_skill->code)){
						$idskill = $rec_skill->id;
	                	$a_skill->id = $rec_skill->id;
		            	update_record('artefact_booklet_skill', $a_skill);
					}
   					else{
						$idskill = insert_record('artefact_booklet_skill', $a_skill, 'id', true);
					}
					// Creer l'association
					$a_listskill = new stdclass();
            		$a_listskill->idlist = $list->id;
		            $a_listskill->idskill = $idskill;
    		        $a_listskill->displayorder = $displayorder;

        	    	if ($rec_a_list_skill = get_record('artefact_booklet_listofskills', 'idlist',  $list->id, 'idskill', $idskill)){
	        	        $a_listskill->id = $rec_a_list_skill->id;
    	        	    update_record('artefact_booklet_listofskills', $a_listskill);
					}
					else{
	                	insert_record('artefact_booklet_listofskills', $a_listskill, 'id', true);
					}
    	    	    $displayorder++;
				}
			}

   			$goto = get_config('wwwroot') . '/artefact/booklet/options.php?id=' . $object->id;
			redirect($goto);
		}
	}
}



/*****************************************************************/
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
                        'value' => $idobject,
                    ),
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
                if (($item->type != 'synthesis') && ($item->type != 'reference')){ // Pas utile de s'autoreferencer !
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
        else if ($object->type == 'reference') {
            $sql = "SELECT abo.id, abo.title, abo.name, abo.type, abo.idframe, abo.displayorder
                     FROM {artefact_booklet_object} abo
                     JOIN {artefact_booklet_frame} abf ON abo.idframe = abf.id
                     WHERE abf.idtab = (SELECT fr.idtab
                                        FROM {artefact_booklet_object} obj
                                        JOIN {artefact_booklet_frame} fr ON obj.idframe = fr.id
                                        WHERE obj.id = ?)
					 ORDER BY abo.idframe ASC, abo.displayorder ASC ";
            $items = get_records_sql_array($sql, array($idobject));
            $options = array();
            $options[''] = get_string('selectafield', 'artefact.booklet');
            foreach ($items as $item) {
                if (($idobject != $item->id) && ($item->type != 'synthesis') && ($item->type != 'reference')){    // Pas utile de s'autoreferencer !
					if ($aframe = get_record('artefact_booklet_frame', 'id', $item->idframe)){
                    	$options[$item->name] = $aframe->title.'  ('.get_string('idframe', 'artefact.booklet',$item->idframe).') '.get_string('objecttitle','artefact.booklet', $item->title).' ('.get_string('idobject', 'artefact.booklet',$item->id).'  '.get_string('objecttype', 'artefact.booklet',$item->type).') ';
					}
                }
            }
            $choice = array(
                'name' => 'referencefield',
                'plugintype' => 'artefact',
                'pluginname' => 'booklet',
                'successcallback' => 'addfieldreference_submit',
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

function addfieldreference_submit(Pieform $form, $values) {
    global $_SERVER;
	//print_object($values);
	//exit;
    try {
        db_begin();
        $data = new stdClass;
        $data->idobject = $values['idobject'];
        $temp = get_record('artefact_booklet_object', 'name', $values['option']);
        $data->idobjectlinked = $temp->id;
        if (count_records('artefact_booklet_reference', 'idobject', $values['idobject'], 'idobjectlinked', $temp->id) == 0) {
            insert_record('artefact_booklet_reference', $data);
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

class ArtefactTypeReference extends ArtefactTypeOption {
    /* classe pour pieforms et fonctions JS propres a une reference */
    public static function is_singular() { return true; }
    public static function get_tablerenderer_js() {
        return "
                'title',
                ";
    }
}

class ArtefactTypeFreeSkills extends ArtefactTypeOption {
    /* classe pour pieforms et fonctions JS propres a une competence proposee par l'utilisateur */
    public static function is_singular() { return true; }
    public static function get_tablerenderer_js() {
        return "
                'title',
                ";
    }

	public static function get_freeskillsform($idtab, $idobject, $idrecord=0, $domainsselected=0) {
		global $USER;

		$object = get_record('artefact_booklet_object', 'id', $idobject);
		// DEBUG
		//echo "<br /> DEBUG :: lib.php :: 2548 :: <br />\n";
		//print_object($object);
		//exit;
    	$elements = array();
		$tab_selected = array();


		if (empty($domainsselected)){
    		$domainsselected='any';
		}

		// Selected skills
		$sql ="SELECT ob.*, sk.* FROM  {artefact_booklet_frskllresult} AS ob, {artefact_booklet_skill} AS sk
 WHERE  ob.idskill = sk.id
 AND ob.idobject = ?
 AND ob.idowner = ?
 ORDER BY sk.domain ASC, sk.code ASC ";
		if ($rec_skills = get_records_sql_array($sql, array(0, $USER->get('id')))){
			foreach ($rec_skills as $rec_skill){
    			$tab_selected[$rec_skill->id] = $rec_skill->id;
			}
		}

		// Domains
    	// Skills
    	$list_of_domains_selected = array();

    	if (!empty($domainsselected) && ($domainsselected!='any')){
			$tab_domainsselected = explode('-', $domainsselected);
			//print_object($tab_domainsselected);
			//exit;
			foreach($tab_domainsselected as $index_domainselected){
				if (isset($index_domainselected)){
    				$list_of_domains_selected[] = trim($index_domainselected);
				}
			}
		}

		//print_object($list_of_domains_selected);
		//exit;
		$sql = "SELECT DISTINCT domain FROM {artefact_booklet_skill} WHERE  (owner = ? OR owner = ?)  ORDER BY domain ASC";
    	$domains = get_records_sql_array($sql, array(0, $USER->get('id')));
    	//print_object($domains);
		//exit;

		if (!empty($domains)){
        	$nbdomains = count($domains);
			if ($nbdomains>1){
		    	$domain_options = array();
				$domain_selected = array();
				$d=0;

				if ($domainsselected=='any'){
					foreach ($domains as $domain){
	                	$domain_options[$d]=$domain->domain;
   		                $domain_selected[] = $d;
						$d++;
					}
				}
				else{
					foreach ($domains as $domain){
	                	$domain_options[$d]=$domain->domain;
						if (array_search($d, $list_of_domains_selected)){
    		                $domain_selected[] = $d;
						}
						$d++;
					}
				}
		        //print_object($domain_options);
				//exit;

    			$elementdomains['domainselect'] = array(
	        		'type' => 'select',
	    	    	'title' => '', //get_string('selectdomains','artefact.booklet'),
		        	'multiple' => true,
    		    	'options' => $domain_options,
        			'defaultvalue' => $domain_selected,
        			//'size' => count($domains),
	                'size' => 3,
    	            'description' => get_string('multiselect', 'artefact.booklet'),
	    		);

            	$elementdomains['submit'] = array(
            		'type' => 'submitcancel',
        	    	'value' => array(get_string('savedomainchoice','artefact.booklet'), get_string('cancel')),
    	         	'goto' => get_config('wwwroot') . '/artefact/booklet/index.php?id='.$idtab,
	        	);

		        $elementdomains['idobject'] = array(
                        'type' => 'hidden',
                        'value' => $idobject,
    	        );

				$elementdomains['idrecord'] = array(
                        'type' => 'hidden',
                        'value' => $idrecord,
    	        );

				$elementdomains['compositetype'] = array(
                    'type' => 'hidden',
                    'value' => $object->type,
        		);

   	    		$domainchoice = array(
            	    'name' => 'domainchoice',
                	'plugintype' => 'artefact',
	                'pluginname' => 'booklet',
    	    	    // 'validatecallback' => 'validate_selectlist',
        	    	'successcallback' => 'selectdomainsfree_submit',
            	    'renderer' => 'table',
                	'elements' => $elementdomains,
	            );
    	    	$compositeform['domainchoice'] = pieform($domainchoice);
			}
		}

		if (!empty($list_of_domains_selected)){
            $where='';
			$params = array();
			foreach($list_of_domains_selected as $d){
				if (!empty($where)){
					$where.=' OR domain = ? ';
				}
				else{
                    $where.=' domain = ? ';
				}
				$params[]= $domain_options[$d];
			}
            $params[]= 0;
            $params[]= $USER->get('id');
			$sql = "SELECT * FROM {artefact_booklet_skill} WHERE ".$where." AND (owner = ? OR owner = ?) ORDER BY code ASC";
		    $skills = get_records_sql_array($sql, $params);
		}
		else{
			$sql = "SELECT * FROM {artefact_booklet_skill} WHERE (owner = ? OR owner = ?) ORDER BY domain ASC, code ASC";
		    $skills = get_records_sql_array($sql, array(0, $USER->get('id')));
		}


		if (!empty($skills)){
			$i=0;
            $elementsskills = array();
        	foreach ($skills as $skill){
				if (!empty($tab_selected[$skill->id])){
                    $elementsskills['select'.$i] = array(
        		        	'type' => 'checkbox',
                			'defaultvalue' => $tab_selected[$skill->id],
		                	'title' => $skill->code,
        		        	//'description' => get_string('checked', 'artefact.booklet'),
           			);
				}
				else{
                    $elementsskills['select'.$i] = array(
        		        	'type' => 'checkbox',
                			'defaultvalue' => 0,
		                	'title' => $skill->code,
        		        	//'description' => '',
           			);
				}
                $elementsskills['html'.$i] = array(
                			'type' => 'html',
                			'value' => $skill->domain.'; '.$skill->description.'; ['.$skill->scale.'|'.$skill->threshold.']'."\n",
           		);
                $elementsskills['id'.$i] = array(
		                	'type' => 'hidden',
        		        	'value' => $skill->id,
           		);
                $elementsskills['type'.$i] = array(
		                	'type' => 'hidden',
        		        	'value' => $skill->type,
           		);

                $i++;
			}

	        $elementsskills['nbitems'] = array(
                	'type' => 'hidden',
                	'value' => $i,
    	    );

       		$elementsskills['submit'] = array(
            	'type' => 'submit',
            	'value' => get_string('savechecklist','artefact.booklet'),
        	);

			$elementsskills['delete'] = array(
                'type' => 'checkbox',
                'help' => false,
                'title' => get_string('deleteskills','artefact.booklet'),
                'defaultvalue' => 0,
                'description' => get_string('deleteskillsdesc','artefact.booklet'),
        	);

	        $elementsskills['idobject'] = array(
                        'type' => 'hidden',
                        'value' => $idobject,
            );
	        $elementsskills['idframe'] = array(
                        'type' => 'hidden',
                        'value' => $object->idframe,
            );

			$elementsskills['compositetype'] = array(
                    'type' => 'hidden',
                    'value' => $object->type,
        	);

            $elementsskills['domainsselected'] = array(
                    'type' => 'hidden',
                    'value' => $domainsselected,
            );

            $elementsskills['idtab'] = array(
        	            'type' => 'hidden',
            	        'value' => $idtab,
    	    );

            $elementsskills['idrecord'] = array(
                        'type' => 'hidden',
                        'value' => $idrecord,
    	    );

            $elementsskills['idframe'] = array(
        	            'type' => 'hidden',
            	        'value' => $object->idframe,
    	    );

    	    $choice = array(
                'name' => 'listchoice',
                'plugintype' => 'artefact',
                'pluginname' => 'booklet',
        	    // 'validatecallback' => 'validate_selectlist',
            	'successcallback' => 'selectskillfree_submit',
                'renderer' => 'table',
                'elements' => $elementsskills,
            );
        	$compositeform['choice'] = pieform($choice);
		}


		$aform = array(
			'name' => 'addskillform',
    	    'plugintype' => 'artefact',
        	'pluginname' => 'booklet',
	        'renderer' => 'table',
    	    'method' => 'post',
        	'successcallback' => 'objectafreeskill_submit',
			'elements' => array(

    	    	'optionnal' => array(
	    	    	'type' => 'fieldset',
		    		'name' => 'inputform',
					'title' => get_string ('addnewskill', 'artefact.booklet'),
	        		'collapsible' => true,
    	        	'collapsed' => true,
	    	        'legend' => get_string('addnewskill', 'artefact.booklet'),
            	    'elements' => array(

			        	'title' => array(
    	    		    	'type' => 'html',
        	        		'title' => get_string('titleobject','artefact.booklet'),
		    	            'value' => ((!empty($object)) ? $object->title : NULL),
        			    ),

	                	'skilldomain' => array(
		                    'type' => 'text',
    		                'title' => get_string('domain', 'artefact.booklet'),
        		            'size' => 40,
            		        'defaultvalue' => NULL,
                		    'rules' => array(
                    	    'required' => true,
	                    	),
    	                	'help' => true
        	        	),
            	    	'skillcode' => array(
                	    	'type' => 'text',
	                	    'title' => get_string('code', 'artefact.booklet'),
    	                	'size' => 20,
	        	            'defaultvalue' => NULL,
    	        	        'rules' => array(
        	        	        'required' => true,
            	        	),
	            	    ),
    	            	'skilltype' => array(
	        	            'type' => 'text',
    	        	        'title' => get_string('sktype', 'artefact.booklet'),
        	        	    'size' => 20,
            	        	'defaultvalue' => NULL,
	            	        'rules' => array(
    	                	    'required' => true,
        	        	    ),
							'description' => get_string('skilltype', 'artefact.booklet'),
    	            	),

	    	            'skilldescription' => array(
    	    	            'type' => 'wysiwyg',
        	    	        'rows' => 5,
            	    	    'cols' => 60,
                	    	'title' => get_string('descriptionlist', 'artefact.booklet'),
	                    	'defaultvalue' => get_string('skilldescriptionmodel', 'artefact.booklet'),
							'description' => get_string('skilldescriptiondesc', 'artefact.booklet'),
			                'rules' => array(
    			            	'required' => true,
        			        ),
               			),

		                'skillscale' => array(
    		                'type' => 'text',
        		            'title' => get_string('generalscale', 'artefact.booklet'),
            		        'size' => 60,
                		    'defaultvalue' => get_string('generalscalemodel', 'artefact.booklet'),
                    		'description' => get_string('generalscaledesc', 'artefact.booklet'),
	                    	'rules' => array(
    	                    	'required' => true,
	        	            ),
    	        	    ),
	    	            'skillthreshold' => array(
    	    	            'type' => 'text',
        	    	        'title' => get_string('threshold', 'artefact.booklet'),
            	    	    'size' => 10,
                	    	'defaultvalue' => get_string('thresholdscalemodel', 'artefact.booklet'),
                    		'description' => get_string('thresholdscaledesc', 'artefact.booklet'),
		                    'rules' => array(
    		                    'required' => true,
        		            ),
            		    ),

	     	    	    'submit' => array(
	         	           'type' => 'submitcancel',
	                        'value' => array(get_string('saveskill', 'artefact.booklet'), get_string('cancel')),
    	                    'goto' => get_config('wwwroot') . '/artefact/booklet/index.php?id='.$idtab,
        	    	    ),
					),
				),
    	        'name' => array(
                    'type' => 'hidden',
                    'value' => ((!empty($object)) ? $object->name : NULL),
        	    ),


            	'domainsselected' => array(
                    'type' => 'hidden',
                   	'value' => $domainsselected,
        		),

				'compositetype' => array(
                    'type' => 'hidden',
                    'value' => $object->type,
    	        ),

				'id' => array(
                    'type' => 'hidden',
                    'value' => $idobject
            	),
	           	'idtab' => array(
        	            'type' => 'hidden',
            	        'value' => $idtab,
   		        ),

				'idrecord' => array(
                        'type' => 'hidden',
                        'value' => $idrecord,
   	        	),
			),
    	);

		$compositeform['addform'] = pieform($aform);

    	$sform = array(
        	'name' => 'inputskills',
	        'plugintype' => 'artefact',
    	    'pluginname' => 'booklet',
    		'renderer' => 'table',
        	'method'      => 'post',
        	'successcallback' => 'objectfreeskills_submit',
		    'elements' => array(
    	    	'optionnal' => array(
	    	    	'type' => 'fieldset',
		    		'name' => 'inputform',
					'title' => get_string ('inputnewskills', 'artefact.booklet'),
	        		'collapsible' => true,
    	        	'collapsed' => true,
	    	        'legend' => get_string('inputnewskills', 'artefact.booklet'),
            	    'elements' => array(
	            		'skillsfree' => array(
	    	            'type' => 'textarea',
    	    	        'rows' => 10,
        	    	    'cols' => 100,
            	    	'title' => get_string('listofskills', 'artefact.booklet'),
            		    'defaultvalue' => '',
                    	'description' => get_string('inputlistofskillsmodel', 'artefact.booklet'),
						'help' => true
       	    		),
   		        	'submit' => array(
       	 	           'type' => 'submitcancel',
           	        		'value' => array(get_string('saveskills', 'artefact.booklet'), get_string('cancel')),
               	    	    'goto' => get_config('wwwroot') . '/artefact/booklet/index.php?id='.$idtab,
	               		),
					),
				),
           		'domainsselected' => array(
                	'type' => 'hidden',
            		'value' => $domainsselected,
	        	),

		        'compositetype' => array(
	    	        'type' => 'hidden',
   		    	    'value' => $object->type,
	       		),
    	       	'id' => array(
       		    	'type' => 'hidden',
           		    'value' => $idobject,
	    	    ),
   		        'idtab' => array(
       				'type' => 'hidden',
           			'value' => $idtab,
	   	        ),
				'idrecord' => array(
        	    	'type' => 'hidden',
   	        	    'value' => $idrecord,
	   		    ),
			),
    	);

    	$compositeform['skillsform'] = pieform($sform);

		//print_object($compositeform);
		//exit;
    	return $compositeform;
	}

}

// -------------------------------------
function selectdomainsfree_submit(Pieform $form, $values) {
    global $_SESSION;
	global $_SERVER;
	//print_object($values);
	//exit;
	// Domain selection
    $domainsselected='';

	if (isset($values['domainselect'])){
		foreach($values['domainselect'] as $a_domain){
	        $domainsselected.= $a_domain.'-';
		}
        $domainsselected=substr($domainsselected,0,strlen($domainsselected)-1);
		//echo "$domainsselected";
		//exit;
		if (!empty($domainsselected)){
            $goto = get_config('wwwroot') . '/artefact/booklet/freeskills.php?id=' . $values['idobject'] .'&idrecord='.$values['idrecord']. '&domainsselected='.$domainsselected;
		}
		else{
			$goto = get_config('wwwroot') . '/artefact/booklet/freeskills.php?id=' . $values['idobject'] .'&idrecord='.$values['idrecord'] . '&domainsselected=';
		}

	}
	else{
        $goto = $_SERVER['HTTP_REFERER'];
	}
	redirect($goto);
}

// -------------------------------------
function selectskillfree_submit(Pieform $form, $values) {
    global $_SESSION;
	global $USER;
    $displayorder = 0;
    $t_skillsfree=array();      // Liste des enregistrement nouvellement selectionnes
	$where='';
    $select='';
	$params = array();
	if (!empty($values['nbitems'])){
 		for ($i=0; $i<$values['nbitems']; $i++){
			if (!empty($values['select'.$i])){
				// Creer l'association
				$a_freeskill = new stdclass();
        	   	$a_freeskill->idskill = $values['id'.$i];
            	$a_freeskill->idobject = $values['idobject'];
	            $a_freeskill->idowner = $USER->get('id');
    	        $a_freeskill->value = 0;
        	    $a_freeskill->idrecord = $values['idrecord'];
            	//print_object($a_freeskill);

            	$t_skillsfree[]=$a_freeskill;
/*
				// formater la requete de suppression
				if (empty($where)){
					$where .= ' (idskill = ?) ';
				}
				else{
        	    	$where .= ' OR (idskill = ?) ';
				}
				$params[] = $values['id'.$i];
*/
			}
		}
	}
    if (!empty($t_skillsfree)){
        //print_object($t_skillsfree);
		//exit;
		foreach($t_skillsfree as $a_freeskill){
			$sql = "SELECT * FROM {artefact_booklet_frskllresult}
 WHERE idobject = ? AND idowner = ? AND idskill AND idrecord = ? ";
            if ($rec_frsk = get_record_sql($sql, array($a_freeskill->idobject, $USER->get('id'), $a_freeskill->idskill, $a_freeskill->idrecord))){
   				//print_object($rec_frsk);
                $a_freeskill->id = $rec_frsk->id;
				$a_freeskill->value = $rec_frsk->value;
                //print_object($a_freeskill);
				//exit;
                if (!$values['delete']){
					update_record('artefact_booklet_frskllresult', $a_freeskill);
				}
				else{
                    delete_records('artefact_booklet_frskllresult', 'id', $rec_frsk->id);
				}
			}
			else{
                if (!$values['delete']){
        	    	insert_record('artefact_booklet_frskllresult', $a_freeskill);
				}
			}
		}
	}

	$goto = get_config('wwwroot').'/artefact/booklet/index.php?idframe='.$values['idframe'].'&tab='.$values['idtab'].'&okdisplay=1';

	redirect($goto);

}

function objectafreeskill_submit(Pieform $form, $values){
    global $_SESSION;
	global $USER;
    if ($object = get_record('artefact_booklet_object', 'id', $values['id'])){
		//echo "<br />lib.php :: 3062 :: OBJECT <br >\n";
		//print_object($object);
		//print_object($values);
		//exit;
		if (!empty($values['domainsselected'])){
    		$goto = get_config('wwwroot') . '/artefact/booklet/freeskills.php?id=' . $object->id . '&domainsselected='.$values['domainsselected'];
		}
		else{
			$goto = get_config('wwwroot') . '/artefact/booklet/freeskills.php?id=' . $object->id . '&domainsselected=';
		}

		$skill = new stdclass();

		$ok=true;
		if ($ok && !empty($values['skilldomain'])){
            $skill->domain =  trim($values['skilldomain']);
  		}
		else{
            $ok=false;
		}
		if ($ok && isset($values['skillcode'])){
            $skill->code =  trim($values['skillcode']);
  		}
		else{
            $ok=false;
		}

		if ($ok && isset($values['skilltype'])){
            $skill->type =  trim($values['skilltype']);
  			if (($skill->type != 0) && ($skill->type != 1) && ($skill->type != 2)){
                $skill->type = '1';
			}
		}
		else{
            $ok=false;
		}

		if ($ok && !empty($values['skilldescription'])){
			$skill->description = $values['skilldescription'];
		}
		else{
            $ok=false;
		}

		if ($ok && !empty($values['skillscale'])){
			$scale= trim($values['skillscale']);

			$scale_str='';

			if ( preg_match("/\,/", $scale)){
            	$tscale=explode(",", $scale);
				foreach ($tscale as $avalue){
					if (isset($avalue)){
                        $avalue = trim($avalue);
						if (!empty($avalue)){
                    		$scale_str .= $avalue . ',';
						}
					}
				}
                $scale_str=substr($scale_str,0,strlen($scale_str)-1);
			}
			else{
                $scale_str=$scale;
			}

			if (!empty($scale_str)){
                $skill->scale = $scale_str;
			}
			else{
                $ok=false;
			}
		}
        else{
            $ok=false;
		}

        $threshold='';
		if ($ok && isset($values['skillthreshold'])){
		    $threshold = trim($values['skillthreshold']);
			if (! is_numeric($threshold)){
                $threshold = 0;
			}
			else{
                $tscale=explode(",", $skill->scale);
				if ($threshold > count($tscale)){
    	            $threshold = count($tscale);
				}
				elseif ($threshold<0){
                	$threshold = 0;
				}
			}
		    $skill->threshold = $threshold ;
		}
		else{
            $ok=false;
		}

			// DEBUG
			//echo "<br />DEBUG :: lib.php :: 3143 : SCALE INPUT : $scale ; SCALE OUTPUT : $scale_str<br />\n";
			//print_object($skill);
			//exit;

		// enregistrer le skill
		if ($ok){
            try {
					// Creer le skill
					if ($rec_skill = get_record('artefact_booklet_skill', 'domain', $skill->domain, 'code', $skill->code)){
						$idskill = $rec_skill->id;
	                	$skill->id = $rec_skill->id;
		            	update_record('artefact_booklet_skill', $skill);
					}
   					else{
                    	$skill->owner = $USER->get('id');
						$idskill = insert_record('artefact_booklet_skill', $skill, 'id', true);
					}
					// Creer l'association
					$a_listskill = new stdclass();
                    $a_listskill->idobject = $object->id;
		            $a_listskill->idskill = $idskill;
                    $a_listskill->idowner = $USER->get('id');
    		        $a_listskill->value = $skill->threshold;
                    $a_listskill->idrecord = $values['idrecord'];

        	    	if ($rec_skill = get_record('artefact_booklet_frskllresult', 'idobject',  $object->id, 'idskill',  $idskill, 'idowner', $USER->get('id'))){
	        	        $a_listskill->id = $rec_skill->id;
                        $a_listskill->value = $rec_skill->value;
                        $a_listskill->idrecord = $rec_skill->idrecord;
    	        	    update_record('artefact_booklet_frskllresult', $a_listskill);
					}
					else{
	                	insert_record('artefact_booklet_frskllresult', $a_listskill, 'id', true);
					}

		    }
    		catch (Exception $e) {
        		$SESSION->add_error_msg(get_string('skillsavefailed', 'artefact.booklet'));
    		}
		}
		else{
           	$SESSION->add_error_msg(get_string('skillsavefailed', 'artefact.booklet'));
		}
	}
	else{
		$goto = get_config('wwwroot').'/artefact/booklet/index.php?tab='.$values['idtab'].'&okdisplay=0';
	}
	redirect($goto);
}


function objectfreeskills_submit(Pieform $form, $values){
    global $_SESSION;
	global $USER;
    srand();
    if ($object = get_record('artefact_booklet_object', 'id', $values['id'])){
		//echo "<br />lib.php :: 1781 :: OBJECT <br >\n";
		//print_object($object);
		//print_object($values);
		//exit;

			if (!empty($values['skillsfree'])){
    	    	//
				if ($tlist=explode("\n", strip_tags($values['skillsfree']))){
					foreach ( $tlist as $line){
						if (!empty($line)){
							$skill = new stdclass();

							// "domain1;code1;description1;[scale_value_value11,scale_value12,scale_value13,...,scale_value1N;threshold1]
							if ($fields = explode("[", $line)){
		                        if (!empty($fields[0])){
    		                       	// "domain1;type1;code1;description1;
        		                    if ($domain_code_description = explode(";", $fields[0])){
                                        $nbfields=count($domain_code_description);
										if ( $nbfields<5){    // pas de type dans le donnees
											if (isset($domain_code_description[0])){
                		                		$skill->domain =  trim(str_replace("-","",$domain_code_description[0]));
												$current_domain = $skill->domain;
                    	    	       		}
											else{
                            	    	    	$skill->domain = $current_domain;
											}
			                                if (isset($domain_code_description[1])){
    			                                $skill->code = trim($domain_code_description[1]);
											}
											else{
                			                    $skill->code = strtoupper($current_domain).'_'.$object->id.'_'.rand();
											}
	                        		        if (isset($domain_code_description[2])){
    	                        		        $skill->description = trim($domain_code_description[2]);
											}
											else{
        	        	                    	$skill->description = get_string('unknown', 'artefact.booklet');
											}
               		                    	$skill->type = '1';
										}
										else{
											if (isset($domain_code_description[0])){
                		                		$skill->domain =  trim(str_replace("-","",$domain_code_description[0]));
												$current_domain = $skill->domain;
	                    	    	        }
											else{
        	                    	    	    $skill->domain = $current_domain;
											}
		        	                        if (isset($domain_code_description[1])){
    		        	                        $skill->type = trim($domain_code_description[1]);
											}
											else{
                		        	            $skill->type = '1';
											}
		                                	if (isset($domain_code_description[2])){
    		                                	$skill->code = trim($domain_code_description[2]);
											}
											else{
                		                    	$skill->code = strtoupper($current_domain).'_'.$object->id.'_'.rand();
											}
	                        	        	if (isset($domain_code_description[3])){
    	                        	        	$skill->description = trim($domain_code_description[3]);
											}
											else{
        		        	                    $skill->description = get_string('unknown', 'artefact.booklet');
											}
										}
									}
								}
    	                	    if (!empty($fields[1])){
	        	                    // scale_value_value11,scale_value12,scale_value13,...,scale_value1N|threshold1]
    	        	                if ($scale_threshold = explode("|", $fields[1])){
										if (!empty($scale_threshold[0])){
            	        	                // scale_value_value11,scale_value12,scale_value13,...,scale_value1N
                	        	        	$skill->scale = trim($scale_threshold[0]);
                    	        	    }
										else if (!empty($skill->type)){ // Type 1 necessite un bareme
	                        	            //$skill->scale = $values['scale'];
	                                        $skill->scale = get_string('generalscalemodel','artefact.booklet');
    	                                    $skill->threshold  = '3';
										}
        		                        if (!empty($scale_threshold[1])){
            		                        // threshold1]
											// Chasser ']'
                    		                $skill->threshold = trim(substr($scale_threshold[1],0,-1));
										}
									}
								}
							}
							$t_skill[] = $skill;
						}
					}
				}
			}
			// enregistrer les skills
			if (!empty($t_skill)){
				foreach ($t_skill as $a_skill){
					// Creer le skill
					if ($rec_skill = get_record('artefact_booklet_skill', 'domain', $a_skill->domain, 'code', $a_skill->code)){
						$idskill = $rec_skill->id;
	                	$a_skill->id = $rec_skill->id;
		            	update_record('artefact_booklet_skill', $a_skill);
					}
   					else{
                    	$a_skill->owner = $USER->get('id');
						$idskill = insert_record('artefact_booklet_skill', $a_skill, 'id', true);
					}
					// Creer l'association
					$a_listskill = new stdclass();
                    $a_listskill->idobject = $object->id;
		            $a_listskill->idskill = $idskill;
                    $a_listskill->idowner = $USER->get('id');
    		        $a_listskill->value = $a_skill->threshold;
                    $a_listskill->idrecord = $values['idrecord'];

        	    	if ($rec_skill = get_record('artefact_booklet_frskllresult', 'idobject',  $object->id, 'idskill',  $idskill, 'idowner', $USER->get('id'))){
	        	        $a_listskill->id = $rec_skill->id;
                        $a_listskill->value = $rec_skill->value;
                        $a_listskill->idrecord = $rec_skill->idrecord;
    	        	    update_record('artefact_booklet_frskllresult', $a_listskill);
					}
					else{
	                	insert_record('artefact_booklet_frskllresult', $a_listskill, 'id', true);
					}
				}
			}
			//$goto = get_config('wwwroot').'/artefact/booklet/index.php?idframe='.$object->idframe.'&tab='.$values['idtab'].'&okdisplay='.$values['okdisplay'];
			if (!empty($values['domainsselected'])){
    	        $goto = get_config('wwwroot') . '/artefact/booklet/freeskills.php?id=' . $object->id . '&domainsselected='.$values['domainsselected'];
			}
			else{
				$goto = get_config('wwwroot') . '/artefact/booklet/freeskills.php?id=' . $object->id . '&domainsselected=';
			}

			redirect($goto);

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




function visualization_submit(Pieform $form, $values) {
    // appele lors de la soumission de donnees d'un cadre en ajout ou modification
    // values est un vecteur qui contient les valeurs des champs du formulaire
    // quelques champs caches permettent de transmettre le contexte
    // idrecord est transmis quand c'est une modification d'un element de liste
    // idtab est le displayorder du tab soumis
    // idtome est l'id du tome selectionne ou en visualisation

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
	/*
	$sql6 = "SELECT MAX(idrecord) as ir FROM {artefact_booklet_lskillsresult} WHERE idowner = ?";
    $maxls = get_record_sql($sql6, array($USER->get('id')));

	$sql7 = "SELECT MAX(idrecord) as ir FROM {artefact_booklet_refresult} WHERE idowner = ?";
    $maxref = get_record_sql($sql6, array($USER->get('id')));
	*/
    $max = max(array($maxcb->ir, $maxrad->ir, $maxtext->ir, $maxda->ir, $maxaf->ir)) + 1;
    settype($max, 'integer');
    // $selectedtome = get_record('artefact_booklet_selectedtome', 'iduser', $USER->get('id'));
    // $tome = get_record('artefact_booklet_tome', 'id', $selectedtome->idtome);
    $tome = get_record('artefact_booklet_tome', 'id', $idtome);
    $temp = get_records_array('artefact_booklet_object');
    foreach ($form->get_elements() as $element) {

		if ($element['type']=='radio') {

			$is_radio_type = false;
            $is_listskills_type = false;
            $is_freeskills_type = false;
			foreach ($temp as $object) {
                if ('frsk' . $object->id == (substr($element['name'], 0, strpos($element['name'], '_')))) {
                    $idobject = $object->id;
                    $is_freeskills_type = true;
					continue;
                }
                elseif ('rlc' . $object->id == (substr($element['name'], 0, strpos($element['name'], '_')))) {
                    $idobject = $object->id;
                    $is_listskills_type = true;
					continue;
                }
				elseif ('ra' . $object->id == $element['name']) {
                    $idobject = $object->id;
                    $is_radio_type = true;
                    continue;
                }
            }

            if ($is_radio_type){
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
			else if ($is_freeskills_type){
			   	// DEBUG
				//echo "<br />lib.php :: 7579<br />\n";
				//print_object($element);
				//echo "<br />VALUES<br />\n";
                //print_object($values);
	            // idobject est l'id dans artefact_booklet_object du champ $element
				// astuce pour stocker l'id du skill dans l'id du radio bouton
                $idskill = trim(substr($element['name'], strrpos($element['name'] , '_') + 1 ));
                //echo "<br />SKILL ID: $idskill\n";
				//exit;
    	        $data = new StdClass;
        	    $data->idobject = $idobject;
            	$data->idowner = $USER->get('id');
	            $data->idskill = $idskill;
                $data->value = $values[$element['name']] + 1; // on incremente l'index pour l'enregistrer
    	        if ($idrecord) {
        	        $data->idrecord = $idrecord;
            	}
				//print_object($data);
				//exit;

                if (!$modifelementlist) {
    	            $count = count_records('artefact_booklet_frskllresult', 'idowner', $USER->get('id'), 'idobject', $idobject, 'idskill', $idskill);
        	    }
            	else {
                	$count = count_records('artefact_booklet_frskllresult', 'idowner', $USER->get('id'), 'idobject', $idobject, 'idskill', $idskill, 'idrecord', $idrecord);
	            }


	            try {
    	            if ($count == 0 || ($list && !$modifelementlist)) {
        	            // pas encore de valeur enregistree ou ajout d'une valeur de liste
            	        if (is_null($idrecord)) {
                	        $data->idrecord = $max;
							$idrecord=$max;
                    	}
	                    insert_record('artefact_booklet_frskllresult', $data);
    	            }
        	        else {
            	        if (!$list) {
                	        // n'est pas dans une liste
                    	    if (!$idrecord) {
                        	    $obj = get_record('artefact_booklet_frskllresult', 'idowner', $USER->get('id'), 'idobject', $idobject, 'idskill', $idskill);
                            	$idrecord = $obj->idrecord;
	                            $newidrecord = 0;
    	                    }
        	                update_record('artefact_booklet_frskllresult', $data, array('idobject'=> $idobject, 'idowner'=> $USER->get('id'), 'idskill'=> $idskill));
                	    }
                    	else {
                        	// est dans une liste
	                        update_record('artefact_booklet_frskllresult', $data, array('idobject'=> $idobject, 'idowner'=> $USER->get('id'), 'idskill'=> $idskill, 'idrecord'=> $idrecord));
        	            }
            	    }
            	}
            	catch(Exception $e) {
                	$errors['test'] = true;
	                $aff = $e;
            	}
			}
			else if ($is_listskills_type){
			   	// DEBUG
				//echo "<br />lib.php :: 5959<br />\n";
				//print_object($element);
				//echo "<br />VALUES<br />\n";
                //print_object($values);
	            // idobject est l'id dans artefact_booklet_object du champ $element
				// astuce pour stocker l'id du skill dans l'id du radio bouton
                $idskill = trim(substr($element['name'], strrpos($element['name'] , '_') + 1 ));
                //echo "<br />SKILL ID: $idskill\n";

    	        $data = new StdClass;
        	    $data->idobject = $idobject;
            	$data->idowner = $USER->get('id');
	            $data->idskill = $idskill;
                $data->value = $values[$element['name']] + 1; // on incremente l'index pour l'enregistrer
    	        if ($idrecord) {
        	        $data->idrecord = $idrecord;
            	}
				//print_object($data);
				//exit;

                if (!$modifelementlist) {
    	            $count = count_records('artefact_booklet_lskillsresult', 'idowner', $USER->get('id'), 'idobject', $idobject, 'idskill', $idskill);
        	    }
            	else {
                	$count = count_records('artefact_booklet_lskillsresult', 'idowner', $USER->get('id'), 'idobject', $idobject, 'idskill', $idskill, 'idrecord', $idrecord);
	            }


	            try {
    	            if ($count == 0 || ($list && !$modifelementlist)) {
        	            // pas encore de valeur enregistree ou ajout d'une valeur de liste
            	        if (is_null($idrecord)) {
                	        $data->idrecord = $max;
							$idrecord=$max;
                    	}
	                    insert_record('artefact_booklet_lskillsresult', $data);
    	            }
        	        else {
            	        if (!$list) {
                	        // n'est pas dans une liste
                    	    if (!$idrecord) {
                        	    $obj = get_record('artefact_booklet_lskillsresult', 'idowner', $USER->get('id'), 'idobject', $idobject, 'idskill', $idskill);
                            	$idrecord = $obj->idrecord;
	                            $newidrecord = 0;
    	                    }
        	                update_record('artefact_booklet_lskillsresult', $data, array('idobject'=> $idobject, 'idowner'=> $USER->get('id'), 'idskill'=> $idskill));
                	    }
                    	else {
                        	// est dans une liste
	                        update_record('artefact_booklet_lskillsresult', $data, array('idobject'=> $idobject, 'idowner'=> $USER->get('id'), 'idskill'=> $idskill, 'idrecord'=> $idrecord));
        	            }
            	    }
            	}
            	catch(Exception $e) {
                	$errors['test'] = true;
	                $aff = $e;
            	}
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
                            $obj = get_records_array('artefact_booklet_resultattachedfiles', 'idowner', $USER->get('id'), 'idobject', $idobject);
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
	// DEBUG
	//echo "<br />DEBUG :: lib.php :: 6313\n";
    //exit;
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

/********************** Hierarchical frames stuff **************************************/

/**
 * Collecte la liste des cadres associés à une page donnée en les restitutant dans l'ordre en profondeur d'abord
 * input : idtab : tab id
 * input : onlyids : if true return ids only which parent id is parentid
 *                   else return frames records
 * input : parentid : id of parent to match
 * output : array of frames or of frames ids
 */
function get_frames($idtab, $onlyids=false, $parentid=0){
    $result = array();

    $tabaff_parentids = array();
    $tabaff_codes = array();

	// Ordonner les frames selon leur frame parent et leur ordre d'affichage
	$recframes = get_records_sql_array('SELECT ar.* FROM {artefact_booklet_frame} ar WHERE ar.idtab = ? ORDER BY ar.idparentframe ASC, ar.displayorder ASC', array($idtab));
	// DEBUG
	//print_object( $frames);
	//exit;
	// REORDONNER sous forme d'arbre parcours en profondeur d'abord


	// 52 branches possibles a chaque niveau de l'arbre, cela devrait suffire ...
	$tcodes = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');

    $tabaff_ids = array();
    $tabaff_niveau = array();
	// Initialisation
    foreach ($recframes as $recframe) {
        if ($recframe){
            $tabaff_niveau[$recframe->id] = 0;
		}
	}
	// Initialisation
	$n=0;
	foreach ($recframes as $recframe) {
       	if ($recframe){
           	$tabaff_codes[$recframe->id] =$tcodes[$n];
			$n++;
		}
	}


	$niveau_courant = 0;
    $ordre_courant = 0;
    $parent_courant = 0;

	// Reordonner
    if ($recframes) {
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
	}
	asort($tabaff_codes);
 	if ($onlyids){
		foreach ($tabaff_codes as $key => $val){
            // echo "<br />DEBUG :: ".$key."=".$val."\n";
			if ($tabaff_parentids[$key] == $parentid){
                $result[] = $key;
			}
		}
	}
	else{
    	foreach ($tabaff_codes as $key => $val){
            // echo "<br />DEBUG :: ".$key."=".$val."\n";
            $result[] = get_record('artefact_booklet_frame', 'id', $key);
		}
	}
	return $result;
}

/**
 * Ordonne la liste des cadres associés à une page donnée dans l'ordre en profondeur d'abord
 * input : idtab : tab id
 * output : array of ids
 */
function get_frames_codes_ordered($idtab){
	global $tabaff_parendids;

	// Ordonner les frames selon leur frame parent et leur ordre d'affichage
	if ($recframes = get_records_sql_array('SELECT ar.id, ar.displayorder, ar.idparentframe FROM {artefact_booklet_frame} ar WHERE ar.idtab = ? ORDER BY ar.idparentframe ASC, ar.displayorder ASC', array($idtab))){
		// REORDONNER sous forme d'arbre parcours en profondeur d'abord
	    $tabaff_ids = array();
		$tabaff_displayorders = array();
	    $tabaff_parendids = array();
		$tabaff_niveau = array();
		$tabaff_codes = array();
		// Initialisation
    	foreach ($recframes as $recframe) {
        	if ($recframe){
            	$tabaff_niveau[$recframe->id] = 0;
			}
		}


		// 52 branches possibles a chaque niveau dee l'arbre, cela devrait suffire ...
		$tcodes = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
	    $niveau_courant = 0;
    	$ordre_courant = 0;
	    $parent_courant = 0;

		// Initialisation
		$n=0;
   		foreach ($recframes as $recframe) {
       		if ($recframe){
           		$tabaff_codes[$recframe->id] = $tcodes[$n];
				$n++;
			}
		}

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
		return ($tabaff_codes);
	}
}

/****************************************** Lists stuff *****************************************************/
/*
'artefact_booklet_object'
			 id
			 ^
			 |
		   1:1
------------|
|   'artefact_booklet_list' <-- n ::'artefact_booklet_listofskills' :: m --> 'artefact_booklet_skill'
-->   'idobject',  <--------------> 'idlist'                                    'code'
'description'                       'idskill'  <-------------------->           'id'
									'id'     					                'description'
                                    'displayorder'                              'scale'

                                                                                'displayorder'
									^
									|
!------------------------------------
v
 :: -->	'artefact_booklet_lskillsresult'
        'idobject'
        'idowner'
        'idskill'
        'value'
        'idrecord'
*/

/********************** Skills stuff **************************************/

function get_skill_choice_display($rec_skill, $index_threshold=0){
	$str_choice = '';
	$noptions=0;
    $tab_scale = array();
	if (!empty($rec_skill)){
		if ($tab_scale = explode(",", $rec_skill->scale)){
			for ($j=0; $j<count($tab_scale); $j++){
        	    $a_scale_element = trim($tab_scale[$j]);
				if (!empty($a_scale_element)){
					if ($index_threshold == $noptions){
                		if ($str_choice){
							$str_choice .= ' | <span class="blueback"><b>'.$a_scale_element.'</b></span>';
						}
						else{
        	            	$str_choice .= '<span class="blueback"><b>'.$a_scale_element.'</b></span>';
						}
					}
					else if ($rec_skill->threshold == $noptions){
                		if ($str_choice){
							$str_choice .= ' | <span class="lightback"><i>'.$a_scale_element.'</i></span>';
						}
						else{
	        	        	$str_choice .= '<span class="lightback"><i>'.$a_scale_element.'</i></span>';
						}
					}
					else{
			    		if ($str_choice){
							$str_choice .= ' | <span class="lightback">'.$a_scale_element.'</span>';
						}
						else{
                        	$str_choice .= '<span class="lightback">'.$a_scale_element.'</span>';
						}
					}
                    $noptions++;
                }
			}
		}
	}
	return $str_choice;
}


/****************************** Reference stuff **************************************/
/**
 *  Display an obejct linked by a reference object
 *
 *  @input : object id
 *  @input : author : user id
 *  @output : string
 *
 */

function display_object_linked($idobject, $author){
	$rslt='';

	// DEBUG
	if ($object = get_record('artefact_booklet_object', 'id', $idobject)){
/*
		if ($object->type == 'longtext' || $object->type == 'shorttext' || $object->type == 'area' || $object->type == 'htmltext') { // || $object->type == 'synthesis') {
			$rslt .= 'ID : '.$object->id . '  '. $object->title. ': TYPE TEXT';
		}
		else{
        	$rslt .= 'ID : '.$object->id . '  '. $object->title. ': '.$object->type;
		}
*/
		if ($object->type == 'longtext' || $object->type == 'shorttext' || $object->type == 'area' || $object->type == 'htmltext'){ // || $object->type == 'synthesis') {
        			$val = get_record('artefact_booklet_resulttext', 'idowner', $author, 'idobject', $object->id);
                    if ($val && $val -> value) {
                        $rslt .= $val -> value;
                    }
    	}
	    else if ($object->type == 'listskills') {
                    $sql = "SELECT re.*, rs.*  FROM {artefact_booklet_lskillsresult} re
                            JOIN {artefact_booklet_skill} rs
                            ON (rs.id = re.idskill)
                            WHERE re.idobject = ?
                            AND re.idowner = ?";
                    $skills = get_records_sql_array($sql, array($object->id, $this->author));
                    $i = 0;
					if (!empty($skills)){
                        $rslt .= "<table>\n";
                     	foreach ($skills as $skill){
                            $value = $skill->value;
                            $str_evaluation = '';
							if (!empty($value)){
                                $value--;
                                if ($tab_scale=explode(",", $skill->scale)){
                                	$str_evaluation = $tab_scale[$value];
								}
							}
                        	$rslt .= '<tr><td>'.$skill->domain.'</td><td><b>'.$skill->code.'</b><td><i>'. $str_evaluation.'</i></td></tr><tr><td colspan="3">'. $skill->description.'</td></tr>'."\n";
                    	}
                        $rslt .= "</table>\n";
					}
		}
        /*
		// on ne va pas traiter des references de references !
		else if ($object->type == 'reference') {
					$reference = get_record('artefact_booklet_refresult', 'idowner', $author, 'idobject', $object->id);
                   	if ($reference && $reference->idreference) {
                        $val = get_record('artefact_booklet_reference', 'id', $radio->idreference);
                        $rslt .= "\n<tr><th>". $object -> title . "</th>";
                        $rslt .= "<td>";
                        $rslt .= display_object_linked($val->idobjectlinked);
                    }
        }
		*/
		else if ($object->type == 'radio') {
                    $radio = get_record('artefact_booklet_resultradio', 'idowner', $author, 'idobject', $object->id);
                    if ($radio && $radio->idchoice) {
                        $val = get_record('artefact_booklet_radio', 'id', $radio->idchoice);
                        $rslt .= $val -> option;
                    }
		}
		else if ($object->type == 'checkbox') {
                    $coche = get_record('artefact_booklet_resultcheckbox', 'idowner', $author, 'idobject', $object->id);
                    if ($coche) {
                        $rslt .= ($coche->value ? get_string('true', 'artefact.booklet')  : get_string('false', 'artefact.booklet') );
                    }
		}
		else if ($object->type == 'date') {
                    $date = get_record('artefact_booklet_resultdate', 'idowner', $author, 'idobject', $object->id);
                    if ($date) {
                        //$rslt .= "\n<tr><th>". $object -> title . "</th>";
                        //$rslt .= "<td>";
                        $rslt .= format_date(strtotime($date->value), 'strftimedate') ;
                    }
		}
		else if ($object->type == 'attachedfiles') {
                    $sql = "SELECT * FROM {artefact_booklet_resultattachedfiles}
                            WHERE idobject = ?
                            AND idowner = ?";
                    $attachedfiles = get_records_sql_array($sql, array($object->id, $author));

                    $rslt .= "<table>";
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
	}
	return $rslt;
}





