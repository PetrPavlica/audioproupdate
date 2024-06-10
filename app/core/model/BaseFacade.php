<?php

namespace App\Core\Model;

use Kdyby\Doctrine\EntityManager;
use App\Core\Model\Database\Entity\Setting;
use Kdyby\Doctrine\EntityRepository;

abstract class BaseFacade
{

    /** @var array */
    protected $namespaceEntity;

    /** @var EntityManager */
    protected $em;

    /** Database entity */
    protected $entity;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, $entity = null)
    {
        $this->em = $em;
        $this->entity = $entity;
        $this->namespaceEntity = ['Intra\\Model\\Database\\Entity\\', 'App\\Core\\Model\\Database\\Entity\\'];
    }

    /**
     * Get em repository
     * @return EntityRepository
     */
    public function get()
    {
        return $this->em->getRepository($this->entity);
    }

    /**
     * Return entity class
     * @return string
     */
    public function entity()
    {
        return $this->entity;
    }

    /**
     * Save all entity object
     * @throws \Exception
     */
    public function save()
    {
        $this->em->flush();
    }

    /**
     * Remove entity
     * @throws \Exception
     */
    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    /**
     * Insert new entity object to db
     * @param object $entity
     * @throws \Exception
     */
    public function insertNew($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
        return $entity;
    }

    /**
     * Get EntityManager
     * @return EntityManager
     */
    public function getEm()
    {
        return $this->em;
    }

    /**
     * Get EntityRepository for specific entity
     * @param $entity
     * @return EntityRepository
     */
    public function gem($entity)
    {
        return $this->getEM()->getRepository($entity);
    }

    /**
     * Autonomous call or function. You can use as gEM<name>() where you need as name set database entity name
     * and you receive EM repository of this entity
     * @param string $name
     * @param $args
     * @return EntityRepository
     * @throws \Exception
     */
    public function __call($name, $args)
    {
        if (strlen($name) > 3) {
            $op = substr($name, 0, 3);
            $prop = $name[ 3 ] . substr($name, 4);
            if ($op === 'gEM') {
                foreach ($this->namespaceEntity as $namespace) {
                    $n = $namespace . $prop;
                    if (class_exists($n)) {
                        return $this->getEm()->getRepository($n);
                    }
                }
                throw new \Exception('Unknow database entity: ' . $prop);
            }
        }
    }

    /**
     * Get specific setting value by code
     * @param string $code
     * @return string value
     */
    public function setting($code)
    {
        return $this->getEm()->getRepository(Setting::class)->findOneBy(['codeSetting' => $code])->value;
    }

    /**
     * Get specific setting value by code
     * @param string $code
     * @return string value
     */
    public function settingEntity($code)
    {
        return $this->getEm()->getRepository(Setting::class)->findOneBy(['codeSetting' => $code]);
    }

    /**
     * Get all settings
     * @param string $code
     * @return array
     */
    public function getAllsettings()
    {
        $set = $this->getEm()->getRepository(Setting::class)->findAll();
        $arr = [];
        foreach ($set as $s) {
            $arr[ $s->codeSetting ] = $s->value;
        }
        return $arr;
    }

}
