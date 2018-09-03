{* needed for Shopware 5.3 and the EmotionAdvanced QuickView function
   has no effect on Shopware 5.2 *}

{extends file='parent:frontend/detail/content.tpl'}

{block name='frontend_detail_data_tax'}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_detail_data_price_default'}
    {if $ShowPrices}
        {$smarty.block.parent}
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

{block name='widgets_swag_emotion_advanced_buy_button'}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_detail_data_block_price_include"}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}
