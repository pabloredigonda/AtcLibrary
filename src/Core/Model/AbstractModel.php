<?php
namespace Core\Model;
// use JMS\Serializer\Annotation\Exclude;

class AbstractModel
{
    protected $forbiddenTypes = array('DateTime');
    
    public function getObjectArray($recursive = 1) {
        $methods = get_class_methods(get_class($this));

        $result = array();
        foreach($methods as $method) {
            if (substr($method, 0, 3) == 'get' && $method!='getObjectArray') {
                $m = lcfirst(substr($method, 3));
                $result[$m] = $this->{$method}();
                if($recursive && is_object($result[$m]) && !in_array(get_class($result[$m]), $this->forbiddenTypes)) {
                    if(method_exists($result[$m], 'getObjectArray')) {
                        $result[$m]->object = $result[$m]->getObjectArray(0);
                    }
                }
            }
        }
        return $result;
    }
}
