<div class="panel">
    <div class="panel-heading">
        <i class="icon-cogs"></i> {l s='General Settings' mod='mpstocksync'}
    </div>
    
    <div class="panel-body">
        {if isset($settings_form)}
            {$settings_form nofilter}
        {else}
            <div class="alert alert-warning">
                <i class="icon-warning"></i> {l s='Settings form is not available' mod='mpstocksync'}
            </div>
        {/if}
        
        <hr>
        
        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="icon-trash"></i> {l s='Maintenance' mod='mpstocksync'}
                    </div>
                    <div class="panel-body">
                        <form action="{$currentIndex|escape:'html':'UTF-8'}&token={$token|escape:'html':'UTF-8'}" method="post">
                            <div class="form-group">
                                <label>{l s='Clear all logs' mod='mpstocksync'}</label>
                                <p class="help-block">{l s='Delete all sync logs from database' mod='mpstocksync'}</p>
                                <button type="submit" name="clear_all_logs" class="btn btn-danger" 
                                        onclick="return confirm('{l s='Are you sure?' mod='mpstocksync'}');">
                                    <i class="icon-trash"></i> {l s='Clear Logs' mod='mpstocksync'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="icon-info"></i> {l s='System Info' mod='mpstocksync'}
                    </div>
                    <div class="panel-body">
                        <dl class="dl-horizontal">
                            <dt>{l s='Module Version:' mod='mpstocksync'}</dt>
                            <dd>2.0.0</dd>
                            
                            <dt>{l s='PrestaShop:' mod='mpstocksync'}</dt>
                            <dd>{$ps_version|escape:'html':'UTF-8'}</dd>
                            
                            <dt>{l s='PHP Version:' mod='mpstocksync'}</dt>
                            <dd>{$php_version|escape:'html':'UTF-8'}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dl-horizontal dt {
    width: 150px;
    text-align: left;
}
.dl-horizontal dd {
    margin-left: 170px;
}
</style>
