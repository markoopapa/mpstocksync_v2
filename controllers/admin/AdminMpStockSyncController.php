<?php
class AdminMpStockSyncController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        
        // Átirányítás a dashboardra
        Tools::redirectAdmin(
            Context::getContext()->link->getAdminLink('AdminMpStockSyncDashboard')
        );
    }
}
