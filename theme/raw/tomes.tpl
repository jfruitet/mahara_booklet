{include file="header.tpl"}
<div id="bookletwrap">


<fieldset><legend>{str tag='tomes' section='artefact.booklet'}</legend>
<table id="tomelist" class="tablerenderer tomecomposite">
    <thead>
        <tr>
            <!-- <th class="tomecontrols"></th> -->
            <th class="nom">{str tag='tomesname' section='artefact.booklet'}</th>
            <th class="tomecontrols"></th>
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
{$tomeform|safe}

</fieldset>



</div>
{include file="footer.tpl"}
