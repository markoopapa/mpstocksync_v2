<div class="panel">
    <div class="panel-heading">
        <i class="icon-dashboard"></i> Dashboard
    </div>
    <div class="panel-body">
        <h3>MP Stock Sync Pro v2.0.0</h3>
        <p>Professional stock synchronization module</p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-info">
                    <div class="panel-heading">Marketplace Status</div>
                    <div class="panel-body">
                        <h4>eMAG</h4>
                        <p>
                            {if $emag_configured}
                                <span class="label label-success">Configured</span>
                            {else}
                                <span class="label label-warning">Not Configured</span>
                            {/if}
                            {if Configuration::get('MP_EMAG_AUTO_SYNC')}
                                <span class="label label-info">Auto Sync</span>
                            {/if}
                        </p>
                        
                        <h4>Trendyol</h4>
                        <p>
                            {if $trendyol_configured}
                                <span class="label label-success">Configured</span>
                            {else}
                                <span class="label label-warning">Not Configured</span>
                            {/if}
                            {if Configuration::get('MP_TRENDYOL_AUTO_SYNC')}
                                <span class="label label-info">Auto Sync</span>
                            {/if}
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="panel panel-success">
                    <div class="panel-heading">Quick Actions</div>
                    <div class="panel-body text-center">
                        <a href="{$link->getAdminLink('AdminMpStockSyncProducts')}" class="btn btn-lg btn-default btn-block">
                            <i class="icon-link"></i> Product Mapping
                        </a>
                        <a href="{$link->getAdminLink('AdminMpStockSyncSuppliers')}" class="btn btn-lg btn-default btn-block">
                            <i class="icon-truck"></i> Suppliers
                        </a>
                        <a href="{$link->getAdminLink('AdminMpStockSyncApi')}" class="btn btn-lg btn-default btn-block">
                            <i class="icon-cogs"></i> API Settings
                        </a>
                        <a href="{$dashboard_url}&sync_emag&token={$token}" class="btn btn-lg btn-primary btn-block">
                            <i class="icon-refresh"></i> Sync eMAG Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        {if isset($stats) && $stats}
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Sync Statistics</div>
                    <div class="panel-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Platform</th>
                                    <th>Total Syncs</th>
                                    <th>Successful</th>
                                    <th>Failed</th>
                                    <th>Success Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach from=$stats key=platform item=data}
                                    {if $platform != 'suppliers'}
                                    <tr>
                                        <td>
                                            {if $platform == 'emag'}
                                                <span class="label" style="background:#0056b3">eMAG</span>
                                            {elseif $platform == 'trendyol'}
                                                <span class="label" style="background:#ff6b00">Trendyol</span>
                                            {else}
                                                {$platform}
                                            {/if}
                                        </td>
                                        <td>{$data.total}</td>
                                        <td><span class="text-success">{$data.success}</span></td>
                                        <td><span class="text-danger">{$data.failed}</span></td>
                                        <td>
                                            {if $data.total > 0}
                                                {math equation="round((x/y)*100)" x=$data.success y=$data.total format="%d"}%
                                            {else}
                                                0%
                                            {/if}
                                        </td>
                                    </tr>
                                    {/if}
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        {/if}
    </div>
</div>
