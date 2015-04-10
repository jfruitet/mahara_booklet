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

defined('INTERNAL') || die();

class PluginBlocktypeSkillFrame extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.booklet/skillframe');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.booklet/skillframe');
    }

    public static function get_categories() {
        return array('booklet');
    }

    /**
     * Optional method. If exists, allows this class to decide the title for
     * all blockinstances of this type
     */
    public static function get_instance_title(BlockInstance $bi) {
        $configdata = $bi->get('configdata');

        if (!empty($configdata['artefactid'])) {
            return $bi->get_artefact_instance($configdata['artefactid'])->get('title');
        }
        return '';
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        require_once(get_config('docroot') . 'artefact/lib.php');
        safe_require('artefact','booklet');
        $smarty = smarty_core();
        $configdata = $instance->get('configdata');
		$configdata['viewid'] = $instance->get('view');
        // Get data about the booklet field in this blockinstance
        if (!empty($configdata['artefactid'])) {
            if ($bookletfield = $instance->get_artefact_instance($configdata['artefactid'])){
            	$rendered = $bookletfield->render_self($configdata);
            	$result = $rendered['html'];
            	if (!empty($rendered['javascript'])) {
                	$result .= '<script type="text/javascript">' . $rendered['javascript'] . '</script>';
            	}
            	return $result;
			}
			else{
                return 'Erreur lib.php :: 58 :: render_instance()';
			}
        }
        return 'Erreur lib.php :: 561 :: render_instance()';
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');

        $form = array();
        // Which booklet field does the user want
        $form[] = self::artefactchooser_element((isset($configdata['artefactid'])) ? $configdata['artefactid'] : null);

		$form['message'] = array(
            'type' => 'html',
            'value' => get_string('filloutyourbookletskill', 'blocktype.booklet/skillframe', '<a href="' . get_config('wwwroot') . 'artefact/booklet/">', '</a>'),
        );


        return $form;
    }

    public static function instance_config_save($values) {
        unset($values['message']);
        return $values;
    }


    public static function artefactchooser_element($default=null) {
        safe_require('artefact', 'booklet');
        return array(
            'name'  => 'artefactid',
            'type'  => 'artefactchooser',
            'title' => get_string('fieldtoshow', 'blocktype.booklet/skillframe'),
            'defaultvalue' => $default,
            'blocktype' => 'skillframe',
            'limit'     => 655360, // 640K profile fields is enough for anyone!
            'selectone' => true,
            'search'    => false,
            'artefacttypes' => array('skillframe'),
            'template'  => 'artefact:booklet:artefactchooser-element.tpl',
        );
    }

    /**
     * Deliberately enforce _no_ sort order. The database will return them in
     * the order they were inserted, which means roughly the order that they
     * are listed in the profile screen
     */
    public static function artefactchooser_get_sort_order() {
        return '';
    }

    public static function rewrite_booklet_config(View $view, $configdata) {
        $artefactid = null;
        if ($view->get('owner') !== null) {
            $artefacttype = null;
            if (!empty($configdata['artefactid'])) {
                $artefacttype = get_field('artefact', 'artefacttype', 'id', $configdata['artefactid']);
            }
            // @todo get artefacttype from a different field when copying from institution or group view.
            if ($artefacttype) {
                $artefactid = get_field('artefact', 'id', 'artefacttype', $artefacttype, 'owner', $view->get('owner'));
            }
        }
        $configdata['artefactid'] = $artefactid;
        return $configdata;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    /**
     * bookletfield blocktype is only allowed in personal views, because
     * there's no such thing as group/site booklets
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

    /**
     * Export the name of the booklet field being exported instead of a
     * reference to the artefact ID - mainly so that the fake "contact
     * information" field (which isn't exported) gets handled properly.
     *
     * @param BlockInstance $bi The blockinstance to export the config for.
     * @return array The config for the blockinstance
     */
    public static function export_blockinstance_config_leap(BlockInstance $bi) {
        $configdata = $bi->get('configdata');
        $result = array();

        if (!empty($configdata['artefactid'])) {
            if ($artefacttype = get_field('artefact', 'artefacttype', 'id', $configdata['artefactid'])) {
                $result['artefacttype'] = json_encode(array($artefacttype));
            }
        }

        return $result;
    }

    /**
     * Load the artefact ID for the field based on the field name that is in
     * the config (see export_blockinstance_config_leap).
     *
     * @param array $biconfig   The block instance config
     * @param array $viewconfig The view config
     * @return BlockInstance The newly made block instance
     */
    public static function import_create_blockinstance_leap(array $biconfig, array $viewconfig) {
        $configdata = array();

        // This blocktype is only allowed in personal views
        if (empty($viewconfig['owner'])) {
            return;
        }
        $owner = $viewconfig['owner'];

        if (isset($biconfig['config']) && is_array($biconfig['config'])) {
            $impcfg = $biconfig['config'];
            if (!empty($impcfg['artefacttype'])) {
                if ($artefactid = get_field_sql("SELECT id
                    FROM {artefact}
                    WHERE \"owner\" = ?
                    AND artefacttype = ?
                    AND artefacttype IN (
                        SELECT name
                        FROM {artefact_installed_type}
                        WHERE plugin = 'booklet'
                    )", array($owner, $impcfg['artefacttype']))) {
                    $configdata['artefactid'] = $artefactid;
                }
            }
        }

        $bi = new BlockInstance(0,
            array(
                'blocktype'  => $biconfig['type'],
                'configdata' => $configdata,
            )
        );

        return $bi;
    }

}
