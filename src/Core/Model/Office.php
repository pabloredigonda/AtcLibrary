<?php
namespace Core\Model;

use Doctrine\ORM\Mapping as ORM;
use Core\Util\Constants;
// use JMS\Serializer\Annotation\Exclude;
// use JMS\Serializer\Annotation\MaxDepth;
// use JMS\Serializer\Annotation\VirtualProperty;
// use JMS\Serializer\Annotation\SerializedName;
// use JMS\Serializer\Annotation as Serializer;

/**
 * Office
 *
 * @ORM\Table(name="office", indexes={@ORM\Index(name="IDX_74516B0281B2B6EE", columns={"address_country_id"}), @ORM\Index(name="IDX_74516B02FB57C240", columns={"address_state_id"}), @ORM\Index(name="IDX_74516B02A76ED395", columns={"user_id"})})
 * @ORM\Entity(repositoryClass="Core\Model\Repository\OfficeRepository")
 */
class Office extends AbstractModel
{
    /**
     * @var integer
     *
     * @ORM\Column(name="office_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     */
    protected $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetimetz", nullable=true)
     */
    protected $createdDate;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=true)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="legal_number", type="string", length=50, nullable=true)
     */
    protected $legalNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="legal_name", type="string", length=256, nullable=true)
     */
    protected $legalName;
    
    /**
     * @var string
     *
     * @ORM\Column(name="address_street1", type="string", length=100, nullable=true)
     */
    protected $addressStreet1;

    /**
     * @var string
     *
     * @ORM\Column(name="address_street2", type="string", length=100, nullable=true)
     */
    protected $addressStreet2;

    /**
     * @var string
     *
     * @ORM\Column(name="address_street_number", type="string", length=50, nullable=true)
     */
    protected $addressStreetNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="address_street_floor", type="string", length=50, nullable=true)
     */
    protected $addressStreetFloor;

    /**
     * @var string
     *
     * @ORM\Column(name="address_street_dept", type="string", length=50, nullable=true)
     */
    protected $addressStreetDept;
    
    /**
     * @var string
     *
     * @ORM\Column(name="address_city", type="string", length=100, nullable=true)
     */
    protected $addressCity;

    /**
     * @var string
     *
     * @ORM\Column(name="address_postal_code", type="string", length=25, nullable=true)
     */
    protected $addressPostalCode;

    /**
     * @var bigint
     *
     * @ORM\Column(name="address_phone_prefix", type="bigint", nullable=false)
     */
    protected $addressPhonePrefix;

    /**
     * @var bigint
     *
     * @ORM\Column(name="address_phone_number", type="bigint", nullable=false)
     */
    protected $addressPhoneNumber;

    /**
     * @var bigint
     *
     * @ORM\Column(name="address_fax_prefix", type="bigint", nullable=true)
     */
    protected $addressFaxPrefix;
    
    /**
     * @var bigint
     *
     * @ORM\Column(name="address_fax", type="bigint", nullable=true)
     */
    protected $addressFax;

    /**
     * @var \SystemCountry
     *
     * @ORM\ManyToOne(targetEntity="SystemCountry")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="address_country_id", referencedColumnName="country_id")
     * })
     */
    protected $addressCountry;

    /**
     * @var \SystemCountryState
     *
     * @ORM\ManyToOne(targetEntity="SystemCountryState")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="address_state_id", referencedColumnName="state_id")
     * })
     */
    protected $addressState;

    /**
     * @var \Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     * })
     */
    protected $user;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Specialty", inversedBy="office")
     * @ORM\JoinTable(name="office_specialty",
     *   joinColumns={
     *     @ORM\JoinColumn(name="office_id", referencedColumnName="office_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="specialty_id", referencedColumnName="specialty_id")
     *   }
     * )
     */
    protected $specialty;

    /**
     * @var string
     *
     * @ORM\Column(name="timezone", type="string", length=100, nullable=true)
     */
    protected $timezone;
    
    /**
     * @var \OfficePin
     *
     * @ORM\ManyToOne(targetEntity="OfficePin")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="office_pin_id", referencedColumnName="office_pin_id")
     * })
     * @Exclude
     */
    protected $officePin;
    
    /**
     * @ORM\OneToMany(targetEntity="OfficeStaff", mappedBy="office")
     * @Exclude
     */
    protected $staff;
    
    /**
     * Constructor
     */
    
    public function __construct()
    {
    	$this->createdDate = new \DateTime('now');
    	
        $this->specialty = new \Doctrine\Common\Collections\ArrayCollection();
        $this->staff = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * toString
     */
    public function __toString() {
        return (string)$this->getId();
    }
    
	/**
	 * @return the $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return the $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return the $createdDate
	 */
	public function getCreatedDate() {
		return $this->createdDate;
	}

	/**
	 * @return the $email
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @return the $legalNumber
	 */
	public function getLegalNumber() {
		return $this->legalNumber;
	}
	
	/**
	 * @return the $legalName
	 */
	public function getLegalName() {
		return $this->legalName;
	}
	
		

	/**
	 * @return the $addressStreet1
	 */
	public function getAddressStreet1() {
		return $this->addressStreet1;
	}

	/**
	 * @return the $addressStreet2
	 */
	public function getAddressStreet2() {
		return $this->addressStreet2;
	}

	/**
	 * @return the $addressStreetNumber
	 */
	public function getAddressStreetNumber() {
		return $this->addressStreetNumber;
	}
	
	/**
	 * @return the $addressStreetFloor
	 */
	public function getAddressStreetFloor() {
		return $this->addressStreetFloor;
	}

	/**
	 * @return the $addressStreetDept
	 */
	public function getAddressStreetDept() {
		return $this->addressStreetDept;
	}
	
	/**
	 * @return the $addressCity
	 */
	public function getAddressCity() {
		return $this->addressCity;
	}

	/**
	 * @return the $addressPostalCode
	 */
	public function getAddressPostalCode() {
		return $this->addressPostalCode;
	}

	/**
	 * @return the $addressPhonePrefix
	 */
	public function getAddressPhonePrefix() {
		return $this->addressPhonePrefix;
	}

	/**
	 * @return the $addressPhoneNumber
	 */
	public function getAddressPhoneNumber() {
		return $this->addressPhoneNumber;
	}
	
    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("phone")
     */
	public function getPhone() {
	    return trim($this->addressPhonePrefix.' '.$this->addressPhoneNumber);
	}

	/**
	 * @return the $addressFax
	 */
	public function getAddressFaxPrefix() {
		return $this->addressFaxPrefix;
	}
	
	/**
	 * @return the $addressFax
	 */
	public function getAddressFax() {
		return $this->addressFax;
	}

	/**
	 * @return the $addressCountry
	 */
	public function getAddressCountry() {
		return $this->addressCountry;
	}

	/**
	 * @return the $addressState
	 */
	public function getAddressState() {
		return $this->addressState;
	}

	/**
	 * @return the $user
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @return the $specialty
	 */
	public function getSpecialty() {
		return $this->specialty;
	}

	/**
	 * @return the $timezone
	 */
	public function getTimezone() {
	    return $this->timezone;
	}

	/**
	 * @return the $timezone Object
	 */
	public function getTimezoneObject() {
	    return new \DateTimeZone($this->timezone ? $this->timezone : Constants::DEFAULT_TIMEZONE);
	}
	
    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("address")
     */
	public function getAddress() {
	    $address = $this->addressStreet1;
	    $address .= $this->addressStreet2 ? ' '.$this->addressStreet2 : '';
	    $address .= $this->addressStreetNumber ? ' '.$this->addressStreetNumber : '';
	    $address .= $address ? ', ' : '';
	    $address .= $this->addressCity ? ' '.$this->addressCity : '';
	    $address .= $address ? ', ' : '';
	    $address .= $this->addressState ? ' '.$this->addressState->getName() : '';
	    return trim($address);
	}
	
	/**
	 * @return the $officePin
	 */
	public function getOfficePin() {
	    return $this->officePin;
	}
	
	/**
	 * @param number $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @param DateTime $createdDate
	 */
	public function setCreatedDate($createdDate) {
		$this->createdDate = $createdDate;
	}

	/**
	 * @param string $email
	 */
	public function setEmail($email) {
		$this->email = $email;
	}

	/**
	 * @param string $legalNumber
	 */
	public function setLegalNumber($legalNumber) {
		$this->legalNumber = $legalNumber;
	}

	/**
	 * @param string $legalName
	 */
	public function setLegalName($legalName) {
		$this->legalName = $legalName;
	}
	
	/**
	 * @param string $addressStreet1
	 */
	public function setAddressStreet1($addressStreet1) {
		$this->addressStreet1 = $addressStreet1;
	}

	/**
	 * @param string $addressStreet2
	 */
	public function setAddressStreet2($addressStreet2) {
		$this->addressStreet2 = $addressStreet2;
	}

	/**
	 * @param string $addressStreetNumber
	 */
	public function setAddressStreetNumber($addressStreetNumber) {
		$this->addressStreetNumber = $addressStreetNumber;
	}

	/**
	 * @param string $addressStreetDept
	 */
	public function setAddressStreetDept($addressStreetDept) {
		$this->addressStreetDept = $addressStreetDept;
	}
	
	/**
	 * @param string $addressStreetFloor
	 */
	public function setAddressStreetFloor($addressStreetFloor) {
		$this->addressStreetFloor = $addressStreetFloor;
	}

	/**
	 * @param string $addressCity
	 */
	public function setAddressCity($addressCity) {
		$this->addressCity = $addressCity;
	}

	/**
	 * @param string $addressPostalCode
	 */
	public function setAddressPostalCode($addressPostalCode) {
		$this->addressPostalCode = $addressPostalCode;
	}

	/**
	 * @param number $addressPhonePrefix
	 */
	public function setAddressPhonePrefix($addressPhonePrefix) {
		$this->addressPhonePrefix = $addressPhonePrefix;
	}

	/**
	 * @param number $addressPhoneNumber
	 */
	public function setAddressPhoneNumber($addressPhoneNumber) {
		$this->addressPhoneNumber = $addressPhoneNumber;
	}

	/**
	 * @param number $addressFaxPrefix
	 */
	public function setAddressFaxPrefix($addressFaxPrefix) {
		$this->addressFaxPrefix = $addressFaxPrefix;
	}
	
	/**
	 * @param number $addressFax
	 */
	public function setAddressFax($addressFax) {
		$this->addressFax = $addressFax;
	}

	/**
	 * @param SystemCountry $addressCountry
	 */
	public function setAddressCountry($addressCountry) {
		$this->addressCountry = $addressCountry;
	}

	/**
	 * @param SystemCountryState $addressState
	 */
	public function setAddressState($addressState) {
		$this->addressState = $addressState;
	}

	/**
	 * @param Users $user
	 */
	public function setUser($user) {
		$this->user = $user;
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $specialty
	 */
	public function setSpecialty($specialty) {
		$this->specialty = $specialty;
	}

	/**
     * @param string $timezone
     */
    public function setTimezone($timezone) {
        $this->timezone = $timezone;
    }
    
    /**
     * @param string $officePin
     */
    public function setOfficePin($officePin) {
        $this->officePin = $officePin;
    }
}
