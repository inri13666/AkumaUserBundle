<?php
/**
 * User  : Nikita.Makarov
 * Date  : 2/3/15
 * Time  : 9:41 AM
 * E-Mail: nikita.makarov@effective-soft.com
 */

namespace Akuma\Bundle\UserBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="role")
 *
 * @ORM\HasLifecycleCallbacks()
 */
class Role implements RoleInterface
{

    /**
     * @ORM\Id
     * @ORM\Column(name="role", type="string", length=20, unique=true)
     *
     * @var string
     */
    private $role;

    /**
     * @ORM\Column(name="name", type="string", length=30)
     *
     * @var string
     */
    private $name;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="User", mappedBy="roles")
     */
    private $users;

    public function __construct($role)
    {
        $this->users = new ArrayCollection();

        $this->role = $role;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * Returns the role.
     *
     * This method returns a string representation whenever possible.
     *
     * When the role cannot be represented with sufficient precision by a
     * string, it should return null.
     *
     * @return string|null A string representation of the role, or null
     */
    public function getRole()
    {
        return strtoupper($this->role);
    }

    public function setUsers($users)
    {
        $this->users->clear();
        foreach ($users as $user) {
            if ($user instanceof User) {
                $this->addUser($user);
            }
        }
    }

    public function getUsers()
    {
        return $this->users->toArray();
    }

    public function hasUser(User $user)
    {
        return $this->users->contains($user);
    }

    public function addUser(User $user)
    {
        if (!$this->hasUser($user)) {
            $this->users->add($user);
        }
    }
}