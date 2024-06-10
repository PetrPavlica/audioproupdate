<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Intra\Model\Database\Entity\WebPushSubscription;
use Kdyby\Doctrine\EntityManager;
use Nette\Security\User;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class WebPushSubscriptionFacade extends BaseFacade
{
    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, WebPushSubscription::class);
    }

    /**
     * @param $values
     * @param $user User
     * @return WebPushSubscription|null
     */
    public function updateSubscription($values, $user)
    {
        try {
            $subscription = $this->get()->findOneBy(['endpoint' => $values['endpoint']]);
            if ($subscription) {
                $subscription->setToken($values['token']);
                $subscription->setKey($values['key']);
            } else {
                $subscription = new WebPushSubscription();
                $subscription->setEndpoint($values['endpoint']);
                $subscription->setKey($values['key']);
                $subscription->setToken($values['token']);
                $this->getEm()->persist($subscription);
            }
            if ($user->isInRole('visitor')) {
                $subscription->setCustomer($this->gEMCustomer()->find($user->id));
            }
            $subscription->setUpdated(new DateTime());
            $this->save();

            return $subscription;
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        return null;
    }

    public function deleteSubscription($values)
    {
        $subscription = $this->get()->findOneBy(['endpoint' => $values['endpoint']]);
        if ($subscription) {
            $this->remove($subscription);
        }
    }
}