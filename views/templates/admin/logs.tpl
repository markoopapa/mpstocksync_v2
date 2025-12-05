{* /modules/mpstocksync/views/templates/admin/logs/logs.tpl *}
<div class="panel">
    <div class="panel-heading">
        <i class="icon-list"></i> {l s='Sync Logs' mod='mpstocksync'}
        <span class="badge">{$logs_count|intval}</span>
    </div>
    
    {if isset($stats) && $stats.total > 0}
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>{$stats.total|intval}</h3>
                        <p>{l s='Total Logs' mod='mpstocksync'}</p>
                    </div>
                    <div class="icon">
                        <i class="icon-list"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{$stats.success|intval}</h3>
                        <p>{l s='Successful' mod='mpstocksync'}</p>
                    </div>
                    <div class="icon">
                        <i class="icon-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>{$stats.failed|intval}</h3>
                        <p>{l s='Failed' mod='mpstocksync'}</p>
                    </div>
                    <div class="icon">
                        <i class="icon-remove"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {/if}
    
    <div class="panel-body">
        {$content}
    </div>
</div>
