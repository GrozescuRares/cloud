<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 30.08.2018
 * Time: 15:16
 */

namespace AppBundle\Dto;

/**
 * Class RoleDto
 * @package AppBundle\Dto
 */
class RoleDto
{
    public $description;

    /**
     * @return string
     */
    public function __toString()
    {
        $role = explode('_', $this->description);

        return ucfirst(strtolower($role[count($role) - 1]));
    }
}
