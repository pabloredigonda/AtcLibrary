<?php
namespace Core\Model;

use Doctrine\ORM\Mapping as ORM;
// use JMS\Serializer\Annotation as Serializer;

/**
 * AdminUser
 *
 * @ORM\Table(name="admin_users", uniqueConstraints={@ORM\UniqueConstraint(name="users_admin_email_key", columns={"email"})})
 * @ORM\Entity(repositoryClass="Core\Model\Repository\AdminUserRepository")
 */
class AdminUser extends AbstractModel
{

    /**
     *
     * @var integer @ORM\Column(name="user_id", type="integer", nullable=false)
     *      @ORM\Id
     *      @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     *
     * @var string @ORM\Column(name="email", type="string", length=100, nullable=false)
     */
    protected $email;

    /**
     *
     * @var string @ORM\Column(name="password", type="string", length=100, nullable=false)
     */
    protected $password;

    /**
     *
     * @var string @ORM\Column(name="first_name", type="string", length=100, nullable=true)
     */
    protected $firstName;

    /**
     *
     * @var string @ORM\Column(name="last_name", type="string", length=100, nullable=true)
     */
    protected $lastName;

    /**
     *
     * @var \DateTime @ORM\Column(name="date_created", type="datetimetz", nullable=true)
     */
    protected $dateCreated = 'now()';

    public function __construct()
    {
        $this->dateCreated = new \DateTime('now');
    }

    /**
     * getDisplayName
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("displayName")
     * 
     * @return mixed
     */
    public function getDisplayName()
    {
        return $this->getFirstName();
    }

    /**
     *
     * @return the $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @return the $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     *
     * @return the $password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     *
     * @return the $firstName
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     *
     * @return the $lastName
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     *
     * @return the $lastName
     */
    public function getFullName()
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    /**
     *
     * @return the $dateCreated
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     *
     * @param number $id            
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     *
     * @param string $email            
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     *
     * @param string $password            
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     *
     * @param string $firstName            
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     *
     * @param string $lastName            
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     *
     * @param DateTime $dateCreated            
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }
}
