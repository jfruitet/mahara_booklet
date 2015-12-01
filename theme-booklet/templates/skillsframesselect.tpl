{include file="header.tpl"}
<div id="bookletwrap">
	{if $optionsform.framesform}
		{$optionsform.framesform|safe}
	{/if}	

	{if $optionsform.domainchoice}
		<fieldset><legend>{str tag='selectdomains' section='artefact.booklet'}</legend>
			{$optionsform.domainchoice|safe}
		</fieldset>
	{/if}	
	
	<fieldset><legend>{str tag='selectskillsfromframes' section='artefact.booklet'}</legend>
		{$optionsform.choice|safe}
	</fieldset>

</div>
{include file="footer.tpl"}
