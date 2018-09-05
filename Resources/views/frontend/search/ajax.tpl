{extends file="parent:frontend/search/ajax.tpl"}

{block name="search_ajax_list_entry_price"}
    {if $ShowPrices}
        {$smarty.block.parent}
    {/if}
{/block}
