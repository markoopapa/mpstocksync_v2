{* views/templates/admin/supplier_config.tpl *}
<div class="panel">
    <div class="panel-heading">
        <i class="icon-truck"></i> Supplier Configuration
    </div>
    
    <div class="panel-body">
        {$supplier_form}
        
        {if $suppliers}
            <div class="row">
                <div class="col-md-12">
                    <h3>Configured Suppliers</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Target Shops</th>
                                <th>Last Sync</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$suppliers item=supplier}
                                <tr>
                                    <td>{$supplier.name}</td>
                                    <td>
                                        {if $supplier.connection_type == 'database'}
                                            <span class="label label-info">Database</span>
                                        {else}
                                            <span class="label label-primary">API</span>
                                        {/if}
                                    </td>
                                    <td>
                                        {assign var=shops value=json_decode($supplier.target_shops, true)}
                                        {if $shops}
                                            {foreach from=$shops item=shopId}
                                                {assign var=shop value=Shop::getShop($shopId)}
                                                <span class="label label-default">{$shop.name}</span>
                                            {/foreach}
                                        {else}
                                            <span class="text-muted">No shops</span>
                                        {/if}
                                    </td>
                                    <td>
                                        {if $supplier.last_sync}
                                            {$supplier.last_sync}
                                        {else}
                                            <span class="text-muted">Never</span>
                                        {/if}
                                    </td>
                                    <td>
                                        {if $supplier.active}
                                            <span class="label label-success">Active</span>
                                        {else}
                                            <span class="label label-danger">Inactive</span>
                                        {/if}
                                        {if $supplier.auto_sync}
                                            <span class="label label-info">Auto</span>
                                        {/if}
                                    </td>
                                    <td>
                                        <a href="{$link->getAdminLink('AdminMpStockSyncSupplierMapping')}&id_supplier={$supplier.id_supplier}" 
                                           class="btn btn-default btn-sm">
                                            <i class="icon-link"></i> Mapping
                                        </a>
                                        <a href="{$currentPage}&sync_supplier={$supplier.id_supplier}&token={$token}" 
                                           class="btn btn-primary btn-sm">
                                            <i class="icon-refresh"></i> Sync Now
                                        </a>
                                        <a href="{$currentPage}&edit_supplier={$supplier.id_supplier}" 
                                           class="btn btn-default btn-sm">
                                            <i class="icon-edit"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        {/if}
    </div>
</div>

<script>
$(document).ready(function() {
    // Show/hide fields based on connection type
    function toggleConnectionFields() {
        var type = $('#connection_type').val();
        if (type == 'database') {
            $('.database-fields').show();
            $('.api-fields').hide();
        } else {
            $('.database-fields').hide();
            $('.api-fields').show();
        }
    }
    
    $('#connection_type').on('change', toggleConnectionFields);
    toggleConnectionFields();
    
    // Auto-match products
    $('#auto-match-btn').on('click', function() {
        if (confirm('This will try to auto-match all supplier products. Continue?')) {
            $.ajax({
                url: '{$link->getAdminLink('AdminMpStockSyncSupplierMapping')}',
                data: {
                    action: 'auto_match',
                    ajax: true,
                    token: '{$token}'
                },
                success: function(response) {
                    alert('Auto-match completed');
                    location.reload();
                }
            });
        }
    });
});
</script>

<style>
.database-fields, .api-fields {
    display: none;
}
</style>
