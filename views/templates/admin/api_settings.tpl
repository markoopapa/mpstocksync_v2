<div class="panel">
    <div class="panel-heading">
        <i class="icon-cogs"></i> {ucfirst($api_name)} API Configuration
        <div class="pull-right">
            <a href="{$current_url}&configure=emag" class="btn btn-default{if $api_name == 'emag'} active{/if}">
                <i class="icon-shop"></i> eMAG
            </a>
            <a href="{$current_url}&configure=trendyol" class="btn btn-default{if $api_name == 'trendyol'} active{/if}">
                <i class="icon-truck"></i> Trendyol
            </a>
        </div>
    </div>
    
    <div class="panel-body">
        {if $test_result}
            <div class="alert alert-{if $test_result.success}success{else}danger{/if}">
                <h4><i class="icon-{if $test_result.success}check{else}remove{/if}"></i> Connection Test Result</h4>
                <p>{$test_result.message}</p>
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
                            <p><strong>API Documentation:</strong></p>
                            <p>
                                <a href="https://marketplace-api.emag.ro/api-docs/" target="_blank" class="btn btn-default btn-sm">
                                    <i class="icon-external-link"></i> Open eMAG API Docs
                                </a>
                            </p>
                            
                            <p><strong>Test Environment:</strong></p>
                            <p>
                                <a href="https://marketplace-api-sandbox.emag.ro/" target="_blank" class="btn btn-default btn-sm">
                                    <i class="icon-external-link"></i> Open Sandbox
                                </a>
                            </p>
                            
                            <p><strong>Required Credentials:</strong></p>
                            <ul>
                                <li>Client ID (from eMAG developer account)</li>
                                <li>Client Secret (from eMAG developer account)</li>
                                <li>Username (your eMAG marketplace email)</li>
                                <li>Password (your eMAG marketplace password)</li>
                            </ul>
                            
                            <p><strong>Note:</strong> eMAG requires SKU as product identifier.</p>
                            
                        {elseif $api_name == 'trendyol'}
                            <p><strong>API Documentation:</strong></p>
                            <p>
                                <a href="https://developers.trendyol.com/" target="_blank" class="btn btn-default btn-sm">
                                    <i class="icon-external-link"></i> Open Trendyol API Docs
                                </a>
                            </p>
                            
                            <p><strong>Required Credentials:</strong></p>
                            <ul>
                                <li>API Key (from Trendyol supplier panel)</li>
                                <li>API Secret (from Trendyol supplier panel)</li>
                                <li>Supplier ID (your Trendyol supplier ID)</li>
                            </ul>
                            
                            <p><strong>Important Notes:</strong></p>
                            <ul>
                                <li>Trendyol requires EAN13 barcodes for product identification</li>
                                <li>Make sure your products have valid EAN13 codes</li>
                                <li>Prices should be in Turkish Lira (TRY)</li>
                            </ul>
                        {/if}
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="panel panel-warning">
                    <div class="panel-heading">
                        <i class="icon-warning"></i> Configuration Status
                    </div>
                    <div class="panel-body">
                        <h4>Current Status:</h4>
                        
                        {if $api_config.configured}
                            <div class="alert alert-success">
                                <i class="icon-check"></i> 
                                <strong>{ucfirst($api_name)} API is configured</strong><br>
                                Status: {if $api_config.status}<span class="label label-success">Enabled</span>{else}<span class="label label-danger">Disabled</span>{/if}<br>
                                Mode: {if $api_config.test_mode}<span class="label label-info">Test Mode</span>{else}<span class="label label-warning">Live Mode</span>{/if}<br>
                                Auto Sync: {if $api_config.auto_sync}<span class="label label-success">On</span>{else}<span class="label label-default">Off</span>{/if}
                            </div>
                        {else}
                            <div class="alert alert-danger">
                                <i class="icon-remove"></i> 
                                <strong>{ucfirst($api_name)} API is not configured</strong><br>
                                Please fill in the API credentials above.
                            </div>
                        {/if}
                        
                        <hr>
                        
                        <h4>Next Steps:</h4>
                        <ol>
                            <li>Enter your API credentials</li>
                            <li>Click "Test Connection" to verify</li>
                            <li>Enable the API if test is successful</li>
                            <li>Configure product mappings</li>
                            <li>Enable auto-sync if desired</li>
                        </ol>
                        
                        <div class="alert alert-info">
                            <i class="icon-lightbulb"></i> 
                            <strong>Tip:</strong> Always test in <strong>Test Mode</strong> first before going live.
                        </div>
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
.btn-default.active {
    background-color: #337ab7;
    color: white;
    border-color: #2e6da4;
}
</style>

<script>
$(document).ready(function() {
    // Form submission handling
    $('form').on('submit', function(e) {
        var form = $(this);
        var submitName = $('[name^="save_"]:focus, [name^="test_"]:focus').attr('name');
        
        if (submitName) {
            // Remove any existing hidden input
            $('input[name="' + submitName + '"]').remove();
            
            // Add hidden input with the button name
            form.append('<input type="hidden" name="' + submitName + '" value="1">');
        }
    });
    
    // Active button styling
    $('.btn-default').on('click', function() {
        $('.btn-default').removeClass('active');
        $(this).addClass('active');
    });
});
</script>
