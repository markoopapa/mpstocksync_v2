<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminMpStockSyncProductMappingController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        
        $this->bootstrap = true;
        $this->table = 'mp_stock_product_mapping'; // Ez a tábla neve
        $this->className = 'MpStockProductMapping'; // Model osztály neve
        $this->identifier = 'id_mapping';
        $this->lang = false;
        
        // Ellenőrizzük, hogy létezik-e a tábla
        if (!$this->tableExists()) {
            $this->warnings[] = $this->l('Database table does not exist. Please install the module properly.');
        }
        
        // Lista oszlopok
        $this->fields_list = array(
            'id_mapping' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'id_product' => array(
                'title' => $this->l('Product ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'marketplace_product_id' => array(
                'title' => $this->l('Marketplace Product ID'),
                'align' => 'center'
            ),
            'marketplace_name' => array(
                'title' => $this->l('Marketplace'),
                'align' => 'center'
            ),
            'last_sync' => array(
                'title' => $this->l('Last Sync'),
                'type' => 'datetime',
                'align' => 'center'
            )
        );
        
        $this->actions = array('edit', 'delete');
    }
    
    /**
     * Ellenőrzi, hogy létezik-e a tábla
     */
    private function tableExists()
    {
        $sql = 'SHOW TABLES LIKE "' . _DB_PREFIX_ . 'mp_stock_product_mapping"';
        return (bool) Db::getInstance()->executeS($sql);
    }
    
    public function initContent()
    {
        parent::initContent();
        
        // Ellenőrizzük a tábla létezését
        if (!$this->tableExists()) {
            $this->content = $this->displayTableError();
            $this->context->smarty->assign('content', $this->content);
            return;
        }
        
        // Ellenőrizzük az API beállításokat
        $api_key = Configuration::get('MP_STOCK_API_KEY');
        $api_secret = Configuration::get('MP_STOCK_API_SECRET');
        
        if (empty($api_key) || empty($api_secret)) {
            $this->warnings[] = $this->l('Please configure API settings first.');
        }
        
        // Betöltjük a listát
        $this->content = $this->renderList();
        $this->context->smarty->assign('content', $this->content);
    }
    
    public function renderList()
    {
        // Ha nincs tábla, mutatunk hibát
        if (!$this->tableExists()) {
            return $this->displayTableError();
        }
        
        // Egyszerű lista betöltés
        $this->_select = '*';
        $this->_orderBy = 'id_mapping';
        $this->_orderWay = 'DESC';
        
        return parent::renderList();
    }
    
    public function renderForm()
    {
        if (!$this->tableExists()) {
            $this->errors[] = $this->l('Database table is missing.');
            return '';
        }
        
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Product Mapping'),
                'icon' => 'icon-link'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Product ID'),
                    'name' => 'id_product',
                    'required' => true,
                    'col' => 4
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Marketplace Product ID'),
                    'name' => 'marketplace_product_id',
                    'required' => true,
                    'col' => 4
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Marketplace'),
                    'name' => 'marketplace_name',
                    'required' => true,
                    'options' => array(
                        'query' => array(
                            array('id' => 'shopify', 'name' => 'Shopify'),
                            array('id' => 'amazon', 'name' => 'Amazon'),
                            array('id' => 'ebay', 'name' => 'Ebay'),
                            array('id' => 'woocommerce', 'name' => 'WooCommerce')
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                    'col' => 4
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default'
            )
        );
        
        return parent::renderForm();
    }
    
    /**
     * Hibaüzenet, ha nincs tábla
     */
    private function displayTableError()
    {
        return '
        <div class="alert alert-danger">
            <h4><i class="icon-warning"></i> ' . $this->l('Database Error') . '</h4>
            <p>' . $this->l('The required database table does not exist.') . '</p>
            <p>' . $this->l('Please run this SQL query in your database:') . '</p>
            <pre style="background:#f1f1f1;padding:10px;border-radius:3px;">
CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mp_stock_product_mapping` (
  `id_mapping` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_product` INT(11) UNSIGNED NOT NULL,
  `marketplace_product_id` VARCHAR(255) NOT NULL,
  `marketplace_name` VARCHAR(50) NOT NULL,
  `last_sync` DATETIME NULL,
  `date_add` DATETIME NOT NULL,
  `date_upd` DATETIME NOT NULL,
  PRIMARY KEY (`id_mapping`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            </pre>
            <p>
                <a href="' . $this->context->link->getAdminLink('AdminModules') . '" class="btn btn-default">
                    <i class="icon-arrow-left"></i> ' . $this->l('Back to Modules') . '
                </a>
            </p>
        </div>';
    }
    
    public function postProcess()
    {
        if (Tools::isSubmit('submitAdd' . $this->table)) {
            $id_product = (int)Tools::getValue('id_product');
            $marketplace_product_id = Tools::getValue('marketplace_product_id');
            $marketplace_name = Tools::getValue('marketplace_name');
            
            if ($id_product > 0 && !empty($marketplace_product_id) && !empty($marketplace_name)) {
                $this->confirmations[] = $this->l('Mapping saved successfully');
            } else {
                $this->errors[] = $this->l('Please fill all fields correctly');
            }
        }
        
        parent::postProcess();
    }
}
