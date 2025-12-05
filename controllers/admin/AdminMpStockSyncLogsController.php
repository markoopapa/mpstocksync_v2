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
                'filter_key' => 'api_name'
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
    }
    
    public function initContent()
    {
        parent::initContent();
        
        // JAVÍTVA: WHERE feltétel eltávolítása vagy javítása
        $this->_where = ''; // Üres WHERE feltétel
        
        $this->content = $this->renderList();
        $this->context->smarty->assign('content', $this->content);
    }
    
    /**
     * Status megjelenítése
     */
    public function displayStatus($status, $row)
    {
        if ($status) {
            return '<span class="label label-success"><i class="icon-check"></i> Success</span>';
        } else {
            return '<span class="label label-danger"><i class="icon-remove"></i> Failed</span>';
        }
    }
    
    /**
     * RenderList override - SQL hiba javítása
     */
    public function renderList()
    {
        // Biztosítjuk, hogy ne legyen hibás WHERE feltétel
        $this->_where = '';
        $this->_group = '';
        
        return parent::renderList();
    }
}
