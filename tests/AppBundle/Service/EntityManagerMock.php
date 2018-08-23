<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 23.08.2018
 * Time: 08:47
 */

namespace Tests\AppBundle\Service;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

/**
 * Class EntityManagerGetRepositorySetUp
 * @package Tests\AppBundle\Service
 */
class EntityManagerMock extends TestCase
{
    /** @var EntityManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $emMock;
    /** @var array | \PHPUnit_Framework_MockObject_MockObject*/
    protected $repositoriesMocks;

    /**
     * EntityManagerMock constructor.
     * @param array  $repositories
     * @param null   $name
     * @param array  $data
     * @param string $dataName
     *
     * $repositories should contains elements in the fallowing format:
     * 'some_key' => ''className'
     * Example: 'RoleRepository::class' => 'RoleRepository::class'
     */
    public function __construct($repositories, $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->generateRepositoriesMocks($repositories);
    }

    /**
     *
     */
    public function setUp()
    {
        $this->emMock = $this->createMock(EntityManager::class);
        $this->emMock->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback([$this, 'entityManagerCallback']));
    }

    /**
     * @return mixed
     *
     * @throws \Exception
     */
    public function entityManagerCallback()
    {
        $args = func_get_args();
        $className = reset($args);

        if (empty($this->repositoriesMocks[$className])) {
            throw new \Exception('No repository for this class: '.$className);
        }

        return $this->repositoriesMocks[$className];
    }

    private function generateRepositoriesMocks($repositories)
    {
        $this->repositoriesMocks = [];
        foreach ($repositories as $className => $repo) {
            $this->repositoriesMocks[$className] = $this->createMock($repo);
        }
    }
}
