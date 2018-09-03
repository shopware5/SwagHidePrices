{extends file="parent:frontend/listing/product-box/box-emotion.tpl"}

{block name="frontend_listing_box_article_price_info"}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}
