{block name='frontend_hide_prices_liveshopping_detail_counter'}
    {* This is required to fix the bottom border of the liveshopping counter div. *}
    <div class="counter full-border is--align-center">
        <div class="counter--time {if $liveShopping.limited === 1}is--stock{/if}">
            {* Liveshopping counter headline *}
            {block name="frontend_liveshopping_detail_counter_headline"}
                <div class="counter--headline">
                    {s name="sLiveHeadline" namespace="frontend/live_shopping/main"}{/s}
                </div>
            {/block}

            {* Liveshopping counter *}
            {block name='frontend_liveshopping_detail_counter_include'}
                {include file='frontend/swag_live_shopping/_includes/liveshopping-counter.tpl'}
            {/block}
        </div>

        {* Liveshopping stock *}
        {block name='frontend_liveshopping_detail_stock'}
            {if $liveShopping.limited === 1}
                {include file='frontend/swag_live_shopping/_includes/liveshopping-stock.tpl'}
            {/if}
        {/block}
    </div>
{/block}
