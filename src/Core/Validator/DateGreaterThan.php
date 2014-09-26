<?php
namespace Core\Validator;

use Zend\Validator\GreaterThan;

class DateGreaterThan extends GreaterThan
{

    protected function createMessage($messageKey, $value)
    {
        if (! isset($this->abstractOptions['messageTemplates'][$messageKey])) {
            return null;
        }
        
        $message = $this->abstractOptions['messageTemplates'][$messageKey];
        $message = $this->translateMessage($messageKey, $message);
        
        $value = $value->format("d/m/Y");
        $min = $this->min->format("d/m/Y");
        
        $message = str_replace('%value%', (string) $value, $message);
        $message = str_replace('%min%', (string) $min, $message);
        
        return $message;
    }
}

