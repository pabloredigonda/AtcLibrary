<?php
namespace Core\Controller;
 
use JMS\Serializer\SerializationContext;
use Locale;
use Zend\Mvc\Controller\AbstractActionController;
use Core\Service\AbstractService;
use Core\Util\Logger\FileLogger;

class SMController extends AbstractActionController {
 
    protected $translator;
    protected $entityManager;
    protected $fileLogger;
    protected $authservice;
    protected $authStorage;
    protected $officeService;
    protected $officeStaffService;
    protected $officeStaffWorkdayService;
    protected $officePayerService;
    protected $sessionCountry;
    protected $sessionUser;
    protected $profileService;
    protected $userService;
    protected $userSettingService;
    protected $inviteService;
    protected $specialtyService;
    protected $notificationService;
    protected $officeProviderService;
    protected $officeInsuranceService;
    protected $officePatientService;
    protected $imageService;
    protected $insuranceService;
    protected $officeAppointmentService;
    protected $messageService;
    protected $officeStaffPatientService;
    protected $officeBillingService;
    protected $patientVisitService;
    protected $patientVisitMedicamentService;
    protected $ehrService;
    protected $ehrCommentService;
    protected $ehrDerivationService;
    protected $ehrDescriptionService;
    protected $ehrIndicationService;
    protected $ehrMedicamentService;
    protected $ehrPracticeService;
    protected $ehrProblemService;
    protected $ehrResultService;
    protected $ehrVitalSignService;
    protected $systemInsuranceService;
    protected $systemCountryStateService;
    protected $systemCountryService;
    protected $systemUsersStatusService;
    protected $systemAppointmentStatusService;
    protected $systemProblemService;
    protected $systemProblemStatusService;
    protected $systemProblemTagService;
    protected $systemVitalSignService;
    protected $systemMedicamentService;
    protected $systemMedicamentPeriodicityService;
    protected $systemPracticeService;
    protected $newsService;
    protected $serializer;

    /**
     * Returns a $type formated object (begin $type either json, xml or yaml)
     *
     * @param mixed         $object        Object to serialize
     * @param Array|string  $groups        Group lists to serialize
     * @param string        $format        Either json, xml or yaml
     * @param bool          $maxDepthCheck Check for circular references (ie: MaxDepth)
     * @param bool          $serializeNull Serialize null values (by default ignores them)
     *
     * @return mixed
     */
    public function serialize(
        $object,
        $groups = null,
        $format = 'json',
        $maxDepthCheck = true,
        $serializeNull = true
    ) {
        if (!$this->serializer) {
            $this->serializer = $this->getServiceLocator()
                ->get('jms_serializer.serializer');
        }

        $context = SerializationContext::create();

        // Check for circular references
        $maxDepthCheck ? $context->enableMaxDepthChecks() : null;

        // Null values causes the properties to be ignored
        $context->setSerializeNull($serializeNull);

        if ($groups) {
            $context->setGroups($groups);
        }

        return $this->serializer->serialize(
            $object,
            $format,
            $context
        );
    }

    /**
     * Dispatchs a notification object to any dispatcher who's listening
     * for that notification.
     *
     * @param mixed $key     Notification key or Notification object
     * @param null  $params  Params
     * @param null  $target  Target notification
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
    
    /**
     * Gets the logger instance
     * @return \Core\Util\FileLogger
     */
    public function getLogger() {
        
        if (!isset($fileLogger)) {
            $config = $this->getServiceLocator()->get('Config');
            $logConfig = $config['log'];
            $this->fileLogger = new FileLogger($logConfig['filename'], $logConfig['dir']);
        }
        return $this->fileLogger;
    }

    public function getService($service = null) {
        if (!$service && isset($this->service)) {
            $service = $this->service;
        }
        if ($service) {
            return $this->getServiceLocator()->get("Core\\Service\\" . $service . "Service");
        }
        throw new \Exception('Must define a service property with an appropiated service class.');
    }

    public function getLocaleAndLanguage() {

        $headers    = $this->getRequest()->getHeaders();
        $config     = $this->getServiceLocator()->get('Config');
        $supported 	= $config['translator']['supported'];
        $default_locale   = $config['translator']['locale'];
        $default_language = $config['translator']['language'];
        $alias 		= $config['translator']['alias'];

        if (isset($config['default_locale']) && $config['default_locale']) {
            $default_locale = $config['default_locale'];
            if (!!($match = Locale::lookup($supported, $default_locale))) {
                $default_locale   = $alias[$match];
                $default_language = $match;
            }
        } else if ($headers->has('Accept-Language')) {
            // Determine language by looking at the requests header
        	$locales = $headers->get('Accept-Language')->getPrioritized();
        	foreach ($locales as $locale) {
        		$locale = $locale->getLanguage();
        		if (!!($match = Locale::lookup($supported, $locale))) {
        			return array( "locale" => $alias[$match], "language" => $match);
        		}
        	}
        }
        return array( "locale" => $default_locale, "language" => $default_language);
    }

    public function addErrorMessage($msg) {
        $this->flashMessenger()->addErrorMessage($msg);
    }

    public function addErrorMessages($form) {
        foreach ($form->getMessages() as $key => $message) {
        	foreach ($message as $msg) {
        		$this->flashMessenger()->addErrorMessage($key . ' - ' . $msg);
        	}
        }
    }

    public function getErrorMessages() {
        return $this->flashMessenger()->getErrorMessages();
    }
    
    protected function checkRequestMethod()
    {
        if (!$this->getRequest()->isPost()) {
            throw new \Core\Exception\InvalidRequestMethodException();
        }
    }

    public function getList(AbstractService $service, $param = null, $value = null, $sortColumn = null) {

        $sort = array();
        if ($sortColumn) {
            $sort = array($sortColumn => 'ASC');
        }
        
        if ($param && $value) {
            $elements = $service->findAllBy($param, $value, $sort);
        } else {
            $elements = $service->findAllBy(null, null, $sort);
        }
        return $this->toList($elements);

    }

    public function toList($elements) {
        $arrElements = array();
        foreach ($elements as $element) {
        	$arrElements[] = array('name' => $element->getDisplayName(), 'id' => $element->getId());
        }
        return $arrElements;
	}

    public function toArray($elements)
    {
        $data = array();
        
        foreach ($elements as $element){
            $data[] = $element->getObjectArray();
        }
        
        return $data;
    }
	
	
    public function translate($key) {
        return $this->getTranslator()->translate($key);
    }
    
    public function getTranslator() {
        if (! $this->translator) {
        	$this->translator = $this->getServiceLocator()->get('translator');
        }
        return $this->translator;
    }

    public function getEntityManager () {
    	if (! $this->entityManager) {
    		$this->entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	}
    	return $this->entityManager;
    }
    
    public function getLanguageCode () {
        return $this->getAuthStorage()->get("languageCode");
    }

    public function getAuthService() {
    	if (! $this->authservice) {
    		$this->authservice = $this->getServiceLocator()->get('AuthService');
    	}
    	return $this->authservice;
    }

    public function getAuthStorage() {
        if (!$this->authStorage) {
            $this->authStorage = $this->getServiceLocator()->get('Application\Model\AuthStorage');
        }
        return $this->authStorage;
    }

    public function getOfficeService() {
        if (!$this->officeService) {
            $this->officeService = $this->getServiceLocator()->get('Core\Service\OfficeService');
        }
        return $this->officeService;
    }

    public function getOfficeStaffService() {
        if (!$this->officeStaffService) {
            $this->officeStaffService = $this->getServiceLocator()->get('Core\Service\OfficeStaffService');
        }
        return $this->officeStaffService;
    }

    public function getSessionCountry() {
        if (!$this->sessionCountry) {
            $this->sessionCountry = $this->getAuthStorage()->get('country');
        }
        return $this->sessionCountry;
    }

    public function getSessionUser() {
        if (!$this->sessionUser) {
            $this->sessionUser = $this->getAuthStorage()->getUser();
        }
        return $this->sessionUser;
    }

    public function getProfileService() {
        if (!$this->profileService) {
            $this->profileService = $this->getServiceLocator()->get('Core\Service\ProfileService');
        }
        return $this->profileService;
    }

    public function getUserService() {
        if (!$this->userService) {
            $this->userService = $this->getServiceLocator()->get('Core\Service\UserService');
        }
        return $this->userService;
    }

    public function getUserSettingService() {
    	if (! $this->userSettingService) {
    		$this->userSettingService = $this->getServiceLocator()->get('Core\Service\UserSettingService');
    	}
    	return $this->userSettingService;
    }

    public function getInviteService() {
        if (!$this->inviteService) {
            $this->inviteService = $this->getServiceLocator()->get('Core\Service\InviteService');
        }
        return $this->inviteService;
    }

    public function getSystemCountryStateService() {
        if (!$this->systemCountryStateService) {
            $this->systemCountryStateService = $this->getServiceLocator()->get('Core\Service\SystemCountryStateService');
        }
        return $this->systemCountryStateService;
    }

    public function getSystemCountryService() {
        if (!$this->systemCountryService) {
            $this->systemCountryService = $this->getServiceLocator()->get('Core\Service\SystemCountryService');
        }
        return $this->systemCountryService;
    }

    public function getSystemUsersStatusService() {
        if (!$this->systemUsersStatusService) {
            $this->systemUsersStatusService = $this->getServiceLocator()->get('Core\Service\SystemUsersStatusService');
        }
        return $this->systemUsersStatusService;
    }

    public function getSpecialtyService() {
        if (!$this->specialtyService) {
            $this->specialtyService = $this->getServiceLocator()->get('Core\Service\SpecialtyService');
        }
        return $this->specialtyService;
    }

    public function getSystemDocumentTypeService() {
    	if (! isset($this->systemDocumentTypeService)) {
    		$this->systemDocumentTypeService = $this->getServiceLocator()->get('Core\Service\SystemDocumentTypeService');
    	}
    	return $this->systemDocumentTypeService;
    }

    public function getNotificationService () {
    	if (! $this->notificationService) {
    		$this->notificationService = $this->getServiceLocator()->get('Core\Service\NotificationService');
    	}
    	return $this->notificationService;
    }

    public function getOfficeStaffWorkdayService() {
        if (! $this->officeStaffWorkdayService) {
            $this->officeStaffWorkdayService = $this->getServiceLocator()->get('Core\Service\OfficeStaffWorkdayService');
        }
        return $this->officeStaffWorkdayService;
    }

    public function getOfficeProviderService() {
        if (!$this->officeProviderService) {
            $this->officeProviderService = $this->getServiceLocator()->get('Core\Service\OfficeProviderService');
        }
        return $this->officeProviderService;
    }

    public function getOfficePayerService() {
    	if (!$this->officePayerService) {
    		$this->officePayerService = $this->getServiceLocator()->get('Core\Service\OfficePayerService');
    	}
    	return $this->officePayerService;
    }

    public function getSystemInsuranceService() {
        if (!$this->systemInsuranceService) {
            $this->systemInsuranceService = $this->getServiceLocator()->get('Core\Service\SystemInsuranceService');
        }
        return $this->systemInsuranceService;
    }

    public function getOfficeInsuranceService() {
        if (!$this->officeInsuranceService) {
            $this->officeInsuranceService = $this->getServiceLocator()->get('Core\Service\OfficeInsuranceService');
        }
        return $this->officeInsuranceService;
    }
    
    public function getOfficePatientService() {
    	if (!$this->officePatientService) {
    		$this->officePatientService = $this->getServiceLocator()->get('Core\Service\OfficePatientService');
    	}
    	return $this->officePatientService;
    }

    public function getImageService() {
        if (!$this->imageService) {
        	$this->imageService = $this->getServiceLocator()->get('Core\ImageService');
        }
        return $this->imageService;
    }

    public function getInsuranceService() {
        if (!$this->insuranceService) {
            $this->insuranceService = $this->getServiceLocator()->get('Core\Service\InsuranceService');
        }
        return $this->insuranceService;
    }

    public function getOfficeAppointmentService() {
        if (!$this->officeAppointmentService) {
            $this->officeAppointmentService = $this->getServiceLocator()->get('Core\Service\OfficeAppointmentService');
        }
        return $this->officeAppointmentService;
    }

    public function getSystemAppointmentStatusService() {
        if (!$this->systemAppointmentStatusService) {
            $this->systemAppointmentStatusService = $this->getServiceLocator()->get('Core\Service\SystemAppointmentStatusService');
        }
        return $this->systemAppointmentStatusService;
    }
    
    public function getOffice() {
        $office = $this->getAuthStorage()->getOffice();
        return $this->getOfficeService()->getRepository()->findOneById($office);
    }

    public function getSystemTimezoneService() {
        if (! isset($this->systemTimezoneService)) {
            $this->systemTimezoneService = $this->getServiceLocator()->get('Core\Service\SystemTimezoneService');
        }
        return $this->systemTimezoneService;
    }
    
    public function getMessageService() {
    	if (! isset($this->messageService)) {
    		$this->messageService = $this->getServiceLocator()->get('Core\Service\MessageService');
    	}
    	return $this->messageService;
    }
    
    public function getOfficeStaffPatientService() {
    	if (! isset($this->officeStaffPatientService)) {
    		$this->officeStaffPatientService = $this->getServiceLocator()->get('Core\Service\OfficeStaffPatientService');
    	}
    	return $this->officeStaffPatientService;
    }
    
    public function getEhrService() {
        if (! isset($this->ehrService)) {
            $this->ehrService = $this->getServiceLocator()->get('Core\Service\EhrService');
        }
        return $this->ehrService;
    }
    
    public function getEhrCommentService() {
        if (! isset($this->ehrCommentService)) {
            $this->ehrCommentService = $this->getServiceLocator()->get('Core\Service\EhrCommentService');
        }
        return $this->ehrCommentService;
    }
    
    public function getEhrDerivationService() {
        if (! isset($this->ehrDerivationService)) {
            $this->ehrDerivationService = $this->getServiceLocator()->get('Core\Service\EhrDerivationService');
        }
        return $this->ehrDerivationService;
    }
    
    public function getEhrDescriptionService() {
        if (! isset($this->ehrDescriptionService)) {
            $this->ehrDescriptionService = $this->getServiceLocator()->get('Core\Service\EhrDescriptionService');
        }
        return $this->ehrDescriptionService;
    }
    
    public function getEhrIndicationService() {
        if (! isset($this->ehrIndicationService)) {
            $this->ehrIndicationService = $this->getServiceLocator()->get('Core\Service\EhrIndicationService');
        }
        return $this->ehrIndicationService;
    }
    
    public function getEhrMedicamentService() {
        if (! isset($this->ehrMedicamentService)) {
            $this->ehrMedicamentService = $this->getServiceLocator()->get('Core\Service\EhrMedicamentService');
        }
        return $this->ehrMedicamentService;
    }
    
    public function getEhrPracticeService() {
        if (! isset($this->ehrPracticeService)) {
            $this->ehrPracticeService = $this->getServiceLocator()->get('Core\Service\EhrPracticeService');
        }
        return $this->ehrPracticeService;
    }
    
    public function getEhrProblemService() {
        if (! isset($this->ehrProblemService)) {
            $this->ehrProblemService = $this->getServiceLocator()->get('Core\Service\EhrProblemService');
        }
        return $this->ehrProblemService;
    }
    
    public function getEhrResultService() {
        if (! isset($this->ehrResultService)) {
            $this->ehrResultService = $this->getServiceLocator()->get('Core\Service\EhrResultService');
        }
        return $this->ehrResultService;
    }
    
    public function getEhrVitalSignService() {
        if (! isset($this->ehrVitalSignService)) {
            $this->ehrVitalSignService = $this->getServiceLocator()->get('Core\Service\EhrVitalSignService');
        }
        return $this->ehrVitalSignService;
    }
    
    public function getSystemProblemStatusService() {
        if (! isset($this->systemProblemStatusService)) {
            $this->systemProblemStatusService = $this->getServiceLocator()->get('Core\Service\SystemProblemStatusService');
        }
        return $this->systemProblemStatusService;
    }
    
    public function getSystemProblemService() {
        if (! isset($this->systemProblemService)) {
            $this->systemProblemService = $this->getServiceLocator()->get('Core\Service\SystemProblemService');
        }
        return $this->systemProblemService;
    }
    
    public function getSystemProblemTagService() {
        if (! isset($this->systemProblemTagService)) {
            $this->systemProblemTagService = $this->getServiceLocator()->get('Core\Service\SystemProblemTagService');
        }
        return $this->systemProblemTagService;
    }
    
    public function getSystemVitalSignService() {
        if (! isset($this->systemVitalSignService)) {
            $this->systemVitalSignService = $this->getServiceLocator()->get('Core\Service\SystemVitalSignService');
        }
        return $this->systemVitalSignService;
    }
    
    public function getSystemMedicamentService() {
        if (! isset($this->systemMedicamentService)) {
            $this->systemMedicamentService = $this->getServiceLocator()->get('Core\Service\SystemMedicamentService');
        }
        
        return $this->systemMedicamentService;
    }
    
    public function getSystemMedicamentPeriodicityService() {
        if (! isset($this->systemMedicamentPeriodicityService)) {
            $this->systemMedicamentPeriodicityService = $this->getServiceLocator()->get('Core\Service\SystemMedicamentPeriodicityService');
        }
        return $this->systemMedicamentPeriodicityService;
    }
    
    public function getSystemPracticeService() {
        if (! isset($this->systemPracticeService)) {
            $this->systemPracticeService = $this->getServiceLocator()->get('Core\Service\SystemPracticeService');
        }
        return $this->systemPracticeService;
    }
    
    public function getOfficeBillingService() {
        if (! isset($this->officeBillingService)) {
            $this->officeBillingService = $this->getServiceLocator()->get('Core\Service\OfficeBillingService');
        }
        return $this->officeBillingService;
    }

    public function getPatientVisitService() {
        if (! isset($this->patientVisitService)) {
            $this->patientVisitService = $this->getServiceLocator()->get('Core\Service\PatientVisitService');
        }
        return $this->patientVisitService;
    }    
    
    public function getPatientVisitMedicamentService() {
        if (! isset($this->patientVisitMedicamentService)) {
            $this->patientVisitMedicamentService = $this->getServiceLocator()->get('Core\Service\PatientVisitMedicamentService');
        }
        return $this->patientVisitMedicamentService;
    } 

    public function getReportService() {
        if (! isset($this->reportService)) {
            $this->reportService = $this->getServiceLocator()->get('Core\Service\ReportService');
        }
        return $this->reportService;
    }

    public function getNewsService() {
        if (! isset($this->newsService)) {
            $this->newsService = $this->getServiceLocator()->get('Core\Service\NewsService');
        }
        return $this->newsService;
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


    /**
     * getCurrentOfficeStaff
     *
     * @return mixed
     */
    public function getCurrentOfficeStaff()
    {
        $staffId = $this->getAuthStorage()->getStaff();
        return $this->getService('OfficeStaff')->findById($staffId);
    }

    protected function disableView()
    {
    	return $this->redirect()->toRoute('office', array(
    		'controller' => 'dashboard',
    	));
    }
    
}

?>
