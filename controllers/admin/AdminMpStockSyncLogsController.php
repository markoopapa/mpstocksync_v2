<?php
class AdminMpStockSyncLogsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'mpstocksync_log';
        $this->identifier = 'id_log';
        $this->className = 'MpStockSyncLog';
        $this->lang = false;
        
        parent::__construct();
        
        $this->fields_list = [
            'id_log' => ['title' => 'ID', 'width' => 50],
            'api_name' => [
                'title' => 'Platform',
                'width' => 100,
                'callback' => 'renderPlatform'
            ],
            'id_product' => ['title' => 'Product ID', 'width' => 80],
            'action' => ['title' => 'Action', 'width' => 100],
            'status' => [
                'title' => 'Status',
                'width' => 80,
                'callback' => 'renderStatus'
            ],
            'error_message' => ['title' => 'Error', 'width' => 200],
            'date_add' => ['title' => 'Date', 'width' => 130, 'type' => 'datetime']
        ];
        
        $this->bulk_actions = [
            'delete' => [
                'text' => 'Delete selected',
                'confirm' => 'Delete selected logs?'
            ]
        ];
    }
    
    public function renderPlatform($value, $row)
    {
        if ($value == 'emag') {
            return '<span class="label" style="background:#0056b3">eMAG</span>';
        } elseif ($value == 'trendyol') {
            return '<span class="label" style="background:#ff6b00">Trendyol</span>';
        }
        return $value;
    }
    
    public function renderStatus($value, $row)
    {
        return $value 
            ? '<span class="label label-success">Success</span>'
            : '<span class="label label-danger">Failed</span>';
    }
    
    public function initToolbar()
    {
        parent::initToolbar();
        
        unset($this->toolbar_btn['new']);
        
        $this->page_header_toolbar_btn['clear_logs'] = [
            'href' => self::$currentIndex . '&clear_logs&token=' . $this->token,
            'desc' => 'Clear All Logs',
            'icon' => 'process-icon-delete',
            'confirm' => 'Are you sure?'
        ];
        
        $this->page_header_toolbar_btn['export_logs'] = [
            'href' => self::$currentIndex . '&export_logs&token=' . $this->token,
            'desc' => 'Export Logs',
            'icon' => 'process-icon-download'
        ];
    }
    
    public function initProcess()
    {
        parent::initProcess();
        
        if (Tools::getValue('clear_logs')) {
            $this->processClearLogs();
        }
        
        if (Tools::getValue('export_logs')) {
            $this->processExportLogs();
        }
    }
    
    private function processClearLogs()
    {
        Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'mpstocksync_log`');
        $this->confirmations[] = 'Logs cleared successfully';
        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
    }
    
    private function processExportLogs()
    {
        // CSV export implementáció
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="sync_logs_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Platform', 'Product ID', 'Action', 'Status', 'Error', 'Date']);
        
        $logs = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'mpstocksync_log` ORDER BY date_add DESC');
        
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id_log'],
                $log['api_name'],
                $log['id_product'],
                $log['action'],
                $log['status'] ? 'Success' : 'Failed',
                $log['error_message'],
                $log['date_add']
            ]);
        }
        
        fclose($output);
        exit;
    }
}
