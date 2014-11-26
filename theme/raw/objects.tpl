{include file="header.tpl"}
<div id="bookletwrap">

{$objectsform.framename|safe}

<fieldset><legend>{str tag='objects' section='artefact.booklet'}</legend>
<table id="objectlist" class="tablerenderer objectcomposite">
    <thead>
        <tr>
            <th class="objectcontrols"></th>
            <th class="nom">{str tag='nomobjects' section='artefact.booklet'}</th>
            <th class="type">{str tag='typeobjects' section='artefact.booklet'}</th>
            <th class="type">{str tag='nameobjects' section='artefact.booklet'}</th>
            <th class="objectcontrols"></th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
        <tr>
            <td class="buttonscell"></td>
            <td class="toggle">{$row->title}</td>
            <td>{$row->type}</td>
            <td>{$row->title}</td>
            <td class="buttonscell"></td>
        </tr>
        {/foreach}
    </tbody>
</table>

{$objectsform.addobject|safe}
</fieldset>

{$objectsform.visuaform|safe}



</div>
{include file="footer.tpl"}
