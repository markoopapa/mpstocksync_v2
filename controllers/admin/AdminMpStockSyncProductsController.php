<?php
class AdminMpStockSyncProductsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'mpstocksync_mapping';
        $this->identifier = 'id_mapping';
        parent::__construct();
    }
    
    public function initContent()
    {
        parent::initContent();
        
        $this->context->smarty->assign([
            'products_url' => $this->context->link->getAdminLink('AdminMpStockSyncProducts')
        ]);
        
        $this->setTemplate('products.tpl');
    }
}
