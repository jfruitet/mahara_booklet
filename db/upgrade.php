<?php

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
        $field = new XMLDBField('copyrigth');
        $field->setAttributes(XMLDB_TYPE_TEXT);
        $status = $status && add_field($table, $field);
    }

    if ($oldversion < 2014112903) {
        $table = new XMLDBTable('artefact_booklet_tome');
        $field = new XMLDBField('status');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'public');
        $status = $status && add_field($table, $field);

    }

    return $status;
}

