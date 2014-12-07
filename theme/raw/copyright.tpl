{include file="header.tpl"}
<div id="bookletwrap">
{if $idtome}
	 <h3>{$statusmodif|safe} : <i>{$statusvalue|safe}</i></h3>
	<div class="rbuttons">
		<a href="{$WWWROOT}artefact/booklet/author.php?id={$idtome}" title="{str tag=edit}">
			<img src="{theme_url filename='images/btn_access.png'}" alt="{str (tag=information  section=artefact.booklet)|escape:html|safe}">
		</a>
	</div>
{/if}	
{if $idtome}
    <ul><b>{$authortitle|safe}</b>
{if $authorlastname}
	<li>{$authorfirstname|safe} {$authorlastname|safe}</li>
{/if}
{if $authormail}
	<li>&lt;{$authormail|safe}&gt;</li>
{/if}
{if $authorinstitution}		
		<li>{$authorinstitution|safe}</li>
{/if}		
{if $version}
		<li>{str (tag=version section=artefact.booklet)|safe} : {$version|safe} - {$dateversion|safe}</li>
{/if}
{if $authorurl}
	<li>{$authorurl|safe}</li>
{/if}
{if $copyright} 	
	<li>{$copyright|safe}</li>
{/if}
	</ul>
{/if}


</div>
{include file="footer.tpl"}
