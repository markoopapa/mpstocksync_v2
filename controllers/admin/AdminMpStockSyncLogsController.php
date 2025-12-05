<?php
class AdminMpStockSyncLogsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'mpstocksync_log';
        $this->identifier = 'id_log';
        parent::__construct();
    }
    
    public function initContent()
    {
        parent::initContent();
        
        $this->context->smarty->assign([
            'logs_url' => $this->context->link->getAdminLink('AdminMpStockSyncLogs')
        ]);
        
        $this->setTemplate('logs.tpl');
    }
}
