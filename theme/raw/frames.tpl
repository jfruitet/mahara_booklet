{include file="header.tpl"}
<div id="bookletwrap">

{$framesform.tabname|safe}

<fieldset><legend>{str tag='frames' section='artefact.booklet'}</legend>
<table id="framelist" class="tablerenderer framecomposite">
    <thead>
        <tr>
            <th class="framecontrols"></th>
            <th class="nom">{str tag='framesname' section='artefact.booklet'}</th>
            <th class="nom">{str tag='islist' section='artefact.booklet'}</th>
            <th class="framecontrols"></th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
        <tr>
            <td class="buttonscell"></td>
            <td class="toggle">{$row->title}</td>
            <td class="toggle">{$row->list}</td>
            <td class="buttonscell"></td>
        </tr>
        {/foreach}
    </tbody>
</table>

{$framesform.addframe|safe}
</fieldset>


{$framesform.visuaform|safe}



</div>
{include file="footer.tpl"}
