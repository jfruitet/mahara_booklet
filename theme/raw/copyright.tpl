{include file="header.tpl"}
<div id="bookletwrap">
{if $author}
    <ul><b>{$authortitle|safe}</b>
		<li>{$authorlastname|safe} {$authorfirstname|safe}</li><li>&lt;{$authormail|safe}&gt;</li><li>{$authorinstitution|safe}</li>
		<li>{$version|safe} - {$dateversion|safe}</li>
	</ul>
	<p>{$authorurl|safe}</p>
{/if}
<p>{$copyright|safe}</p>

</div>
{include file="footer.tpl"}
