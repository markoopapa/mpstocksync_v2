<div class="mpstocksync-dashboard">
    <div class="row">
        <!-- eMAG Status Card -->
        <div class="col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="icon-shop"></i> eMAG Status
                    <span class="pull-right">
                        {if $emag_status.configured}
                            <span class="label label-success">Configured</span>
                        {else}
                            <span class="label label-warning">Not Configured</span>
                        {/if}
                        {if $emag_status.test_mode}
                            <span class="label label-info">Test Mode</span>
                        {/if}
                    </span>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Sync Statistics</h4>
                            {if isset($stats.emag)}
                                <p>Total: <strong>{$stats.emag.total}</strong></p>
                                <p>Success: <strong>{$stats.emag.success}</strong></p>
                                <p>Failed: <strong>{$stats.emag.failed}</strong></p>
                                <p>Success Rate: <strong>{$stats.emag.success_rate}%</strong></p>
                            {else}
                                <p>No syncs yet</p>
                            {/if}
                        </div>
                        <div class="col-md-6">
                            <h4>Queue Status</h4>
                            {if isset($queue_stats.emag)}
                                <p>Pending: <strong>{$queue_stats.emag.pending}</strong></p>
                                <p>Processing: <strong>{$queue_stats.emag.processing}</strong></p>
                                <p>Failed: <strong>{$queue_stats.emag.failed}</strong></p>
                            {else}
                                <p>Queue empty</p>
                            {/if}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <hr>
                            <a href="{$api_url}&configure=emag" class="btn btn-default">
                                <i class="icon-cogs"></i> Configure eMAG
                            </a>
                            <a href="{$dashboard_url}&sync_emag&token={$token}" class="btn btn-primary">
                                <i class="icon-refresh"></i> Sync Now
                            </a>
                            {if $emag_status.last_sync}
                                <p class="text-muted small">
                                    Last sync: {$emag_status.last_sync}
                                </p>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Trendyol Status Card -->
        <div class="col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="icon-truck"></i> Trendyol Status
                    <span class="pull-right">
                        {if $trendyol_status.configured}
                            <span class="label label-success">Configured</span>
                        {else}
                            <span class="label label-warning">Not Configured</span>
                        {/if}
                        {if $trendyol_status.test_mode}
                            <span class="label label-info">Test Mode</span>
                        {/if}
                    </span>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Sync Statistics</h4>
                            {if isset($stats.trendyol)}
                                <p>Total: <strong>{$stats.trendyol.total}</strong></p>
                                <p>Success: <strong>{$stats.trendyol.success}</strong></p>
                                <p>Failed: <strong>{$stats.trendyol.failed}</strong></p>
                                <p>Success Rate: <strong>{$stats.trendyol.success_rate}%</strong></p>
                            {else}
                                <p>No syncs yet</p>
                            {/if}
                        </div>
                        <div class="col-md-6">
                            <h4>Queue Status</h4>
                            {if isset($queue_stats.trendyol)}
                                <p>Pending: <strong>{$queue_stats.trendyol.pending}</strong></p>
                                <p>Processing: <strong>{$queue_stats.trendyol.processing}</strong></p>
                                <p>Failed: <strong>{$queue_stats.trendyol.failed}</strong></p>
                            {else}
                                <p>Queue empty</p>
                            {/if}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <hr>
                            <a href="{$api_url}&configure=trendyol" class="btn btn-default">
                                <i class="icon-cogs"></i> Configure Trendyol
                            </a>
                            <a href="{$dashboard_url}&sync_trendyol&token={$token}" class="btn btn-primary">
                                <i class="icon-refresh"></i> Sync Now
                            </a>
                            {if $trendyol_status.last_sync}
                                <p class="text-muted small">
                                    Last sync: {$trendyol_status.last_sync}
                                </p>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="icon-rocket"></i> Quick Actions
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <a href="{$products_url}" class="btn btn-lg btn-block btn-default">
                                <i class="icon-link"></i><br>
                                Product Mapping
                            </a>
                        </div>
                        <div class="col-md-3 text-center">
                            <a href="{$logs_url}" class="btn btn-lg btn-block btn-default">
                                <i class="icon-list"></i><br>
                                Sync Logs
                            </a>
                        </div>
                        <div class="col-md-3 text-center">
                            <a href="{$dashboard_url}&sync_all&token={$token}" class="btn btn-lg btn-block btn-primary">
                                <i class="icon-cogs"></i><br>
                                Sync All Platforms
                            </a>
                        </div>
                        <div class="col-md-3 text-center">
                            <a href="{$settings_url}" class="btn btn-lg btn-block btn-default">
                                <i class="icon-settings"></i><br>
                                Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Syncs -->
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="icon-time"></i> Recent Syncs
                </div>
                <div class="panel-body">
                    {if $recent_syncs}
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>API</th>
                                    <th>Product</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach from=$recent_syncs item=sync}
                                    <tr>
                                        <td>{$sync.date_add}</td>
                                        <td>
                                            {if $sync.api_name == 'emag'}
                                                <span class="label" style="background:#0056b3">eMAG</span>
                                            {elseif $sync.api_name == 'trendyol'}
                                                <span class="label" style="background:#ff6b00">Trendyol</span>
                                            {else}
                                                <span class="label label-default">{$sync.api_name}</span>
                                            {/if}
                                        </td>
                                        <td>
                                            {if $sync.name}
                                                {$sync.name} ({$sync.reference})
                                            {else}
                                                ID: {$sync.id_product}
                                            {/if}
                                        </td>
                                        <td>{$sync.action}</td>
                                        <td>
                                            {if $sync.status}
                                                <span class="label label-success">Success</span>
                                            {else}
                                                <span class="label label-danger">Failed</span>
                                            {/if}
                                        </td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    {else}
                        <p class="text-center">No syncs recorded yet</p>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.mpstocksync-dashboard .panel {
    margin-bottom: 20px;
}
.mpstocksync-dashboard .btn-lg {
    padding: 20px;
    margin-bottom: 10px;
}
.mpstocksync-dashboard .btn-lg i {
    font-size: 24px;
    margin-bottom: 10px;
    display: block;
}
</style>
