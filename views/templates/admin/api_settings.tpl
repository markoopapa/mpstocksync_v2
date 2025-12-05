<div class="panel">
    <div class="panel-heading">
        <i class="icon-cogs"></i> {ucfirst($api_name)} API Configuration
        <span class="pull-right">
            <a href="{$link->getAdminLink('AdminMpStockSyncApi')}&configure=emag" class="btn btn-default {if $api_name == 'emag'}active{/if}">
                eMAG
            </a>
            <a href="{$link->getAdminLink('AdminMpStockSyncApi')}&configure=trendyol" class="btn btn-default {if $api_name == 'trendyol'}active{/if}">
                Trendyol
            </a>
        </span>
    </div>
    
    <div class="panel-body">
        {if $test_result}
            <div class="alert alert-{if $test_result.success}success{else}danger{/if}">
                <h4>Connection Test Result</h4>
                <p>{$test_result.message}</p>
                {if !$test_result.success && $test_result.details}
                    <p><small>{$test_result.details}</small></p>
                {/if}
            </div>
        {/if}
        
        {$form}
        
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <i class="icon-info"></i> {ucfirst($api_name)} API Information
                    </div>
                    <div class="panel-body">
                        {if $api_name == 'emag'}
                            <p><strong>API Documentation:</strong> 
                                <a href="https://marketplace-api.emag.ro/api-docs/" target="_blank">
                                    https://marketplace-api.emag.ro/api-docs/
                                </a>
                            </p>
                            <p><strong>Test Environment:</strong> 
                                <a href="https://marketplace-api-sandbox.emag.ro/" target="_blank">
                                    https://marketplace-api-sandbox.emag.ro/
                                </a>
                            </p>
                            <p><strong>Required Fields:</strong></p>
                            <ul>
                                <li>Client ID (from eMAG developer account)</li>
                                <li>Client Secret (from eMAG developer account)</li>
                                <li>Username (your eMAG marketplace email)</li>
                                <li>Password (your eMAG marketplace password)</li>
                            </ul>
                        {elseif $api_name == 'trendyol'}
                            <p><strong>API Documentation:</strong> 
                                <a href="https://developers.trendyol.com/" target="_blank">
                                    https://developers.trendyol.com/
                                </a>
                            </p>
                            <p><strong>Required Fields:</strong></p>
                            <ul>
                                <li>API Key (from Trendyol supplier panel)</li>
                                <li>API Secret (from Trendyol supplier panel)</li>
                                <li>Supplier ID (your Trendyol supplier ID)</li>
                            </ul>
                            <p><strong>Note:</strong> Trendyol requires EAN13 barcodes for product identification.</p>
                        {/if}
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="panel panel-warning">
                    <div class="panel-heading">
                        <i class="icon-warning"></i> Important Notes
                    </div>
                    <div class="panel-body">
                        <ol>
                            <li>Always test in <strong>Test Mode</strong> first</li>
                            <li>Make sure you have the correct API credentials</li>
                            <li>Configure product mappings before enabling auto-sync</li>
                            <li>Monitor sync logs regularly</li>
                            <li>Contact support if you encounter issues</li>
                        </ol>
                        
                        {if $api_config.configured}
                            <div class="alert alert-success">
                                <i class="icon-check"></i> API is configured and 
                                {if $api_config.enabled}
                                    <strong>enabled</strong>
                                {else}
                                    <strong>disabled</strong>
                                {/if}
                                {if $api_config.test_mode}
                                    (Test Mode)
                                {/if}
                            </div>
                        {else}
                            <div class="alert alert-danger">
                                <i class="icon-remove"></i> API is not configured
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.btn.active {
    font-weight: bold;
    border: 2px solid #333;
}
</style>
