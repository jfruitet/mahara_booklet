{include file="header.tpl"}
<div id="bookletwrap">
<h3>{$pagetitle|safe}</h3> 
{if $author}
    <div>
	<ul><b>{$authortitle|safe}</b>
		<li>{$authorlastname|safe} {$authorfirstname|safe}</li><li>&lt;{$authormail|safe}&gt;</li><li>{$authorinstitution|safe}</li>
		<li>{$version|safe} - {$dateversion|safe}</li>
	</ul>
	</div>
	<p>{$authorurl|safe}</p>
{/if}
<p>{$copyright|safe}</p>

{if $authorform}
	{$authorform.tabname|safe}
{/if}
</div>
{include file="footer.tpl"}
