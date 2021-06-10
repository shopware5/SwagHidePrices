{extends file="parent:frontend/detail/index.tpl"}

{block name='frontend_detail_data_tax'}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_detail_data_price_default'}
    {if $ShowPrices}
        {$smarty.block.parent}
    {else}
        <meta itemprop="price" content="0">
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

{block name='panel_content_header_price'}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='bundle_article_price_supplier'}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='bundle_article_reference_price_unit_reference_content'}
    {if $ShowPrices}
        {$smarty.block.parent}
    {else}
        <span class="bundle--product-content-description is--bold">&nbsp;</span> <span class="bundle--purchaseUnit-{$product.bundleArticleId}"></span> <span class="bundle--purchaseDescription-{$product.bundleArticleId}">&nbsp;</span><span class="bundle--reference-price-{$product.bundleArticleId}">&nbsp;</span>
    {/if}
{/block}
