<?php
class AdminMpStockSyncSuppliersController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }
    
    public function initContent()
    {
        parent::initContent();
        
        $this->content = '
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-truck"></i> Suppliers
            </div>
            <div class="panel-body">
                <div class="alert alert-info">
                    <i class="icon-info"></i> Supplier synchronization will be available in a future update.
                </div>
                <p>
                    This feature will allow syncing stock from external supplier databases.
                </p>
            </div>
        </div>';
        
        $this->context->smarty->assign('content', $this->content);
    }
}
