<?php
namespace Core\Controller\Message\Controller;

use Core\Notification\User\UserMessageNotification;
use Zend\View\Model\ViewModel;
use Core\Controller\SMController;
use Core\Dto\ResponseDTO;
use Core\Model\Message;
use Zend\Json\Json;
use Core\Util\Constants;
use Zend\Filter\StripTags;
use Core\Exception\UnauthorizedAccessException;
use Core\Controller\Message\Form\MessageConversationForm;
use Core\Notification\Target\UserTarget;
use Core\Notification\Patient\PatientNewConversationNotification;
use Core\Notification\Staff\StaffNewConversationNotification;

class MessageController extends SMController {

    protected $my_picture;
    protected $other_picture;

    public function indexAction() {

        $view_vars = array();

        $conversationId = (int)$this->params('id');
        $offset = 0;

        $me = $this->getAuthStorage()->getUser();
        
        // Only users with professional profile can access this section
        if (!$this->getAuthStorage()->get('is_patient')) {
        	$profiles = $this->getAuthStorage()->get('staff_profiles');
        	$staffIsProfessional = in_array('professional', $profiles);
        	if (!$staffIsProfessional) {
        		return $this->redirect()->toRoute('office', array('controller' => 'dashboard'));
        	}
        }
        
        $my_picture = $this->getPicture($me);

        $conversationsArr = array();
        $conversations = $this->getMessageService()->findConversationsByUser($me);
        foreach ($conversations as $conversation) {

            if($conversation->getSender()->getId() == $this->getAuthStorage()->getUser()->getId()) {
                $conversation->setRecipientStatus($conversation->getSenderStatus());
            }

            $staff_name = '';
            $staff_user_id = '';
            $staff = $this->getService('OfficeStaff')->findById($conversation->getStaff());
            if ($staff) {
            	$staff_name = $staff->getFullName();
            	$staff_user_id = $staff->getUser()->getId();
            }
            
            $c = array('conversation' => $conversation,
                       'picture' => $this->getMessagePicture($conversation),
            		   'staff_name' => $staff_name,
            		   'staff_user_id' => $staff_user_id,
            );
            array_push($conversationsArr, $c);
        }

        // A conversation was selected
        if ($conversationId &&  $selectedConversation = $this->getMessageService()->findConversation($conversationId, $me)) {
        	$selectedConversation->setCreatedDate($selectedConversation->getCreatedDate()->setTimezone($this->getTimezone()));

        	$staff = $this->getService('OfficeStaff')->findById($selectedConversation->getStaff());
        	if ($staff) {
        		$view_vars['staff'] = $staff->getFullName();
        		$view_vars['staff_user_id'] = $staff->getUser()->getId();
        	}
        	
            $view_vars['isBlocked'] = $this->getMessageService()->isBlocked($selectedConversation, $this->getCounterPart($selectedConversation));
            $view_vars['messages'] = $this->processMessages($conversationId, $offset);
            $view_vars['selectedConversation'] = $selectedConversation;
            $view_vars['senderName'] = $this->getSenderName($conversation);
            $view_vars['total'] = $this->getMessageService()->findTotal($selectedConversation);

            array_unshift($view_vars['messages'], array('message' => $selectedConversation, 'picture' => $this->getMessagePicture($selectedConversation)));
            
            // First conversation is selected
        } else {
            if (count($conversations) > 0) {
                $conversation = $conversations[0];
                $conversation->setCreatedDate($conversation->getCreatedDate()->setTimezone($this->getTimezone()));

                $staff = $this->getService('OfficeStaff')->findById($conversation->getStaff());
                if ($staff) {
                	$view_vars['staff'] = $staff->getFullName();
                	$view_vars['staff_user_id'] = $staff->getUser()->getId();
                }
                
                $view_vars['isBlocked'] = $this->getMessageService()->isBlocked($conversation, $this->getCounterPart($conversation));
                $view_vars['selectedConversation'] = $conversation;
                $view_vars['senderName'] = $this->getSenderName($conversation);
                $view_vars['messages'] = $this->processMessages($conversations[0]->getId(), $offset);
                
                array_unshift($view_vars['messages'], array('message' => $conversations[0], 'picture' => $this->getMessagePicture($conversations[0])));
            }
        }

        $view_vars['conversations'] = $conversationsArr;
        $view_vars['me'] = $me;
        $view_vars['my_picture'] = $my_picture;

        $view = new ViewModel($view_vars);
        $view->setTemplate($this->getModule() . '/message/index');

        $layout = $this->layout();
        $layout->setTemplate('layout/' . $this->getModule());

        return $view;
    }

    public function conversationAction() {

        $request = $this->getRequest();
        $response = $this->getResponse();
        $responseDTO = new ResponseDTO('false');

        $office = $this->getAuthStorage()->getOffice();
        $recipient = $this->getAuthStorage()->getUser();
        $staff = $this->getAuthStorage()->get('staff');

        $conversationId = (int)$request->getPost('id');
        $offset =  (int)$request->getPost('offset');

        if ($conversationId) {
            $conversation = $this->getMessageService()->findConversation($conversationId, $recipient);
            if($conversation) {
                $messagesArr = $this->processMessages($conversationId, $offset);
                array_unshift($messagesArr, array('message' => $conversation, 'picture' => $this->getPicture($conversation->getSender(), $conversation->getRecipient()->getId())));

                $selectedConversation = $this->getMessageService()->findById($conversationId);
                $selectedConversation->setCreatedDate($selectedConversation->getCreatedDate()->setTimezone($this->getTimezone()));
                $isBlocked = $this->getMessageService()->isBlocked($selectedConversation, $staff);

                $responseDTO = new ResponseDTO('true');
                $responseDTO->setContent(
                    array(
                        'messages' => $messagesArr,
                        'conversation' => $conversation,
                        'isBlocked' => $isBlocked,
                        'recipient' => $recipient,
                        'total' => $this->getMessageService()->findTotal($conversation),
                    	'staff_name' => !$office ? $this->getStaffName($conversation->getStaff()) : ''
                    )
                );
            }
        }

        return $this->getResponse()->setContent($this->serialize($responseDTO));
    }

    public function sendAction() {

        $request = $this->getRequest();
        $response = $this->getResponse();
        $responseDTO = new ResponseDTO('false');

        $sender = $this->getAuthStorage()->getUser();
        $conversationId = (int)$request->getPost('conversationId');
        $conversation = $this->getMessageService()->findConversation($conversationId, $sender);
        if($conversation) {

            $enabled = $this->getOfficePatientService()->isEnabledForMessage($conversation->getPatient(), $conversation->getStaff());
            if (!$enabled) {
                $responseDTO = new ResponseDTO('false', '', $this->translate('El mensaje no pudo ser enviado'));
                return $this->getResponse()->setContent($this->serialize($responseDTO));
            }

            try {
                if (!$this->isParticipant($sender, $conversation)) {
                    throw new \Exception();
                }

                $filter = new StripTags(array('allowTags' => 'br'));
                $message = $filter->filter($request->getPost('message'));

                $sender = $this->getUserService()->findById($sender->getId());
                $recipient = ($conversation->getSender()->getId() == $sender->getId()) ? $conversation->getRecipient() : $conversation->getSender();
                $office = $this->getAuthStorage()->getOffice();

                $this->getMessageService()->saveMessage($conversation, $message, $sender, $recipient, $office);

                $this->notify(
                    new UserMessageNotification(
                        $recipient,
                        $sender->getId(),
                        $this->getPicture(
                            $sender,
                            $recipient->getId()
                        )
                    )
                );

                $responseDTO = new ResponseDTO('true');
            } catch (\Exception $e) {
                $responseDTO = new ResponseDTO('false', '', '', $e);
            }
        }

        return $this->getResponse()->setContent($this->serialize($responseDTO));
    }

    public function createConversationAction() {

        $response = $this->getResponse();
        $request = $this->getRequest();

        if ($request->isPost()) {

            $data = array(
                'recipient_id' => (int)$request->getPost('message_recipient_id'),
                'subject'	 => $request->getPost('message_subject'),
                'message'	 => $request->getPost('message_content'),
            );

            // Conversation recipient
            $isPatient = $this->getAuthStorage()->get('is_patient');
            if ($isPatient) {
                $patientUserId = $this->getAuthStorage()->getUser()->getId();
                $recipient = $this->getOfficeStaffService()->findById($data['recipient_id']);
                $sender = $this->getOfficePatientService()->findByOfficeAndUserId($recipient->getOffice(), $patientUserId);
                $patient = $sender;
                $staff = $recipient;
            } else {
                $sender	   = $this->getOfficeStaffService()->findById($this->getAuthStorage()->getStaff()->getId());
                $recipient = $this->getOfficePatientService()->findById($data['recipient_id']);
                $patient = $recipient;
                $staff = $sender;
            }

            $office	= $recipient->getOffice();

            // Form
            $form = new MessageConversationForm(
                $this->getEntityManager(),
                null,
                array(
                    'service' => $this->getOfficePatientService(),
                    'patient' => $patient,
                    'staff' => $staff,
                )
            );

            $message = new Message();

            //Validation
            $form->setData($data);
            if (!$form->isValid()) {
                $responseDTO = new ResponseDTO('false');
                //$responseDTO->addMessages($form->getMessages());
                $responseDTO->addMessage($this->translate('El destinatario no está habilitado para recibir mensajes'));                
                return $response->setContent($this->serialize($responseDTO));
            }

			$registeredUser = $recipient->getUser();
            if ($registeredUser == null) {
            	$responseDTO = new ResponseDTO('false');
            	$responseDTO->addMessage($this->translate('No se puede enviar mensaje ya que el destinatario no se encuentra registrado en la plataforma'));
            	return $response->setContent($this->serialize($responseDTO));
            }
            
            if ($registeredUser && $registeredUser->getId() == $this->getAuthStorage()->getUser()->getId()) {
            	$responseDTO = new ResponseDTO('false');
            	$responseDTO->addMessage($this->translate('No se puede enviar un mensaje a sí mismo'));
            	return $response->setContent($this->serialize($responseDTO));
            }
            
            $data = $form->getData();

            // Save message
            $message = $this->getMessageService()->saveNewConversation($data["subject"], $data["message"], $sender, $recipient, $office, $patient->getId(), $staff->getId() );

            // New conversation notification
            if (!$isPatient) {
                $this->notify(new PatientNewConversationNotification($patient, $staff, $message));
            } else {
                $this->notify(new StaffNewConversationNotification($staff, $patient, $message));
            }

            $responseDTO = new ResponseDTO('true', null, $this->translate('Mensaje enviado'));

            return $this->getResponse()->setContent($this->serialize($responseDTO));
        }
    }

    public function unreadAction() {
        $response = $this->getResponse();
        $request = $this->getRequest();

        if ($request->isPost()) {
            try {
                $messages = $this->getMessageService()->findTotalUnread($this->getAuthStorage()->getUser());
                $responseDTO = new ResponseDTO('true', '');
                $responseDTO->setContent($messages);
            } catch (\Exception $e) {
                $responseDTO = new ResponseDTO('false', null, $e->getMessage(), $e);
            }
        } else {
            $responseDTO = new ResponseDTO('false');
        }
        return $response->setContent($this->serialize($responseDTO));
    }

    public function previewAction() {
        $response = $this->getResponse();
        $request = $this->getRequest();

        if ($request->isPost()) {
            try {
                $office = $this->getAuthStorage()->getOffice();
                $messages = $this->getMessageService()->findUnread($this->getAuthStorage()->getUser());
                $messagesA = array();
                foreach($messages as $message) {
                    $m = $message;
                    $m->setPicture($this->getOfficePatientService()->getPicture($office, $message->getSender()));
                    
                    $m = array('message' => $m,
                    		   'staff_name' => !$office ? $this->getStaffName($m->getStaff()) : '');
                    array_push($messagesA, $m);
                }
                $responseDTO = new ResponseDTO('true', '');
                $responseDTO->setContent($messagesA);
            } catch (\Exception $e) {
                $responseDTO = new ResponseDTO('false', null, $e->getMessage(), $e);
            }
        } else {
            $responseDTO = new ResponseDTO('false');
        }
        return $response->setContent($this->serialize($responseDTO));
    }

    public function previewLiveAction() {
        $response = $this->getResponse();
        $request = $this->getRequest();

        if ($request->isPost()) {
            try {
                $office = $this->getAuthStorage()->getOffice();
                $conversationId = (int)$request->getPost('id');
                if($conversationId) {
                    $conversation = $this->getMessageService()->findById($conversationId);
                    $messages = $this->getMessageService()->findUnreadByConversation($this->getAuthStorage()->getUser(), $conversation);
                    $messagesA = array();
                    foreach($messages as $message) {
                        $message->setCreatedDate($message->getCreatedDate()->setTimezone($this->getTimezone()));
                        $message->setLastActivity($message->getLastActivity()->setTimezone($this->getTimezone()));
                        
                        $m = array('message' => $message,
                                   'picture' => $this->getMessagePicture($message),
                         		   'staff_name' => !$office ? $this->getStaffName($message->getConversation()->getStaff()) : '');
                        array_push($messagesA, $m);
                    }
                    $responseDTO = new ResponseDTO('true', '');
                    $responseDTO->setContent($messagesA);
                } else {
                    $responseDTO = new ResponseDTO('false');
                }
            } catch (\Exception $e) {
                $responseDTO = new ResponseDTO('false', null, $e->getMessage(), $e);
            }
        } else {
            $responseDTO = new ResponseDTO('false');
        }

        return $response->setContent($this->serialize($responseDTO));
    }

    public function conversationsAction() {
        $response = $this->getResponse();
        $recipient = $this->getAuthStorage()->getUser();

        $office = $this->getAuthStorage()->getOffice();
        
        $conversationsArr = array();
        $conversations = $this->getMessageService()->findUnreadConversationsByUser($recipient);
        foreach ($conversations as $conversation) {
            if($conversation->getSender()->getId() == $this->getAuthStorage()->getUser()->getId()) {
                $conversation->setSender($conversation->getRecipient());
            }
            
            $c = array('conversation' => $conversation,
                       'picture' => $this->getMessagePicture($conversation),
                       'is_mine' => $conversation->getLastActiveUser() == $this->getAuthStorage()->getUser()->getId(),
            		   'staff_name' => !$office ? $this->getStaffName($conversation->getStaff()) : '');
            array_push($conversationsArr, $c);
        }

        $responseDTO = new ResponseDTO('true', '');
        $responseDTO->setContent($conversationsArr);

        return $response->setContent($this->serialize($responseDTO));
    }

    public function deleteAction() {
        $request = $this->getRequest();
        $response = $this->getResponse();

        if ($request->isPost()) {
            try {
                $conversationId = (int)$request->getPost('id');
                $conversation = $this->getMessageService()->findById($conversationId);

                $user = $this->getAuthStorage()->getUser();
                $this->getMessageService()->deleteConversation($conversation, $user);
                $responseDTO = new ResponseDTO('true', '');
            } catch (\Exception $e) {
                $responseDTO = new ResponseDTO('false');
                $responseDTO->addMessage($e->getMessage());
            }
        } else {
            $responseDTO = new ResponseDTO('false');
        }

        return $response->setContent($this->serialize($responseDTO));
    }

    public function blockAction() {
        $request = $this->getRequest();
        $response = $this->getResponse();

        if ($request->isPost()) {
            try {
                $conversationId = (int)$request->getPost('id');
                $conversation = $this->getMessageService()->findById($conversationId);

                $staff = $this->getAuthStorage()->get('staff');
                $blockUser = $this->getCounterPart($conversation);

                $this->getMessageService()->blockUser($blockUser, $staff);
                $responseDTO = new ResponseDTO('true', '');
            } catch (\Exception $e) {
                $responseDTO = new ResponseDTO('false');
                $responseDTO->addMessage($e->getMessage());
            }
        } else {
            $responseDTO = new ResponseDTO('false');
        }

        return $response->setContent($this->serialize($responseDTO));
    }

    private function isParticipant($user, $conversation) {
        $id = $user->getId();
        if ($conversation->getSender()->getId() == $id || $conversation->getRecipient()->getId() == $id) {
            return true;
        }
        return false;
    }

    private function processMessages($conversationId, $offset) {
        $arr = array();

        $conversation = $this->getMessageService()->findById($conversationId);
        $user = $this->getAuthStorage()->getUser();

        $messages = $this->getMessageService()->findByConversation($conversation, $offset, $user);
        foreach ($messages as $message) {
            if ($message->getId() == $conversationId) {
                continue;
            }
            $message->setCreatedDate($message->getCreatedDate()->setTimezone($this->getTimezone()));
            $message->setLastActivity($message->getLastActivity()->setTimezone($this->getTimezone()));
            $m = array('message' => $message,
                       'picture' => $this->getMessagePicture($message));
            array_push($arr, $m);
        }

        $this->getMessageService()->markAsRead($conversationId, $user->getId());

        return $arr;
    }

    private function getMessagePicture($message) {

        $user = $this->getAuthStorage()->getUser();

        if ($message->getSender()->getId() == $user->getId()) {

            if (!isset($this->my_picture)) {
                $this->my_picture = $this->getPicture($user);
            }
            return $this->my_picture;

        } else {

            if (!isset($this->other_picture) || $message->getConversation() == null) {
                $this->other_picture = $this->getPicture($message->getSender());
            }
            return $this->other_picture;
        }
    }

    /**
     * Gets the user picture
     * @param $user
     * @param $counterPart  User id of the counter part. Pass this value when you want to obtain the picture value of the office_patient_staff relation.
     *                      ie. when you sent a notification
     * @return the picture
     */
    private function getPicture($user, $counterPart = null) {

        $loggedUser = $this->getAuthStorage()->getUser();
        $isPatient = $this->getAuthStorage()->get('is_patient');

        // Logged user is Patient
        if ($isPatient) {
            if ($loggedUser->getId() == $user->getId()) {
                $picture = $user->getPicture();
                if ($counterPart) {
                    $pic = $this->getOfficeStaffPatientService()->getPictures($loggedUser->getId(), $counterPart);
                    $picture = $pic['patient_picture'];
                }
                if ($picture) {
                	return Constants::PROFILE_PICTURE_PATH . '/' . $picture;
                }
            }
            $pic = $this->getOfficeStaffPatientService()->getPictures($loggedUser->getId(), $user->getId());
            if($pic && array_key_exists('staff_picture', $pic) && $pic['staff_picture']) {
                return  Constants::PROFILE_STAFF_PICTURE_PATH . '/' . $pic['staff_picture'];
            }
            return Constants::PROFILE_PICTURE_PATH . '/' . Constants::PROFILE_PICTURE_PLACEHOLDER;

            // Logged user is Staff
        } else {

            $staff = $this->getAuthStorage()->get('staff');
            $office = $this->getAuthStorage()->getOffice();

            if ($loggedUser->getId() == $user->getId()) {
                $picture = $staff->getPicture();
                if ($counterPart) {
                    $pic = $this->getOfficeStaffPatientService()->getPictures($counterPart, $loggedUser->getId());
                    $picture = $pic['staff_picture'];
                }
                if ($picture) {
                	return Constants::PROFILE_STAFF_PICTURE_PATH . '/' . $picture;
                }
            }
            $pic = $this->getOfficeStaffPatientService()->getPictures($user->getId(), $loggedUser->getId());
            if($pic && array_key_exists('patient_picture', $pic) && $pic['patient_picture']) {
                return  Constants::PROFILE_PICTURE_PATH . '/' . $pic['patient_picture'];
            }
            return Constants::PROFILE_PICTURE_PATH . '/' . Constants::PROFILE_PICTURE_PLACEHOLDER;
        }
    }

    private function getCounterPart($message) {
        $me = $this->getAuthStorage()->getUser();
        if ($me->getId() == $message->getSender()->getId()) {
            return $message->getRecipient();
        }
        return $message->getSender();
    }

    private function getModule() {
        $isPatient = $this->getAuthStorage()->get('is_patient');
        if ($isPatient) {
            return Constants::PATIENT_MODULE;
        }
        return Constants::OFFICE_MODULE;
    }

    private function getTimezone() {
        $isPatient = $this->getAuthStorage()->get('is_patient');
        if ($isPatient) {
            $timezone = ($this->getAuthStorage()->get('timezone')) ? $this->getAuthStorage()->get('timezone') : 'UTC';
            return new \DateTimeZone($timezone);
        }
        return $this->getAuthStorage()->getOffice()->getTimezoneObject();
    }
    
    private function getStaffName($staff_id) {
   		$staff = $this->getService('OfficeStaff')->findById($staff_id);
   		return $staff ? $staff->getFullName() : '';
    }
    
    private function getSenderName($conversation) {
    	$office = $this->getAuthStorage()->getOffice();
    	$user = $this->getAuthStorage()->getUser();
    	
    	if ($office) {
    		if ($conversation->getSender()->getId() == $user->getId()) {
    			return $conversation->getRecipient()->getFullName();
    		} else {
    			return $user->getFullName();
    		}
    	} else {
    		return $this->getService('OfficeStaff')->findById($conversation->getStaff())->getFullName();
    	}
    }
}
?>