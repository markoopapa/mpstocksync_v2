$(document).ready(function() {
    // Dashboard actions
    $('#sync-emag-btn').on('click', function(e) {
        e.preventDefault();
        if (confirm('Sync all eMAG products?')) {
            startSync('emag');
        }
    });
    
    $('#sync-trendyol-btn').on('click', function(e) {
        e.preventDefault();
        if (confirm('Sync all Trendyol products?')) {
            startSync('trendyol');
        }
    });
    
    $('#sync-all-btn').on('click', function(e) {
        e.preventDefault();
        if (confirm('Sync all products on both platforms?')) {
            startSync('all');
        }
    });
    
    // Manual sync for single product
    $('.manual-sync-btn').on('click', function() {
        var productId = $(this).data('product-id');
        var api = $(this).data('api');
        
        if (confirm('Sync this product to ' + api + '?')) {
            syncSingleProduct(productId, api);
        }
    });
    
    // Test connection
    $('.test-connection-btn').on('click', function() {
        var api = $(this).data('api');
        testConnection(api);
    });
    
    // Clear logs
    $('#clear-logs-btn').on('click', function() {
        if (confirm('Clear all sync logs? This cannot be undone.')) {
            clearLogs();
        }
    });
    
    // Helper functions
    function startSync(api) {
        var btn = api == 'emag' ? $('#sync-emag-btn') : 
                  api == 'trendyol' ? $('#sync-trendyol-btn') : $('#sync-all-btn');
        
        var originalText = btn.html();
        btn.html('<i class="icon-spinner icon-spin"></i> Syncing...').prop('disabled', true);
        
        $.ajax({
            url: mpstocksync_ajax_url,
            type: 'POST',
            data: {
                action: 'manual_sync',
                api: api,
                token: mpstocksync_token,
                ajax: true
            },
            success: function(response) {
                var data = JSON.parse(response);
                showMessage('success', 'Sync completed: ' + data.success + ' successful, ' + data.errors + ' errors');
                btn.html(originalText).prop('disabled', false);
                setTimeout(function() {
                    location.reload();
                }, 2000);
            },
            error: function() {
                showMessage('error', 'Sync failed');
                btn.html(originalText).prop('disabled', false);
            }
        });
    }
    
    function syncSingleProduct(productId, api) {
        $.ajax({
            url: mpstocksync_ajax_url,
            type: 'POST',
            data: {
                action: 'sync_single',
                product_id: productId,
                api: api,
                token: mpstocksync_token,
                ajax: true
            },
            success: function(response) {
                var data = JSON.parse(response);
                if (data.success) {
                    showMessage('success', 'Product synced successfully');
                } else {
                    showMessage('error', 'Sync failed: ' + data.message);
                }
            },
            error: function() {
                showMessage('error', 'Request failed');
            }
        });
    }
    
    function testConnection(api) {
        var btn = $('.test-connection-btn[data-api="' + api + '"]');
        var originalText = btn.html();
        btn.html('<i class="icon-spinner icon-spin"></i> Testing...').prop('disabled', true);
        
        $.ajax({
            url: mpstocksync_ajax_url,
            type: 'POST',
            data: {
                action: 'test_connection',
                api: api,
                token: mpstocksync_token,
                ajax: true
            },
            success: function(response) {
                var data = JSON.parse(response);
                if (data.success) {
                    showMessage('success', api.toUpperCase() + ' connection successful');
                } else {
                    showMessage('error', api.toUpperCase() + ' connection failed: ' + data.message);
                }
                btn.html(originalText).prop('disabled', false);
            },
            error: function() {
                showMessage('error', 'Connection test failed');
                btn.html(originalText).prop('disabled', false);
            }
        });
    }
    
    function clearLogs() {
        $.ajax({
            url: mpstocksync_ajax_url,
            type: 'POST',
            data: {
                action: 'clear_logs',
                token: mpstocksync_token,
                ajax: true
            },
            success: function(response) {
                showMessage('success', 'Logs cleared successfully');
                setTimeout(function() {
                    location.reload();
                }, 1000);
            },
            error: function() {
                showMessage('error', 'Failed to clear logs');
            }
        });
    }
    
    function showMessage(type, text) {
        var alertClass = type == 'success' ? 'alert-success' : 'alert-danger';
        var html = '<div class="alert ' + alertClass + ' alert-dismissible" style="position: fixed; top: 20px; right: 20px; z-index: 10000;">' +
                   '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                   text + '</div>';
        
        $('.page-head').after(html);
        
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
});
