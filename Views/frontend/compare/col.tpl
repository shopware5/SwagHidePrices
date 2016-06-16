{extends file="parent:frontend/compare/col.tpl"}

{block name='frontend_compare_price'}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}

{* Additionaly overwrite this block - but it shouldn't be necessary because of the above block which includes this block. But it works *}
{block name='frontend_compare_unitprice'}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}
