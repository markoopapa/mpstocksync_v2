<?php
class AdminMpStockSyncLogsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        
        $this->bootstrap = true;
        $this->table = 'mpstocksync_log';
        $this->className = 'MpStockSyncLog';
        $this->identifier = 'id_log';
        $this->lang = false;
        
        // Lista oszlopok
        $this->fields_list = [
            'id_log' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ],
            'api_name' => [
                'title' => $this->l('API'),
                'align' => 'center',
                'type' => 'select',
                'list' => ['emag', 'trendyol', 'internal'],
                'filter_key' => 'a!api_name'
            ],
            'id_product' => [
                'title' => $this->l('Product ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ],
            'action' => [
                'title' => $this->l('Action'),
                'align' => 'center'
            ],
            'status' => [
                'title' => $this->l('Status'),
                'align' => 'center',
                'type' => 'bool',
                'active' => 'status',
                'orderby' => false,
                'callback' => 'displayStatus'
            ],
            'error_message' => [
                'title' => $this->l('Error Message'),
                'width' => 'auto',
                'callback' => 'truncateErrorMessage'
            ],
            'date_add' => [
                'title' => $this->l('Date'),
                'type' => 'datetime',
                'align' => 'center'
            ]
        ];
        
        $this->actions = ['view', 'delete'];
        
        // Default sort order
        $this->_defaultOrderBy = 'date_add';
        $this->_defaultOrderWay = 'DESC';
        
        // Filter beállítások
        $this->_filter = true;
        $this->list_no_link = true;
    }
    
    public function initContent()
    {
        parent::initContent();
        
        // Statisztikák
        $stats = $this->getLogStatistics();
        
        $this->context->smarty->assign([
            'stats' => $stats,
            'logs_count' => $this->getTotalLogs()
        ]);
        
        $this->content = $this->renderList();
        $this->context->smarty->assign('content', $this->content);
    }
    
    /**
     * Log statisztikák
     */
    private function getLogStatistics()
    {
        $stats = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'by_api' => []
        ];
        
        // Összes log
        $stats['total'] = (int)Db::getInstance()->getValue('
            SELECT COUNT(*) FROM `'._DB_PREFIX_.'mpstocksync_log`
        ');
        
        // Sikeres log-ok
        $stats['success'] = (int)Db::getInstance()->getValue('
            SELECT COUNT(*) FROM `'._DB_PREFIX_.'mpstocksync_log`
            WHERE status = 1
        ');
        
        // Sikertelen log-ok
        $stats['failed'] = (int)Db::getInstance()->getValue('
            SELECT COUNT(*) FROM `'._DB_PREFIX_.'mpstocksync_log`
            WHERE status = 0
        ');
        
        // API-k szerinti bontás
        $api_stats = Db::getInstance()->executeS('
            SELECT api_name, 
                   COUNT(*) as total,
                   SUM(IF(status=1,1,0)) as success,
                   SUM(IF(status=0,1,0)) as failed
            FROM `'._DB_PREFIX_.'mpstocksync_log`
            GROUP BY api_name
        ');
        
        if (is_array($api_stats)) {
            foreach ($api_stats as $api_stat) {
                $stats['by_api'][$api_stat['api_name']] = [
                    'total' => (int)$api_stat['total'],
                    'success' => (int)$api_stat['success'],
                    'failed' => (int)$api_stat['failed']
                ];
            }
        }
        
        return $stats;
    }
    
    /**
     * Összes log száma
     */
    private function getTotalLogs()
    {
        return (int)Db::getInstance()->getValue('
            SELECT COUNT(*) FROM `'._DB_PREFIX_.'mpstocksync_log`
        ');
    }
    
    /**
     * Status megjelenítése
     */
    public function displayStatus($status, $row)
    {
        if ($status) {
            return '<span class="label label-success"><i class="icon-check"></i> ' . $this->l('Success') . '</span>';
        } else {
            return '<span class="label label-danger"><i class="icon-remove"></i> ' . $this->l('Failed') . '</span>';
        }
    }
    
    /**
     * Hibaüzenet rövidítése
     */
    public function truncateErrorMessage($message, $row)
    {
        if (empty($message)) {
            return '-';
        }
        
        if (strlen($message) > 100) {
            return substr($message, 0, 100) . '...';
        }
        
        return $message;
    }
    
    /**
     * View action - log részletek megjelenítése
     */
    public function displayViewLink($token, $id, $name = null)
    {
        $url = $this->context->link->getAdminLink('AdminMpStockSyncLogs') . '&viewlog=' . (int)$id;
        return '<a href="' . $url . '" class="btn btn-default"><i class="icon-eye"></i> ' . $this->l('View') . '</a>';
    }
    
    public function renderView()
    {
        if (Tools::getValue('viewlog')) {
            $id_log = (int)Tools::getValue('viewlog');
            $log = Db::getInstance()->getRow('
                SELECT * FROM `'._DB_PREFIX_.'mpstocksync_log`
                WHERE id_log = ' . $id_log
            );
            
            if ($log) {
                $this->context->smarty->assign('log', $log);
                return $this->context->smarty->fetch(
                    _PS_MODULE_DIR_ . 'mpstocksync/views/templates/admin/logs/view.tpl'
                );
            }
        }
        
        return parent::renderView();
    }
    
    /**
     * Bulk delete
     */
    public function processBulkDelete()
    {
        if ($this->access('delete')) {
            if (is_array($this->boxes) && !empty($this->boxes)) {
                $ids = array_map('intval', $this->boxes);
                Db::getInstance()->execute('
                    DELETE FROM `'._DB_PREFIX_.'mpstocksync_log`
                    WHERE id_log IN (' . implode(',', $ids) . ')
                ');
                $this->confirmations[] = sprintf($this->l('%d log(s) deleted successfully'), count($ids));
            }
        }
    }
}
