<?php
namespace Core\Controller;

use JMS\Serializer\SerializationContext;
use Locale;
use Zend\Mvc\Controller\AbstractActionController;
use Core\Service\AbstractService;

class SMController extends AbstractActionController
{
    protected $serializer;

    /**
     * Returns a $type formated object (begin $type either json, xml or yaml)
     *
     * @param mixed $object
     *            Object to serialize
     * @param Array|string $groups
     *            Group lists to serialize
     * @param string $format
     *            Either json, xml or yaml
     * @param bool $maxDepthCheck
     *            Check for circular references (ie: MaxDepth)
     * @param bool $serializeNull
     *            Serialize null values (by default ignores them)
     *            
     * @return mixed
     */
    public function serialize($object, $groups = null, $format = 'json', $maxDepthCheck = true, $serializeNull = true)
    {
        if (! $this->serializer) {
            $this->serializer = $this->getServiceLocator()->get('jms_serializer.serializer');
        }
        
        $context = SerializationContext::create();
        
        // Check for circular references
        $maxDepthCheck ? $context->enableMaxDepthChecks() : null;
        
        // Null values causes the properties to be ignored
        $context->setSerializeNull($serializeNull);
        
        if ($groups) {
            $context->setGroups($groups);
        }
        
        return $this->serializer->serialize($object, $format, $context);
    }

    /**
     * Dispatchs a notification object to any dispatcher who's listening
     * for that notification.
     *
     * @param mixed $key
     *            Notification key or Notification object
     * @param null $params
     *            Params
     * @param null $target
     *            Target notification
     *            
     * @return mixed
     */
    public function notify($key, $params = null, $target = null)
    {
        if (is_object($key)) {
            $notification = $key;
            $nd = $this->getServiceLocator()->get('Core\Service\NotificationDispatcherService');
            $nd->dispatch($notification);
        } else {
            $ns = $this->getServiceLocator()->get('Core\Service\NotificationService');
            $ns->dispatch($key, $params, $target);
        }
    }

    public function getService($service = null)
    {
        if (! $service && isset($this->service)) {
            $service = $this->service;
        }
        if ($service) {
            return $this->getServiceLocator()->get("Core\\Service\\" . $service . "Service");
        }
        throw new \Exception('Must define a service property with an appropiated service class.');
    }

    public function getLocaleAndLanguage()
    {
        $headers = $this->getRequest()->getHeaders();
        $config = $this->getServiceLocator()->get('Config');
        $supported = $config['translator']['supported'];
        $default_locale = $config['translator']['locale'];
        $default_language = $config['translator']['language'];
        $alias = $config['translator']['alias'];
        
        if (isset($config['default_locale']) && $config['default_locale']) {
            $default_locale = $config['default_locale'];
            if (! ! ($match = Locale::lookup($supported, $default_locale))) {
                $default_locale = $alias[$match];
                $default_language = $match;
            }
        } else 
            if ($headers->has('Accept-Language')) {
                // Determine language by looking at the requests header
                $locales = $headers->get('Accept-Language')->getPrioritized();
                foreach ($locales as $locale) {
                    $locale = $locale->getLanguage();
                    if (! ! ($match = Locale::lookup($supported, $locale))) {
                        return array(
                            "locale" => $alias[$match],
                            "language" => $match
                        );
                    }
                }
            }
        return array(
            "locale" => $default_locale,
            "language" => $default_language
        );
    }

    public function addErrorMessage($msg)
    {
        $this->flashMessenger()->addErrorMessage($msg);
    }

    public function addErrorMessages($form)
    {
        foreach ($form->getMessages() as $key => $message) {
            foreach ($message as $msg) {
                $this->flashMessenger()->addErrorMessage($key . ' - ' . $msg);
            }
        }
    }

    public function getErrorMessages()
    {
        return $this->flashMessenger()->getErrorMessages();
    }

    protected function checkRequestMethod()
    {
        if (! $this->getRequest()->isPost()) {
            throw new \Core\Exception\InvalidRequestMethodException();
        }
    }

    public function getList(AbstractService $service, $param = null, $value = null, $sortColumn = null)
    {
        $sort = array();
        if ($sortColumn) {
            $sort = array(
                $sortColumn => 'ASC'
            );
        }
        
        if ($param && $value) {
            $elements = $service->findAllBy($param, $value, $sort);
        } else {
            $elements = $service->findAllBy(null, null, $sort);
        }
        return $this->toList($elements);
    }

    public function toList($elements)
    {
        $arrElements = array();
        foreach ($elements as $element) {
            $arrElements[] = array(
                'name' => $element->getDisplayName(),
                'id' => $element->getId()
            );
        }
        return $arrElements;
    }

    public function toArray($elements)
    {
        $data = array();
        
        foreach ($elements as $element) {
            $data[] = $element->getObjectArray();
        }
        
        return $data;
    }

    public function translate($key)
    {
        return $this->getTranslator()->translate($key);
    }

    public function getTranslator()
    {
        return $this->getServiceLocator()->get('translator');
    }

    public function getEntityManager()
    {
        return  $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    }

    public function getLanguageCode()
    {
        return $this->getAuthStorage()->get("languageCode");
    }

    public function getAuthService()
    {
        return $this->getServiceLocator()->get('AuthService');
    }

    public function getAuthStorage()
    {
        return $this->getServiceLocator()->get('Application\Model\AuthStorage');
    }

    /**
     * getCurrentUser
     *
     * @return mixed
     */
    public function getCurrentUser()
    {
        $userId = $this->getAuthStorage()->getUser();
        return $this->getService('User')->findById($userId);
    }
}

