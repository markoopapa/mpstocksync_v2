<form action="{$post_url|escape:'html':'UTF-8'}" method="post" class="form-horizontal">
    <input type="hidden" name="token" value="{$token|escape:'html':'UTF-8'}">
    
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-cogs"></i> {l s='API Settings' mod='mpstocksync'}
        </div>
        
        <!-- eMAG Settings -->
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-cog"></i> {l s='eMAG API Settings' mod='mpstocksync'}
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='API URL' mod='mpstocksync'}</label>
                    <div class="col-lg-9">
                        <input type="text" name="emag_api_url" value="{$config.emag.api_url|escape:'html':'UTF-8'}" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Client ID' mod='mpstocksync'}</label>
                    <div class="col-lg-9">
                        <input type="text" name="emag_client_id" value="{$config.emag.client_id|escape:'html':'UTF-8'}" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Client Secret' mod='mpstocksync'}</label>
                    <div class="col-lg-9">
                        <input type="password" name="emag_client_secret" value="{$config.emag.client_secret|escape:'html':'UTF-8'}" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Username' mod='mpstocksync'}</label>
                    <div class="col-lg-9">
                        <input type="text" name="emag_username" value="{$config.emag.username|escape:'html':'UTF-8'}" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Password' mod='mpstocksync'}</label>
                    <div class="col-lg-9">
                        <input type="password" name="emag_password" value="{$config.emag.password|escape:'html':'UTF-8'}" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Auto Sync' mod='mpstocksync'}</label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="emag_auto_sync" id="emag_auto_sync_on" value="1" {if $config.emag.auto_sync}checked="checked"{/if}>
                            <label for="emag_auto_sync_on">{l s='Yes' mod='mpstocksync'}</label>
                            <input type="radio" name="emag_auto_sync" id="emag_auto_sync_off" value="0" {if !$config.emag.auto_sync}checked="checked"{/if}>
                            <label for="emag_auto_sync_off">{l s='No' mod='mpstocksync'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Trendyol Settings -->
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-cog"></i> {l s='Trendyol API Settings' mod='mpstocksync'}
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='API URL' mod='mpstocksync'}</label>
                    <div class="col-lg-9">
                        <input type="text" name="trendyol_api_url" value="{$config.trendyol.api_url|escape:'html':'UTF-8'}" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='API Key' mod='mpstocksync'}</label>
                    <div class="col-lg-9">
                        <input type="text" name="trendyol_api_key" value="{$config.trendyol.api_key|escape:'html':'UTF-8'}" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='API Secret' mod='mpstocksync'}</label>
                    <div class="col-lg-9">
                        <input type="password" name="trendyol_api_secret" value="{$config.trendyol.api_secret|escape:'html':'UTF-8'}" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Supplier ID' mod='mpstocksync'}</label>
                    <div class="col-lg-9">
                        <input type="text" name="trendyol_supplier_id" value="{$config.trendyol.supplier_id|escape:'html':'UTF-8'}" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Auto Sync' mod='mpstocksync'}</label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="trendyol_auto_sync" id="trendyol_auto_sync_on" value="1" {if $config.trendyol.auto_sync}checked="checked"{/if}>
                            <label for="trendyol_auto_sync_on">{l s='Yes' mod='mpstocksync'}</label>
                            <input type="radio" name="trendyol_auto_sync" id="trendyol_auto_sync_off" value="0" {if !$config.trendyol.auto_sync}checked="checked"{/if}>
                            <label for="trendyol_auto_sync_off">{l s='No' mod='mpstocksync'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- General Settings -->
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-wrench"></i> {l s='General Settings' mod='mpstocksync'}
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Enable Logging' mod='mpstocksync'}</label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="log_enabled" id="log_enabled_on" value="1" {if $config.general.log_enabled}checked="checked"{/if}>
                            <label for="log_enabled_on">{l s='Yes' mod='mpstocksync'}</label>
                            <input type="radio" name="log_enabled" id="log_enabled_off" value="0" {if !$config.general.log_enabled}checked="checked"{/if}>
                            <label for="log_enabled_off">{l s='No' mod='mpstocksync'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Notify on Errors' mod='mpstocksync'}</label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="notify_errors" id="notify_errors_on" value="1" {if $config.general.notify_errors}checked="checked"{/if}>
                            <label for="notify_errors_on">{l s='Yes' mod='mpstocksync'}</label>
                            <input type="radio" name="notify_errors" id="notify_errors_off" value="0" {if !$config.general.notify_errors}checked="checked"{/if}>
                            <label for="notify_errors_off">{l s='No' mod='mpstocksync'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Auto Retry Failed Syncs' mod='mpstocksync'}</label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="auto_retry" id="auto_retry_on" value="1" {if $config.general.auto_retry}checked="checked"{/if}>
                            <label for="auto_retry_on">{l s='Yes' mod='mpstocksync'}</label>
                            <input type="radio" name="auto_retry" id="auto_retry_off" value="0" {if !$config.general.auto_retry}checked="checked"{/if}>
                            <label for="auto_retry_off">{l s='No' mod='mpstocksync'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Retry Attempts' mod='mpstocksync'}</label>
                    <div class="col-lg-9">
                        <input type="number" name="retry_attempts" value="{$config.general.retry_attempts|intval}" class="form-control" min="0" max="10">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Retry Delay (seconds)' mod='mpstocksync'}</label>
                    <div class="col-lg-9">
                        <input type="number" name="retry_delay" value="{$config.general.retry_delay|intval}" class="form-control" min="0" max="3600">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Save Button -->
        <div class="panel-footer">
            <button type="submit" name="submit_api_settings" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Save Settings' mod='mpstocksync'}
            </button>
        </div>
    </div>
</form>
