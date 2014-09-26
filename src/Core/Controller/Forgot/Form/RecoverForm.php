<?php

namespace Core\Controller\Forgot\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use DoctrineORMModule\Form\Element\DoctrineEntity;
use Doctrine\ORM\EntityManager;
use Core\Model\Users;

class RecoverForm extends Form {

	protected $inputFilter;

	public function __construct(EntityManager $entityManager, $name = null, $options = array())
	{
		parent::__construct('loginForm');

		$this->setHydrator(new \DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity($entityManager, '\Core\Model\Users'))->setObject(new Users());

		$this->setAttribute('method', 'post');

		$this->add(array(
				'type' => 'Zend\Form\Element\Text',
				'name' => 'password'
		));

		$this->add(array(
				'type' => 'Zend\Form\Element\Text',
				'name' => 'email'
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
							'name' => 'email',
							'required' => false,
					))
			);
			
			$inputFilter->add(
					$factory->createInput(array(
							'name' => 'password',
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
												'min'      => 5,
												'max'      => 100,
										),
									),
							),
					))
			);
				
			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}
}
