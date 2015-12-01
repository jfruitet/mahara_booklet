{include file="header.tpl"}
<div id="bookletwrap">
	{if $optionsform.domainchoice}
		<fieldset><legend>{str tag='selectdomains' section='artefact.booklet'}</legend>
			{$optionsform.domainchoice|safe}
		</fieldset>
	{/if}	
	
	{if $optionsform.addform}
		<br />
			{$optionsform.addform|safe}
	{/if}	

	{if $optionsform.skillsform}
		<br />
			{$optionsform.skillsform|safe}
	{/if}	

	<fieldset><legend>{str tag='selecteditskills' section='artefact.booklet'}</legend>
		{$optionsform.choice|safe}
	</fieldset>

</div>
{include file="footer.tpl"}
