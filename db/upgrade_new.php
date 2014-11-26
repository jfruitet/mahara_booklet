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

    if ($oldversion < 2014091200) {
        $table = new XMLDBTable('artefact_booklet_document');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('idframe', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
		$table->addFieldInfo('artefact', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('framefk', XMLDB_KEY_FOREIGN, array('idframe'), 'artefact_booklet_frame', array('id'));
        $table->addKeyInfo('artefactfk', XMLDB_KEY_FOREIGN, array('artefact'), 'artefact', array('id'));

        if (!create_table($table)) {
            throw new SQLException($table . " could not be created, check log for errors.");
        }
    }


    return $status;
}

