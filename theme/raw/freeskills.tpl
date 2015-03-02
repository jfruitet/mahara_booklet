{include file="header.tpl"}
<div id="bookletwrap">

{$optionsform.form|safe}

	{if $optionsform.skillsform}
		<fieldset><legend>{str tag='inputnewskills' section='artefact.booklet'}</legend>
			{$optionsform.skillsform|safe}
		</fieldset>
	{/if}	
	{if $optionsform.domainchoice}
		<fieldset><legend>{str tag='selectdomains' section='artefact.booklet'}</legend>
			{$optionsform.domainchoice|safe}
		</fieldset>
	{/if}		
	<fieldset><legend>{str tag='selectskills' section='artefact.booklet'}</legend>
		{$optionsform.choice|safe}
	</fieldset>

</div>
{include file="footer.tpl"}
