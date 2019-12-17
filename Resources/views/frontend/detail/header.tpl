{extends file='parent:frontend/detail/header.tpl'}

{* Meta opengraph tags *}
{block name='frontend_index_header_meta_tags_opengraph'}
    {if $ShowPrices}
        {$smarty.block.parent}
    {else}
        <meta property="og:type" content="product"/>
        <meta property="og:site_name" content="{{config name=sShopname}|escapeHtml}"/>
        <meta property="og:url"
              content="{url sArticle=$sArticle.articleID title=$sArticle.articleName controller=detail}"/>
        <meta property="og:title" content="{$sArticle.articleName|escapeHtml}"/>
        <meta property="og:description"
              content="{$sArticle.description_long|strip_tags|trim|truncate:$SeoDescriptionMaxLength:'…'|escapeHtml}"/>
        <meta property="og:image" content="{$sArticle.image.source}"/>
        <meta property="product:brand" content="{$sArticle.supplierName|escapeHtml}"/>
        <meta property="product:product_link"
              content="{url sArticle=$sArticle.articleID title=$sArticle.articleName controller=detail}"/>
        <meta name="twitter:card" content="product"/>
        <meta name="twitter:site" content="{{config name=sShopname}|escapeHtml}"/>
        <meta name="twitter:title" content="{$sArticle.articleName|escapeHtml}"/>
        <meta name="twitter:description"
              content="{$sArticle.description_long|strip_tags|trim|truncate:$SeoDescriptionMaxLength:'…'|escapeHtml}"/>
        <meta name="twitter:image" content="{$sArticle.image.source}"/>
    {/if}
{/block}
