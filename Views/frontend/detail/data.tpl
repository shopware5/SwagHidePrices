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

{block name="frontend_detail_liveshopping_data"}
    {if $ShowPrices || !$liveShopping}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_detail_data_block_price_include"}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}
