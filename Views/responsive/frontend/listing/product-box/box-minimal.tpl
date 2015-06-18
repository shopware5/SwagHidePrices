{extends file="parent:frontend/listing/product-box/box-minimal.tpl"}

{block name="frontend_listing_box_article_price"}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}
