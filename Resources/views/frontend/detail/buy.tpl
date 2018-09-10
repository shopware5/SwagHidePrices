{extends file="parent:frontend/detail/buy.tpl"}

{block name="frontend_detail_buy_quantity"}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}
