{include file="header.tpl"}

{$choiceform|safe}

<div id="menubookletwrap">
{if $menuspecialform}
    {foreach from=$menuspecialform item=menuspecialform}
		{$menuspecialform|safe}
    {/foreach}
{/if}
</div>

<div id="bookletwrap">
    {foreach from=$indexform item=itemform}
        {$itemform|safe}
    {/foreach}
</div>

{include file="footer.tpl"}

