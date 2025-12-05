<?php
class AdminMpStockSyncApiController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        
        $this->meta_title = 'API Settings';
    }
    
    public function initContent()
    {
        parent::initContent();
        
        $this->context->smarty->assign([
            'api_url' => $this->context->link->getAdminLink('AdminMpStockSyncApi')
        ]);
        
        $this->setTemplate('api_settings.tpl');
    }
}
