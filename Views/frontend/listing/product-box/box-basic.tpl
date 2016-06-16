{extends file="parent:frontend/listing/product-box/box-basic.tpl"}

{block name='frontend_listing_box_article_price'}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_listing_box_article_unit_reference_content'}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}
