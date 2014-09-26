<?php
namespace Core\Model;

use Doctrine\ORM\Mapping as ORM;
// use JMS\Serializer\Annotation\Exclude;
// use JMS\Serializer\Annotation as Serializer;

/**
 * User
 *
 * @ORM\Table(name="users", uniqueConstraints={@ORM\UniqueConstraint(name="users_email_key", columns={"email"})}})
 * @ORM\Entity(repositoryClass="Core\Model\Repository\UserRepository")
 */
class User extends AbstractModel
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
     *      @Exclude
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
     * @return the $type
     */
    public function getType()
    {
        return $this->type;
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
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("fullName")
     *
     * @return string
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
