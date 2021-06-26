<?php
namespace Multiunique\Productlist\Block;

Class Index extends \Magento\Framework\View\Element\Template
{    
    
    protected $_productCollectionFactory;
    protected $helperFactory;
    protected $listProductBlock;
    private $request;
    protected $_categoryFactory;
    protected $_attributeFactory;
    protected $_productListHelper;
    protected $httpContext;
        
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,        
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,  
        \Magento\Catalog\Helper\ImageFactory $helperFactory,
        \Magento\Catalog\Block\Product\ListProduct $listProductBlock,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    )
    {    
        $this->_attributeFactory = $attributeFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->helperFactory = $helperFactory;
        $this->listProductBlock = $listProductBlock;
        $this->request =$request;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
    }
    
    public function getProductCollection()
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $filterableAttributes = $objectManager->create(\Magento\Catalog\Model\Layer\Category\FilterableAttributeList::class);
        $attributes = $filterableAttributes->getList();

        $price=$this->getRequest()->getParam('price');
        $cat=$this->getRequest()->getParam('cat');
        $sortby=$this->getRequest()->getParam('product_list_order');
        if (empty($sortby)) {
           $sortby='position';
        }
        $listdesc=$this->getRequest()->getParam('product_list_dir');
        
        $page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
           //get values of current limit
        $pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 8;
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('handle_display', array('eq' => 1));
        $collection->setOrder('title','ASC');
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
           
        foreach ($attributes as $attributes1 ) {
                     $attributefrontend = $attributes1->getFrontendInput();
                   if (!empty($_REQUEST[$attributes1->getAttributeCode()]) && $attributefrontend=="select") {
                        $collection->addAttributeToFilter($attributes1->getAttributeCode(), $_REQUEST[$attributes1->getAttributeCode()]);
                    }
                   if (!empty($_REQUEST[$attributes1->getAttributeCode()]) && $attributefrontend=="multiselect") {
                     $collection->addAttributeToFilter($attributes1->getAttributeCode(), array('in' => array((int)$_REQUEST[$attributes1->getAttributeCode()])));
                   }
                    
                    if (isset($price) || isset($cat) || isset($_REQUEST[$attributes1->getAttributeCode()]) || isset($sortby)) {

                         $pricebetween=explode("-",$price);        
                     
                       if (!empty($pricebetween[0])) {
                         $collection->addAttributeToFilter('price', array('gteq' => $pricebetween[0]));
                       }
                       if (!empty($pricebetween[1])) { 
                           $collection->addAttributeToFilter('price', array('lteq' => $pricebetween[1]));
                       }
                       if (!empty($cat)) {
                           $category = $this->_categoryFactory->create()->load($cat);
                           $collection->addCategoryFilter($category);
                           $collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
                           $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                       }
         
                          
                    }
                    if ($sortby=='position') {
                            $collection->setOrder($sortby,'ASC');
                       }
                       if (!empty($listdesc)) {
                           $collection->setOrder($sortby,'DESC');
                       } else {
                           $collection->setOrder($sortby,'ASC');
                       }
             
        }
        /*echo $collection->getSelect(); */

        return $collection;
    }

    public function getImage($product)
    {
     // for image url get
      return  $this->helperFactory->create()->init($product, 'product_thumbnail_image')->resize(150)->getUrl();       
    }
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('products'));
        if ($this->getProductCollection()) {
        $toolbar = $this->getLayout()
                   ->createBlock(
                'Magento\Catalog\Block\Product\ProductList\Toolbar',
                'product_list_toolbar'
                )
                ->setTemplate('Multiunique_Productlist::toolbar.phtml')
                ->setCollection($this->getProductCollection());

            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'AllProduct.product.pager'
            )->setAvailableLimit(array(8=>8,16=>16,24=>24))->setShowPerPage(true)->setCollection(
                $this->getProductCollection()
            );
            $this->setChild('pager', $pager);
            $this->setChild('toolbar', $toolbar);
            $this->getProductCollection()->load();
        }
        return $this;
    }
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }
    public function getMode()
    {
        return $this->getChildBlock('toolbar')->getCurrentMode();
    }
    public function getAddToCartPostParams($product)
    {
    return $this->listProductBlock->getAddToCartPostParams($product);
    }
    
}