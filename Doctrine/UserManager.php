<?php
/**
 * User  : Nikita Makarov
 * Date  : 1/5/16
 * E-Mail: mesaverde228@gmail.com
 *
 * @file
 * Description
 */

namespace Akuma\Bundle\UserBundle\Doctrine;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use FOS\UserBundle\Model\UserInterface;

class UserManager extends \FOS\UserBundle\Doctrine\UserManager
{
    /**
     * Updates a user.
     *
     * @param UserInterface $user
     * @param Boolean       $andFlush Whether to flush the changes (default true)
     */
    public function updateUser(UserInterface $user, $andFlush = true)
    {
        /** @var EntityManager $em */
        $em = $this->objectManager;
        $meta = $em->getClassMetadata(get_class($user));
        $roleClass = $meta->getAssociationTargetClass('roles');
        $roles = array();
        foreach ($user->getRoles() as $role) {
            if ($roleClass !== get_class($role)) {
                /** Try to get Role */
                if ($em->find($roleClass, $role->getRole())) {
                    try {
                        $_ref = $em->getReference($roleClass, $role->getRole());
                        $roles[] = $_ref;
                    } catch (ORMException $e) {
                        var_dump($e);
                    }
                }else{
                    $_ref = new $roleClass($role->getRole());
                    $em->persist($_ref);
                    $roles[] = $_ref;
                }
            }else{
                $roles[] = $role;
            }
        }
        $user->setRoles($roles);
        parent::updateUser($user, $andFlush);
    }
} 