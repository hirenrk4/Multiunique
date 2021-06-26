<?php
/**
 * Created By : Rohan Hapani
 */
namespace Multiunique\Productlist\Controller\Customer;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;
class Index extends Action
{
    public function execute() { 
     $this->_view->loadLayout(); 
     $this->_view->renderLayout(); 
    } 
}