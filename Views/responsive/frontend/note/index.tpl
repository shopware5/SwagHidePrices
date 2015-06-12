{extends file="parent:frontend/note/index.tpl"}

{block name='frontend_note_item_price'}
	{if $ShowPrices}
		{$smarty.block.parent}
	{/if}
{/block}

{block name='frontend_note_item_unitprice'}
	{if $ShowPrices}
		{$smarty.block.parent}
	{/if}
{/block}