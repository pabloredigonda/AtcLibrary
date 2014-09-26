<?php
namespace Core\Validator;

use Zend\Validator\LessThan;

class DateLessThan extends LessThan
{

    protected function createMessage($messageKey, $value)
    {
        if (! isset($this->abstractOptions['messageTemplates'][$messageKey])) {
            return null;
        }
        
        $message = $this->abstractOptions['messageTemplates'][$messageKey];
        $message = $this->translateMessage($messageKey, $message);
        
        $value = $value->format("d/m/Y");
        $max = $this->max->format("d/m/Y");
        
        $message = str_replace('%value%', (string) $value, $message);
        $message = str_replace('%max%', (string) $max, $message);
        
        return $message;
    }
}

