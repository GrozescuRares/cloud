<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Role
 *
 * @ORM\Table(name="roles")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RoleRepository")
 */
class Role
{
    /**
     * @var int
     *
     * @ORM\Column(name="roleId", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $roleId;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;


    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="role")
     */
    private $users;

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param mixed $users
     *
     * @return $this
     */
    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @return int
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     * @param int $roleId
     *
     * @return $this
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;

        return $this;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Role
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $role = explode('_', $this->description);

        return ucfirst(strtolower($role[count($role)-1]));
    }
}
