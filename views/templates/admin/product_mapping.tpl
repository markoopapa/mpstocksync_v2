<div class="panel">
    <div class="panel-heading">
        <i class="icon-link"></i> {l s='Product Mapping' mod='mpstocksync_v2'}
    </div>
    
    {if isset($warning_message) && !$has_api_config}
        <div class="alert alert-warning">
            <i class="icon-warning"></i> {$warning_message}
            <a href="{$link->getAdminLink('AdminMpStockSyncApi')}" class="btn btn-default">
                <i class="icon-cogs"></i> {l s='Go to API Settings' mod='mpstocksync_v2'}
            </a>
        </div>
    {elseif isset($error_message)}
        <div class="alert alert-danger">
            <i class="icon-remove"></i> {$error_message}
        </div>
    {elseif isset($no_data_message)}
        <div class="alert alert-info">
            <i class="icon-info"></i> {$no_data_message}
        </div>
    {/if}
    
    <div class="panel-body">
        {$content}
    </div>
</div>
