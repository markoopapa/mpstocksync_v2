<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_0_1($module)
{
    $sql = [];
    
    // Add error_message column to log table
    $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'mpstocksync_log` 
              ADD COLUMN `error_message` TEXT AFTER `status`';
    
    // Add test_mode column to api config
    $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'mpstocksync_api_config`
              ADD COLUMN `test_mode` TINYINT(1) DEFAULT 1 AFTER `seller_id`';
    
    foreach ($sql as $query) {
        if (!Db::getInstance()->execute($query)) {
            return false;
        }
    }
    
    return true;
}
