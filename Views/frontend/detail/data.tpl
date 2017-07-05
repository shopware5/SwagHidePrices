{extends file="parent:frontend/detail/data.tpl"}

{block name="frontend_detail_data_price"}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_detail_data_tax"}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_detail_data_price_default"}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_liveshopping_detail_pricing_include"}
    {if $ShowPrices || !$liveShopping}
        {$smarty.block.parent}
    {/if}
{/block}

{* Liveshopping counter *}
{block name="frontend_liveshopping_detail_counter"}
    {if !$ShowPrices && $liveShopping}
        {include file="frontend/swag_hide_prices/swag_live_shopping/detail/liveshopping-detail.tpl"}
    {elseif $ShowPrices && $liveShopping}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_detail_data_block_price_include"}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}
