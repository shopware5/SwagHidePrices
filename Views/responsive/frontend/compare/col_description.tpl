{extends file="parent:frontend/compare/col_description.tpl"}

{block name="frontend_compare_price"}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}
