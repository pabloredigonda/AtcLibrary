<?php
namespace Core\Helper;

use Zend\Form\Form;

class FormHelper {
    
    /**
     * Gets the element names of a given form 
     * @param Form $form        the form    
     * @param array $exclude    the names to be excluded from array
     * @return  an array of names 
     */
    public static function getElementNames(Form $form, $exclude = array()) {
        
        $attributes = array();
        
        $elements = $form->getElements();
        foreach ($elements as $element) {
        	array_push($attributes, $element->getName());
        }
        
        foreach ($exclude as $element) {
            $idx = array_search($element, $attributes);
            array_splice($attributes, $idx, 1);
        }
        return $attributes;
    }
    
    
	/**
	 * Replace language-specific characters by ASCII-equivalents.
	 * @param string $s
	 * @return string
	 */
	public static function normalizeChars($s) {
		$replace = array(
				'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'Ae', 'Å'=>'A', 'Æ'=>'A', 'Ă'=>'A',
				'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'ae', 'å'=>'a', 'ă'=>'a', 'æ'=>'ae',
				'þ'=>'b', 'Þ'=>'B',
				'Ç'=>'C', 'ç'=>'c',
				'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E',
				'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e',
				'Ğ'=>'G', 'ğ'=>'g',
				'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'İ'=>'I', 'ı'=>'i', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i',
				'Ñ'=>'N',
				'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'Oe', 'Ø'=>'O', 'ö'=>'oe', 'ø'=>'o',
				'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
				'Š'=>'S', 'š'=>'s', 'Ş'=>'S', 'ș'=>'s', 'Ș'=>'S', 'ş'=>'s', 'ß'=>'ss',
				'ț'=>'t', 'Ț'=>'T',
				'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'Ue',
				'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'ue',
				'Ý'=>'Y',
				'ý'=>'y', 'ý'=>'y', 'ÿ'=>'y',
				'Ž'=>'Z', 'ž'=>'z'
		);
		return strtr($s, $replace);
	}
	
}

?>