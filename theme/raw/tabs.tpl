{include file="header.tpl"}
<div id="bookletwrap">

{$tabsform.tabname|safe}

<fieldset><legend>{str tag='tabs' section='artefact.booklet'}</legend>
<table id="tablist" class="tablerenderer tabcomposite">
    <thead>
        <tr>
            <th class="tabcontrols"></th>
            <th class="nom">{str tag='tabsname' section='artefact.booklet'}</th>
            <th class="tabcontrols"></th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
        <tr>
            <td class="buttonscell"></td>
            <td class="toggle">{$row->title}</td>
            <td class="buttonscell"></td>
        </tr>
        {/foreach}
    </tbody>
</table>

{$tabsform.addtab|safe}
</fieldset>



{$tabsform.visua|safe}


</div>
{include file="footer.tpl"}
