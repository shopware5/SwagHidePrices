{block name="frontend_index_header_css_screen" append}
    {if !$ShowPrices}
        <style type="text/css">
            .price { display:none !important }
            .pseudoprice { display:none !important }
            .amount { display:none !important }
            .article_details_price { display:none !important }
            .article_details_price2 { display:none !important }
            .smallsize { display:none !important }
            .referenceunit { display: none !important }
            .abo-price { display:none !important }
            .bundle-price { display:none !important }
        </style>
    {/if}
{/block}

{* --- DETAILSEITE START --- *}
{block name="frontend_detail_buy_button"}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}
{* --- DETAILSEITE ENDE --- *}

{* --- KATEGORIE-LISTING START --- *}
{block name='frontend_listing_box_article_actions_buy_now'}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}
{* --- KATEGORIE-LISTING ENDE --- *}

{* --- MERKZETTEL START --- *}
{block name="frontend_note_item_price"}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}
{* --- MERKZETTEL ENDE --- *}

{* --- PREISFILTER SUCHE START --- *}
{block name="frontend_search_filter_price"}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}
{* --- PREISFILTER SUCHE ENDE --- *}