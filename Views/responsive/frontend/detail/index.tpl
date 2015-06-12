{extends file="parent:frontend/detail/index.tpl"}

{block name='frontend_detail_data_tax'}
	{if $ShowPrices}
		{$smarty.block.parent}
	{/if}
{/block}

{block name='frontend_detail_data_price_default'}
	{if $ShowPrices}
		{$smarty.block.parent}
	{/if}
{/block}

{block name='frontend_detail_data_pseudo_price_discount_icon'}
	{if $ShowPrices}
		{$smarty.block.parent}
	{/if}
{/block}

{block name='frontend_detail_data_pseudo_price'}
	{if $ShowPrices}
		{$smarty.block.parent}
	{/if}
{/block}

{block name='frontend_detail_buy_button'}
	{if $ShowPrices}
		{$smarty.block.parent}
	{/if}
{/block}
