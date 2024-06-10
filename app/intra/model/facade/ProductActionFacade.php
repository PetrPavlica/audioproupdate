<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Kdyby\Doctrine\EntityManager;
use Intra\Model\Database\Entity\ProductAction;
use Nette\Caching\IStorage;
use Nette\Caching\Cache;

class ProductActionFacade extends BaseFacade
{

    /** @var IStorage */
    protected $storage;

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, IStorage $storage)
    {
        parent::__construct($em, ProductAction::class);
        $this->storage = $storage;
    }

    public function activationAction($action)
    {
        if (is_numeric($action)) {
            $action = $this->get()->findOneBy(['id' => $action]);
        }
        $action = $this->get()->findOneBy([
            'id' => $action->id,
            'dateForm <=' => new \DateTime(),
            'dateTo >=' => new \DateTime()
        ]);
        if (!count($action)) {
            return false;
        }
        $product = $action->product;
        $product->setActiveAction($action);

        $actions = $this->get()->findBy([
            'active' => 1,
            'product' => $product->id,
            'id !=' => $action->id,
            'currency' => $action->currency,
            'isTypeOfPrice' => 0,
            'special' => 0
        ]);
        foreach ($actions as $item) {
            $item->setActive(false);
        }
        $action->setActive(true);
        $this->save();

        for($i = 0; $i < 10; $i++) {
            $this->storage->clean([
                Cache::TAGS => ["product-" . $action->product->id]
            ]);
        }

        return $product;
    }

    public function stopAction($action)
    {
        if (is_numeric($action)) {
            $action = $this->get()->findOneBy(['id' => $action]);
        }
        if (!count($action)) {
            return false;
        }
        $product = $action->product;
        if ($product->activeAction && $product->activeAction->id == $action->id) {
            $product->setActiveAction(null);
        }
        $action->setActive(false);
        $this->save();

        for($i = 0; $i < 10; $i++) {
            $this->storage->clean([
                Cache::TAGS => ["product-" . $action->product->id]
            ]);
        }

        return $product;
    }

    public function deleteAction($action)
    {
        if (is_numeric($action)) {
            $action = $this->get()->find($action);
        }
        if ($action) {
            if ($action->active && $action->isTypeOfPrice == 0 && $action->special == 0) {
                return false;
            }
            $this->remove($action);
            for ($i = 0; $i < 10; $i++) {
                $this->storage->clean([
                    Cache::TAGS => ["product-" . $action->product->id]
                ]);
            }
            return true;
        }

        return false;
    }

    public function updateAllActions()
    {
        // Prvně deaktivuji všechny, které jsou mimo datumovou range
        $actionsFrom = $this->get()->findBy(['dateForm >' => new \DateTime(), 'active' => 1, 'isTypeOfPrice' => 0]);
        $actionsTo = $this->get()->findBy(['dateTo <' => new \DateTime(), 'active' => 1, 'isTypeOfPrice' => 0]);

        foreach ($actionsFrom as $action) {
            $this->stopAction($action);
        }
        foreach ($actionsTo as $action) {
            $this->stopAction($action);
        }

        // Nyní spustím ty co by měly běžet dle času
        $actions = $this->get()->findBy(['dateForm <=' => new \DateTime(), 'dateTo >=' => new \DateTime()]);
        foreach ($actions as $action) {
            $this->activationAction($action);
        }
    }

}
