<?php 
namespace Core\Helper;

use \Zend\View\Helper\Placeholder\Container\AbstractStandalone;

class DocumentReady extends AbstractStandalone
{
    /**
     * Registry key for placeholder
     *
     * @var string
     */
    protected $regKey = 'Core_Helper_DocumentReady';
    
    public function toString($indent = null)
    {
        return 
        "<script type=\"text/javascript\">
            //<!--
            $(document).ready(function() {
                " . parent::toString() . "      
    	    }); 
            //-->
        </script>";
    }
    
}    