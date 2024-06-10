<?php

namespace App\Core\Model\Facade;

use App\Core\Model\BaseFacade;
use Kdyby\Doctrine\EntityManager;
use App\Core\Model\Database\Entity\PermisionRule;
use App\Core\Model\Database\Entity\PermisionGroup;
use App\Core\Model\Database\Entity\PermisionItem;

class PermisionRuleFacade extends BaseFacade {

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) {
        parent::__construct($em);
    }

    /**
     * Get em repository
     * @return Repository of PermisionRule
     */
    public function get() {
        return $this->em->getRepository(PermisionRule::class);
    }

    /**
     * Return entity class
     * @return string
     */
    public function entity() {
        return PermisionRule::class;
    }

    /**
     * Find and return entity PermisionGroup
     * @param integer $id
     * @return PermisionGroup
     */
    public function getGroup($id) {
        return $this->em->getRepository(PermisionGroup::class)->find($id);
    }

    /**
     * Insert / Update or Delete PermisionRule
     * @param type $ruleName
     * @param type $ruleType
     * @param type $group
     */
    public function insertUpdateRuleByGroup($ruleName, $ruleType, $group) {
        $rule = $this->get()->findOneBy([
            'item' => $ruleName,
            'group' => $group->id
        ]);

        if (count($rule)) { /* Check if rule exist */
            if ($ruleType) {
                $rule->setAction($ruleType);
            } else { /* if item type is NULL - remove this rule */
                $this->em->remove($rule);
            }
        } else { /* if item not exist - create new */
            if ($ruleType) {
                $item = $this->em->getRepository(PermisionItem::class)->findOneBy(['name' => $ruleName]);
                /* in inserted item is menu - you must insert read rule for presenter */
                if ($item->type == PermisionItem::TYPE_MENU) {
                    $presenterName = explode('__', $ruleName)[0];
                    $presenterRule = new PermisionRule();
                    $presenterRule->setGroup($group);
                    $presenterRule->setItem($presenterName);
                    $presenterRule->setAction(PermisionRule::ACTION_ALL);
                    $this->em->persist($presenterRule);
                }
                $rule = new PermisionRule();
                $rule->setGroup($group);
                $rule->setItem($ruleName);
                $rule->setAction($ruleType);
                $this->em->persist($rule);
            }
        }
        $this->em->flush();
    }

    /**
     * Get permision Array for autorization
     * @param integer $groupId
     * @return array
     */
    public function getPermisionArray($groupId) {
        $permision = $this->get()->findBy(['group' => $groupId]);
        $permisionArr = [];
        if (count($permision)) {
            foreach ($permision as $item) {
                $permisionArr[$item->item] = $item->action;
            }
        }
        return $permisionArr;
    }

}
