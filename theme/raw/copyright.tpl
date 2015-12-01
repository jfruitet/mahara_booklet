{include file="header.tpl"}
<div id="bookletwrap">
{if $idtome}
	 <h3>{$statusmodif|safe} : <i>{$statusvalue|safe}</i></h3>
	 {if $listgroups}
	 <div><h3>{$restrictedgroups|safe}</h3> {$listgroups|safe} </div>
	 {/if}
	<div class="rbuttons">
		<a href="{$WWWROOT}artefact/booklet/author.php?id={$idtome}" title="{str tag=edit}">
			<img src="{theme_url filename='images/btn_access.png'}" alt="{str (tag=information  section=artefact.booklet)|escape:html|safe}">
		</a>
	</div>
{/if}	
{if $idtome}
	<div><h3>{$information|safe}</h3>
    {if $author}
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
		</ul>
	{/if}
	{if $copyright} 	
		{$copyright|safe}
	{/if}
	
	</div>
{/if}


</div>
{include file="footer.tpl"}
