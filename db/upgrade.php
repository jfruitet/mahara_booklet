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

defined('INTERNAL') || die();

function xmldb_artefact_booklet_upgrade($oldversion=0) {
    $status = true;

    if ($oldversion < 2014022800) {
        $table = new XMLDBTable('artefact_booklet_resultattachedfiles');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('idobject', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->addFieldInfo('idowner', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->addFieldInfo('artefact', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->addFieldInfo('idrecord', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('objectfk', XMLDB_KEY_FOREIGN, array('idobject'), 'artefact_booklet_object', array('id'));
        $table->addKeyInfo('ownerfk', XMLDB_KEY_FOREIGN, array('idowner'), 'usr', array('id'));
        $table->addKeyInfo('artefactfk', XMLDB_KEY_FOREIGN, array('artefact'), 'artefact', array('id'));

        if (!create_table($table)) {
            throw new SQLException($table . " could not be created, check log for errors.");
        }
    }

    if ($oldversion < 2014112901) {
        $table = new XMLDBTable('artefact_booklet_author');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('idtome', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->addFieldInfo('authormail', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->addFieldInfo('authorfirstname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->addFieldInfo('authorlastname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->addFieldInfo('authorinstitution', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->addFieldInfo('authorurl', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->addFieldInfo('key', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->addFieldInfo('version', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL);
        $table->addFieldInfo('timestamp', XMLDB_TYPE_DATETIME);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('tomefk', XMLDB_KEY_FOREIGN, array('idtome'), 'artefact_booklet_tome', array('id'));

        if (!create_table($table)) {
            throw new SQLException($table . " could not be created, check log for errors.");
        }

    }

    if ($oldversion < 2014112902) {
        $table = new XMLDBTable('artefact_booklet_author');
        $field = new XMLDBField('copyright');
        $field->setAttributes(XMLDB_TYPE_TEXT);
        $status = $status && add_field($table, $field);
    }

    if ($oldversion < 2014112903) {
        $table = new XMLDBTable('artefact_booklet_tome');
        $field = new XMLDBField('status');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'public');
        $status = $status && add_field($table, $field);

    }

    if ($oldversion < 2015012200) {
        $table = new XMLDBTable('artefact_booklet_frame');
        $field = new XMLDBField('idparentframe');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'displayorder');
        $status = $status && add_field($table, $field);
    }


    if ($oldversion < 2015021310) {

        $table = new XMLDBTable('artefact_booklet_list');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('idobject', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->addFieldInfo('description', XMLDB_TYPE_TEXT, XMLDB_NOTNULL);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('objectfk', XMLDB_KEY_FOREIGN, array('idobject'), 'artefact_booklet_object', array('id'));
        if (!create_table($table)) {
            throw new SQLException($table . " could not be created, check log for errors.");
        }


        $table1 = new XMLDBTable('artefact_booklet_skill');

        $table1->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
		$table1->addFieldInfo('domain', XMLDB_TYPE_CHAR, '255', null, null);
        $table1->addFieldInfo('code', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table1->addFieldInfo('description', XMLDB_TYPE_TEXT, XMLDB_NOTNULL);
        $table1->addFieldInfo('scale', XMLDB_TYPE_CHAR, '255', null, null);
        $table1->addFieldInfo('threshold', XMLDB_TYPE_INTEGER, '10', null, null);

        $table1->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!create_table($table1)) {
            throw new SQLException($table1 . " could not be created, check log for errors.");
        }

        $table2 = new XMLDBTable('artefact_booklet_listofskills');

        $table2->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table2->addFieldInfo('idlist', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table2->addFieldInfo('idskill', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table2->addFieldInfo('displayorder', XMLDB_TYPE_INTEGER, '10', null, null);

		$table2->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table2->addKeyInfo('listfk', XMLDB_KEY_FOREIGN, array('idlist'), 'artefact_booklet_list', array('id'));
        $table2->addKeyInfo('skillfk', XMLDB_KEY_FOREIGN, array('idskill'), 'artefact_booklet_skill', array('id'));
        if (!create_table($table2)) {
            throw new SQLException($table2 . " could not be created, check log for errors.");
        }

        // ATTENTION le nom des index doit être unique pour la BD
		// Comme MySql les génère à partir du nom des tables il faut que ce nom soit unique
		// resultlskills provoque une erreur aussi j'ai renommé la table lskillsresult
        $table3 = new XMLDBTable('artefact_booklet_lskillsresult');

      	$table3->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table3->addFieldInfo('idobject', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table3->addFieldInfo('idowner', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table3->addFieldInfo('idskill', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table3->addFieldInfo('value', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table3->addFieldInfo('idrecord', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);


        $table3->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table3->addKeyInfo('objectfk', XMLDB_KEY_FOREIGN, array('idobject'), 'artefact_booklet_object', array('id'));
        $table3->addKeyInfo('ownerfk', XMLDB_KEY_FOREIGN, array('idowner'), 'usr', array('id'));
        $table3->addKeyInfo('skillfk', XMLDB_KEY_FOREIGN, array('idskill'), 'artefact_booklet_skill', array('id'));

        if (!create_table($table3)) {
            throw new SQLException($table3 . " could not be created, check log for errors.");
        }

    }


    return $status;
}

