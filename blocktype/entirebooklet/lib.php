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

class PluginBlocktypeEntirebooklet extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.booklet/entirebooklet');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.booklet/entirebooklet');
    }

    public static function get_categories() {
        return array('booklet');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        require_once(get_config('docroot') . 'artefact/lib.php');
        $smarty = smarty_core();
        $result = '';
        if ($artefacts = get_records_sql_array('
            SELECT va.artefact, a.artefacttype
            FROM {view_artefact} va
            INNER JOIN {artefact} a ON (va.artefact = a.id)
            WHERE va.view = ?
            AND va.block = ?', array($instance->get('view'), $instance->get('id')))) {
            foreach ($artefacts as $artefact) {
                $bookletfield = $instance->get_artefact_instance($artefact->artefact);
                $rendered = $bookletfield->render_self(array('viewid' => $instance->get('view')));
                $result .= $rendered['html'];
                if (!empty($rendered['javascript'])) {
                    $result .= '<script type="text/javascript">' . $rendered['javascript'] . '</script>';
                }
                $smarty->assign($artefact->artefacttype, $result);
            }
        }
        return $smarty->fetch('blocktype:entirebooklet:content.tpl');
    }

    // Yes, we do have instance config. People are allowed to specify the title
    // of the block, nothing else at this time. So in the next two methods we
    // say yes and return no fields, so the title will be configurable.
    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form() {
        return array();
    }

    public static function artefactchooser_element($default=null) {
    }

    /**
     * Subscribe to the blockinstancecommit event to make sure all artefacts
     * that should be in the blockinstance are
     */
    public static function get_event_subscriptions() {
        return array(
            (object)array(
                'event'        => 'blockinstancecommit',
                'callfunction' => 'ensure_booklet_artefacts_in_blockinstance',
            ),
        );
    }

    // appelé à la création d'un blockinstance (au moment du drag and drop)
    public static function ensure_booklet_artefacts_in_blockinstance($event, $blockinstance) {
        global $USER;
        $selectedtome = get_record('artefact_booklet_selectedtome', 'iduser', $USER->get('id'));
        if ($selectedtome && $blockinstance->get('blocktype') == 'entirebooklet') {
            safe_require('artefact', 'booklet');
            $artefacttypes = implode(', ', array_map('db_quote', PluginArtefactbooklet::get_artefact_types()));
            // Get all artefacts that are booklet related and belong to the correct owner
            $artefacts = get_records_sql_array('
                SELECT id
                FROM {artefact}
                WHERE artefacttype = \'visualization\'
                AND note = ?
                AND "owner" = (
                    SELECT "owner"
                    FROM {view}
                    WHERE id = ? )',
            array($selectedtome->idtome, $blockinstance->get('view')));
            if ($artefacts) {
                // Make sure they're registered as being in this view
                foreach ($artefacts as $artefact) {
                    $record = (object)array(
                        'view' => $blockinstance->get('view'),
                        'artefact' => $artefact->id,
                        'block' => $blockinstance->get('id'),
                    );
                    ensure_record_exists('view_artefact', $record, $record);
                }
            }
        }
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    /**
     * Entirebooklet blocktype is only allowed in personal views, because
     * there's no such thing as group/site booklet
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

}
