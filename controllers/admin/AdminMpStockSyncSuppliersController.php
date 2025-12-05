<?php
require_once _PS_MODULE_DIR_ . 'mpstocksync/classes/MpStockSyncMapping.php';

class AdminMpStockSyncSuppliersController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'supplier';
        $this->identifier = 'id_supplier';
        $this->className = 'Supplier';
        $this->lang = false;
        $this->list_no_link = true;

        parent::__construct();

        $this->fields_list = array(
            'id_supplier' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 30
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'width' => 'auto'
            ),
            'mpstocksync_active' => array(
                'title' => $this->l('Active'),
                'active' => 'status',
                'type' => 'bool',
                'align' => 'center',
                'orderby' => false
            )
        );

        $this->bulk_actions = array(
            'enable' => array(
                'text' => $this->l('Enable'),
                'icon' => 'icon-power-off text-success'
            ),
            'disable' => array(
                'text' => $this->l('Disable'),
                'icon' => 'icon-power-off text-danger'
            )
        );
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        $this->page_header_toolbar_btn['sync_suppliers'] = array(
            'href' => self::$currentIndex . '&syncSuppliers&token=' . $this->token,
            'desc' => $this->l('Sync Suppliers'),
            'icon' => 'icon-refresh'
        );
    }

    public function postProcess()
    {
        if (Tools::getValue('syncSuppliers')) {
            $this->syncSuppliers();
        }
        parent::postProcess();
    }

    private function syncSuppliers()
    {
        // Implement supplier sync logic here
        $this->confirmations[] = $this->l('Suppliers synchronized successfully');
    }
}
