{include file="header.tpl"}
<div id="bookletwrap">

{$optionsform.form|safe}

{if $synthese || $radio  || $reference}

<fieldset><legend>{str tag='options' section='artefact.booklet'}</legend>
{if $radio}<table id="radiolist" class="tablerenderer optioncomposite">{/if}
{if $synthese}<table id="synthesislist" class="tablerenderer optioncomposite">{/if}
{if $reference}<table id="referencelist" class="tablerenderer optioncomposite">{/if}
    <thead>
        <tr>
            <!-- <th class="optioncontrols"></th> -->
            {if $radio} <th class="nom">{str tag='optionsname' section='artefact.booklet'}</th>{/if}
            {if $synthese}<th class="nom">{str tag='fieldlinkedname' section='artefact.booklet'}</th>{/if}
			{if $reference}<th class="nom">{str tag='fieldlinkedname' section='artefact.booklet'}</th>{/if}
            <th class="optioncontrols"></th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
        <tr>
            <td class="buttonscell"></td>
            {if $radio} <td class="toggle">{$row->option}</td>{/if}
            {if $synthese} <td class="toggle">{$row->name}</td>{/if}
			{if $reference} <td class="toggle">{$row->name}</td>{/if}
            <td class="buttonscell"></td>
        </tr>
        {/foreach}
    </tbody>
</table>

{$optionsform.choice|safe}
</fieldset>

{/if}

{if $listskills}
	{if $optionsform.skillsform}
		<br />
			{$optionsform.skillsform|safe}
	{/if}	
	{if $optionsform.domainchoice}
		<br />
		<fieldset><legend>{str tag='selectdomains' section='artefact.booklet'}</legend>
			{$optionsform.domainchoice|safe}
		</fieldset>
	{/if}	
	<br />	
	<fieldset><legend>{str tag='selectskills' section='artefact.booklet'}</legend>
		{$optionsform.choice|safe}
	</fieldset>
{/if}
{$optionsform.visuaform|safe}


</div>
{include file="footer.tpl"}
