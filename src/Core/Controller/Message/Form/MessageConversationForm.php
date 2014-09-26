<?php

namespace Core\Controller\Message\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

use Core\Model\OfficeStaff;
use Doctrine\ORM\EntityManager;
use Core\Exception\InvalidServiceException;

use Core\Service\OfficePatientService;
use Core\Validator\Patient\PatientEnabled;
use Core\Model\AbstractModel;

class MessageConversationForm extends Form {

	protected $inputFilter;
	protected $patientEnabledValidator;
	
	public function __construct(EntityManager $entityManager, $name = null, $options = array()) 
	{
		parent::__construct('messageConversationForm');

		/**
		 * patient 
		 */
		if(!isset($options['patient']) ){
			throw new InvalidServiceException("Patient is required");
		}
		
		/**
		 * staff
		 */
		if(!isset($options['staff'])){
		    throw new InvalidServiceException("Staff is required");
		}
		
		/**
		 * service
		 */
		if(!isset($options['service']) OR  ! $options['service'] instanceof OfficePatientService ){
			throw new InvalidServiceException("Invalid service instance, OfficePatientService is requiered");
		}
		
		$this->patientEnabledValidator = new PatientEnabled( array(
            'service' 	=> $options['service'],
			'patient'  	=> $options['patient'],
		    'staff'     => $options['staff'],
		));
		
		unset($options['service']);
		unset($options['patient']);
		unset($options['staff']);
		
		$this->setAttribute('method', 'post');

		$this->add(array(
			'type' => 'Zend\Form\Element\Text',
			'name' => 'recipient_id'
		));
		
		$this->add(array(
				'type' => 'Zend\Form\Element\Text',
				'name' => 'subject'
		));

		$this->add(array(
			'type' => 'Zend\Form\Element\Text',
			'name' => 'message'
		));
		
		// Input filter
		$this->setInputFilter($this->getInputFilter());
	}
	
	public function getInputFilter()
	{
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();
			$factory     = new InputFactory();
	
			$inputFilter->add(
				$factory->createInput(array(
					'name' => 'recipient_id',
					'required' => true,
					'filters'  => array(
							array('name' => 'StripTags'),
							array('name' => 'StringTrim'),
					),
					'validators' => array(
	                    $this->patientEnabledValidator 
	                ),
				))
			);
			
			$inputFilter->add(
					$factory->createInput(array(
							'name' => 'subject',
							'required' => true,
							'filters'  => array(
									array('name' => 'StripTags'),
									array('name' => 'StringTrim'),
							),
							'validators' => array(
									array(
											'name'    => 'StringLength',
											'options' => array(
													'encoding' => 'UTF-8',
													'min'      => 1,
													'max'      => 50,
											),
									),
							),
					))
			);

			$inputFilter->add(
					$factory->createInput(array(
							'name' => 'message',
							'required' => true,
							'filters'  => array(
									array('name' => 'StripTags'),
									array('name' => 'StringTrim'),
							),
							'validators' => array(
									array(
											'name'    => 'StringLength',
											'options' => array(
													'encoding' => 'UTF-8',
													'min'      => 1,
											),
									),
							),
					))
			);			

			$this->inputFilter = $inputFilter;
		}
		
		return $this->inputFilter;
	}
	
// 	public function isValid()
// 	{
// 		$options = array(
// 			'patientId'		=> $this->getObject()->getId()
// 		);
		
// 		$this->documentExistsValidator->setOptions($options);
		
// 		return parent::isValid();
// 	}
}

?>