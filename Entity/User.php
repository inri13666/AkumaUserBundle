<?php
/**
 * User  : Nikita.Makarov
 * Date  : 2/3/15
 * Time  : 9:38 AM
 * E-Mail: nikita.makarov@effective-soft.com
 */

namespace Akuma\Bundle\UserBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\ORMException;
use FOS\UserBundle\Model\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
class User extends \FOS\UserBundle\Model\User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id",type="bigint",options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255)
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(name="username_canonical", type="string", length=255, unique=true)
     */
    protected $usernameCanonical;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="email_canonical", type="string", length=255, unique=true)
     */
    protected $emailCanonical;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    protected $enabled;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string")
     */
    protected $salt;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string")
     */
    protected $password;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */
    protected $lastLogin;

    /**
     * @var boolean
     *
     * @ORM\Column(name="locked", type="boolean")
     */
    protected $locked;

    /**
     * @var boolean
     *
     * @ORM\Column(name="expired", type="boolean")
     */
    protected $expired;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expires_at", type="datetime", nullable=true)
     */
    protected $expiresAt;

    /**
     * @var string
     *
     * @ORM\Column(name="confirmation_token", type="string", nullable=true)
     */
    protected $confirmationToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="password_requested_at", type="datetime", nullable=true)
     */
    protected $passwordRequestedAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="credentials_expired", type="boolean")
     */
    protected $credentialsExpired;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="credentials_expire_at", type="datetime", nullable=true)
     */
    protected $credentialsExpireAt;

    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="users", cascade={"persist"})
     * @ORM\JoinTable(name="user_roles",
     * joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     * inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="role", onDelete="CASCADE")}
     * )
     */
    protected $roles;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string
     */
    protected $plainPassword;


    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->enabled = false;
        $this->locked = false;
        $this->expired = false;
        $this->credentialsExpired = false;
    }

    /**
     * Serializes the user.
     *
     * The serialized data have to contain the fields used by the equals method and the username.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize(array(
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->expired,
            $this->locked,
            $this->credentialsExpired,
            $this->enabled,
            $this->id,
        ));
    }

    /**
     * Unserializes the user.
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        // add a few extra elements in the array to ensure that we have enough keys when unserializing
        // older data which does not include all properties.
        $data = array_merge($data, array_fill(0, 2, null));

        list(
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->expired,
            $this->locked,
            $this->credentialsExpired,
            $this->enabled,
            $this->id
            ) = $data;
    }

    /**
     * Removes sensitive data from the user.
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getUsernameCanonical()
    {
        return $this->usernameCanonical;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getEmailCanonical()
    {
        return $this->emailCanonical;
    }

    /**
     * Gets the encrypted password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Gets the last login time.
     *
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    public function isAccountNonExpired()
    {
        if (true === $this->expired) {
            return false;
        }

        if (null !== $this->expiresAt && $this->expiresAt->getTimestamp() < time()) {
            return false;
        }

        return true;
    }

    public function isAccountNonLocked()
    {
        return !$this->locked;
    }

    public function isCredentialsNonExpired()
    {
        if (true === $this->credentialsExpired) {
            return false;
        }

        if (null !== $this->credentialsExpireAt && $this->credentialsExpireAt->getTimestamp() < time()) {
            return false;
        }

        return true;
    }

    public function isCredentialsExpired()
    {
        return !$this->isCredentialsNonExpired();
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function isExpired()
    {
        return !$this->isAccountNonExpired();
    }

    public function isLocked()
    {
        return !$this->isAccountNonLocked();
    }

    public function isSuperAdmin()
    {
        return $this->hasRole(static::ROLE_SUPER_ADMIN);
    }

    public function isUser(UserInterface $user = null)
    {
        return null !== $user && $this->getId() === $user->getId();
    }

    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    public function setUsernameCanonical($usernameCanonical)
    {
        $this->usernameCanonical = $usernameCanonical;

        return $this;
    }

    /**
     * @param \DateTime $date
     *
     * @return User
     */
    public function setCredentialsExpireAt(\DateTime $date)
    {
        $this->credentialsExpireAt = $date;

        return $this;
    }

    /**
     * @param boolean $boolean
     *
     * @return User
     */
    public function setCredentialsExpired($boolean)
    {
        $this->credentialsExpired = $boolean;

        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function setEmailCanonical($emailCanonical)
    {
        $this->emailCanonical = $emailCanonical;

        return $this;
    }

    public function setEnabled($boolean)
    {
        $this->enabled = (Boolean)$boolean;

        return $this;
    }

    /**
     * Sets this user to expired.
     *
     * @param Boolean $boolean
     *
     * @return User
     */
    public function setExpired($boolean)
    {
        $this->expired = (Boolean)$boolean;

        return $this;
    }

    /**
     * @param \DateTime $date
     *
     * @return User
     */
    public function setExpiresAt(\DateTime $date)
    {
        $this->expiresAt = $date;

        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;

        return $this;
    }

    public function setLastLogin(\DateTime $time)
    {
        $this->lastLogin = $time;

        return $this;
    }

    public function setLocked($boolean)
    {
        $this->locked = $boolean;

        return $this;
    }

    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function setPasswordRequestedAt(\DateTime $date = null)
    {
        $this->passwordRequestedAt = $date;

        return $this;
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return null|\DateTime
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    public function isPasswordRequestNonExpired($ttl)
    {
        return $this->getPasswordRequestedAt() instanceof \DateTime &&
        $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    /**
     * Gets the plain password.
     *
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param string $role
     *
     * @return $this|UserInterface
     */
    public function removeRole($role)
    {
        $roleElement = $this->getRole($role);
        if ($roleElement) {
            $this->roles->removeElement($roleElement);
        }
    }

    /**
     * Pass a string, get the desired Role object or null.
     *
     * @param string $role
     *
     * @return Role|null
     */
    public function getRole($role)
    {
        foreach ($this->getRoles() as $roleItem) {
            if ($role == $roleItem->getRole()) {
                return $roleItem;
            }
        }
        return null;
    }

    /**
     * Returns the user roles
     *
     * @return array The roles
     */
    public function getRoles()
    {
        return $this->roles->toArray();
    }

    /**
     * Adds a Role OBJECT to the ArrayCollection. Can't type hint due to interface so throws Exception.
     *
     * @throws \Exception
     *
     * @param Role $role
     *
     * @return \FOS\UserBundle\Model\UserInterface|void
     */
    public function addRole($role)
    {
        if (!$role instanceof Role) {
            //throw new \Exception( "addRole takes a Role object as the parameter" );
            $_role = new Role($role);
        } else {
            $_role = $role;
        }

        if (!$this->hasRole($_role->getRole())) {
            $_role->addUser($this);
            $this->roles->add($_role);
        }
    }

    public function setRoles(array $roles)
    {
        $this->roles->clear();
        foreach ($roles as $role) {
            $this->addRole($role);
        }
        return $this;
    }

    public function setSuperAdmin($boolean)
    {
        if (true === $boolean) {
            $this->addRole(static::ROLE_SUPER_ADMIN);
        } else {
            $this->removeRole(static::ROLE_SUPER_ADMIN);
        }
        return $this;
    }

    /**
     * Never use this to check if this user has access to anything!
     *
     * Use the SecurityContext, or an implementation of AccessDecisionManager
     * instead, e.g.
     *
     *         $securityContext->isGranted('ROLE_USER');
     *
     * @param string $role
     *
     * @return boolean
     */
    public function hasRole($role)
    {
        $roles = $this->getRoles();
        /** @var Role $roleObject */
        foreach ($roles as $roleObject) {
            if ($roleObject->getRole() == strtoupper($role)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @ORM\PrePersist()
     *
     * @param LifecycleEventArgs $args
     */
    public function persistRoles(LifecycleEventArgs $args)
    {
        $roles = $args->getObject()->getRoles();

        $meta = $args->getObjectManager()->getClassMetadata(get_class($args->getObject()));
        $roleClass = $meta->getAssociationTargetClass('roles');

        $has_default_role = false;
        /** @var EntityManager $em */
        $em = $args->getObjectManager();

        foreach ($roles as $k => $role) {
            try {
                $role_id = $role->getRole();
                if ($role_id === static::ROLE_DEFAULT) {
                    $has_default_role = true;
                }

                if ($em->find($roleClass, $role_id)) {
                    $_ref = $em->getReference($roleClass, $role_id);
                    if ($_ref) {
                        $roles[$k] = $_ref;
                    }
                }
            } catch (ORMException $e) {
                continue;
            }
        }
        if (!$has_default_role) {
            if ($em->find($roleClass, static::ROLE_DEFAULT)) {
                $_ref = $em->getReference($roleClass, static::ROLE_DEFAULT);
                if ($_ref) {
                    $roles[] = $_ref;
                }
            } else {
                $roles[] = new $roleClass(static::ROLE_DEFAULT);
            }
        }

        $args->getObject()->setRoles($roles);
    }

    /**
     * @ORM\PreUpdate()
     *
     * @param PreUpdateEventArgs $args
     */
    public function updateRoles(PreUpdateEventArgs $args)
    {

        if ($args->hasChangedField('roles')) {
            $roles = $args->getObject()->getRoles();

            $meta = $args->getObjectManager()->getClassMetadata(get_class($args->getObject()));
            $roleClass = $meta->getAssociationTargetClass('roles');

            $has_default_role = false;
            /** @var EntityManager $em */
            $em = $args->getObjectManager();

            foreach ($roles as $k => $role) {
                try {
                    $role_id = $role->getRole();
                    if ($role_id === static::ROLE_DEFAULT) {
                        $has_default_role = true;
                    }

                    if ($em->find($roleClass, $role_id)) {
                        $_ref = $em->getReference($roleClass, $role_id);
                        if ($_ref) {
                            $roles[$k] = $_ref;
                        }
                    }
                } catch (ORMException $e) {
                    continue;
                }
            }

            if (!$has_default_role) {
                if ($em->find($roleClass, static::ROLE_DEFAULT)) {
                    $_ref = $em->getReference($roleClass, static::ROLE_DEFAULT);
                    if ($_ref) {
                        $roles[] = $_ref;
                    }
                } else {
                    $roles[] = new $roleClass(static::ROLE_DEFAULT);
                }
            }

            $args->setNewValue('roles', $roles);
        }
    }
}