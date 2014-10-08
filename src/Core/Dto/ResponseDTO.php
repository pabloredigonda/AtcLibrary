<?php
namespace Core\Dto;

use Core\Exception\SmException;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class ResponseDTO
 *
 * @category General
 * @package  Core\Dto
 * @author   Pablo Redigonda <pablo.redigonda@globant.com>
 */
class ResponseDTO
{
    /**
     * @Serializer\Groups({"list", "details", "visitsBilling"})
     */
	public $success;

    /**
     * @Serializer\Groups({"list", "details", "visitsBilling"})
     */
	public $redirectTo;

    /**
     * @Serializer\Groups({"list", "details", "visitsBilling"})
     */
	public $messages = array();

    /**
     * @Serializer\Groups({"list", "details", "visitsBilling"})
     */
	public $content;

    /**
     * @Serializer\Groups({"list", "details", "visitsBilling"})
     */
	public $error;

    /**
     * @Serializer\Groups({"list", "details", "visitsBilling"})
     */
	public $errors = array();

    /**
     * @param        $success
     * @param string $redirectTo
     * @param string $message
     * @param null   $exception
     */
    public function __construct($success, $redirectTo = '', $message = '', $exception = null)
    {
		$this->success = $success;
		$this->redirectTo = $redirectTo;

		if ($exception instanceof SmException) {
            $this->error = $exception->getKey();
            
		} else if (isset($exception) && getenv('APP_ENV') != 'production') {
            /** @var \Exception $exception */
		    $this->error = $exception->getMessage();
		}

		if ($message) {
			array_push($this->messages, $message);
		}
	}

    /**
     * setSuccess
     *
     * @param $success
     *
     * @return mixed
     */
    public function setSuccess($success)
    {
		$this->success = "$success"; // FIX boolean-string convertion
	}

    /**
     * setRedirectTo
     *
     * @param $redirectTo
     *
     * @return mixed
     */
    public function setRedirectTo($redirectTo)
    {
		$this->redirectTo = $redirectTo;
	}

    /**
     * addMessage
     *
     * @param $message
     *
     * @return mixed
     */
    public function addMessage($message)
    {
		array_push($this->messages, $message);
	}

    /**
     * addMessages
     *
     * @param      $form_messages
     * @param bool $useKey
     *
     * @return mixed
     */
    public function addMessages($form_messages, $useKey = true)
    {
		foreach ($form_messages as $key => $messages) {
		    if (!is_array($messages)) {
		        $this->addMessage($messages);
		    } else {
                foreach($messages as $message) {
                	
                	if($useKey){
                		$this->addMessage($key . ' - ' . $message);
                	}else{
                		$this->addMessage($message);
                	}
			    }
		    }
		}
	}

    /**
     * setContent
     *
     * @param $content
     *
     * @return mixed
     */
    public function setContent($content)
    {
		$this->content = $content;
	}

    /**
     * addError
     *
     * @param      $message
     * @param null $id
     *
     * @return mixed
     */
    public function addError($message, $id = null)
    {
		if( $id ){
			$this->errors[$id] = $message;
		}else{
			array_push($this->errors, $message);
		}
	}

    /**
     * addErrors
     *
     * @param      $form_errors
     * @param bool $useKey
     *
     * @return mixed
     */
    public function addErrors($form_errors, $useKey = true)
    {
		foreach ($form_errors as $key => $errors ) {
			
			if( is_array($errors) ){
			    return $this->addErrors($errors, $useKey);
			}			
        	
        	if($useKey){
        	    $this->addError($errors, $key);
        	}else{
        	    $this->addError($errors);
        	}
	    }
	}
}