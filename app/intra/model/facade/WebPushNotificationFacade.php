<?php

namespace Intra\Model\Facade;

use App\Core\Model\BaseFacade;
use Intra\Model\Database\Entity\WebPushNotification;
use Intra\Model\Database\Entity\WebPushNotificationFront;
use Kdyby\Doctrine\EntityManager;
use Minishlink\WebPush\WebPush;
use Nette\Application\LinkGenerator;
use Nette\Utils\Strings;

class WebPushNotificationFacade extends BaseFacade
{
    /** @var LinkGenerator */
    public $linkGenerator;

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, LinkGenerator $linkGenerator)
    {
        parent::__construct($em, WebPushNotification::class);
        $this->linkGenerator = $linkGenerator;
    }

    public function saveImage($path, $notification)
    {
        $notification->setImage($path);
        $this->save();
    }

    public function sendNotification($notification)
    {
        if (is_numeric($notification)) {
            $notification = $this->get()->find($notification);
        }

        if ($notification) {
            $notifications = [];

            $subscriptions = $this->gEMWebPushSubscription()->findAll();
            if ($subscriptions) {
                foreach ($subscriptions as $s) {
                    $notifications[] = [
                        'endpoint' => $s->endpoint,
                        'key' => $s->key,
                        'token' => $s->token
                    ];
                }
            }

            $payload = [
                'body' => $notification->body,
                'title' => $notification->name,
                'icon' => 'front/design/favicon/favicon-96x96.png',
                'image' => $notification->image,
                'url' => $notification->link
            ];

            if ($notification->product) {
                $slug = Strings::webalize($notification->product->name);
                $payload['url'] = $this->linkGenerator->link('ProductDetail:detail', [
                    'id' => $notification->product->id,
                    'slug' => $slug,
                    'utm_source' => 'web-push',
                    'utm_medium' => 'notification',
                    'utm_campaign' => Strings::webalize($notification->name)
                ]);
                if (isset($notification->product->images[0]->path)) {
                    $payload['image'] = $notification->product->images[0]->path;
                }
            } else {
                $query = parse_url($payload['url'], PHP_URL_QUERY);

                if ($query) {
                    $payload['url'] .= '&utm_source=web-push&utm_medium=notification&utm_campaign='.Strings::webalize($notification->name);
                } else {
                    $payload['url'] .= '?utm_source=web-push&utm_medium=notification&utm_campaign='.Strings::webalize($notification->name);
                }
            }

            $auth = [
                'VAPID' => [
                    'subject' => 'mailto:marek@webrex.cz',
                    'publicKey' => file_get_contents('../keys/public_key.txt'),
                    'privateKey' => file_get_contents('../keys/private_key.txt'),
                ],
            ];

            $webPush = new WebPush($auth);

            foreach ($notifications as $n) {
                $webPush->sendNotification(
                    $n['endpoint'],
                    json_encode($payload),
                    $n['key'],
                    $n['token']
                );
            }

            $flush = $webPush->flush();
            if ($flush !== true) {
                foreach ($flush as $report) {
                    if (!$report['success']) {
                        $endpoint = $report['endpoint'];
                        if ($report['expired']) {
                            $subscription = $this->gEMWebPushSubscription()->findOneBy(['endpoint' => $endpoint]);
                            if ($subscription) {
                                $this->getEm()->remove($subscription);
                            }
                        }
                    }
                }
                $this->save();
            }
        }
    }

    public function sendCustomerNotification($order, $customer)
    {
        if ($order && $customer) {
            $notification = [
                'endpoint' => $customer->endpoint,
                'key' => $customer->key,
                'token' => $customer->token
            ];

            $payload = [
                'title' => 'Nedokončená objednávka v hodnotě '.$order->currency->markBefore.' '.number_format($order->totalPrice, $order->currency->countDecimal, '.', ' ').' '.$order->currency->markBehind,
                'body' => "Dobrý den\nvšimli jsme si, že se ve Vašem nákupním koši nachází nedokončená objednávka.
                Váš košík jsme Vám prozatím uložili, takže se z něj nic neztratí a objednávku můžete dokončit.
                Pokud si výběrem nejste jisti, nebo k produktům potřebujete více informací, neváhejte nám napsat e-mail
                nebo můžete zavolat. Rádi Vám pomůžeme vybrat produkt, který splní Vaše očekávání.\n",
                'icon' => 'assets/img/favicon/favicon-96x96.png',
                'url' => $this->linkGenerator->link('Basket:one')
            ];

            $auth = [
                'VAPID' => [
                    'subject' => 'mailto:marek@webrex.cz',
                    'publicKey' => file_get_contents('../keys/public_key.txt'),
                    'privateKey' => file_get_contents('../keys/private_key.txt'),
                ],
            ];

            $webPush = new WebPush($auth);

            $webPush->sendNotification(
                $notification['endpoint'],
                json_encode($payload),
                $notification['key'],
                $notification['token']
            );

            $flush = $webPush->flush();
            if ($flush !== true) {
                foreach ($flush as $report) {
                    if (!$report['success']) {
                        $endpoint = $report['endpoint'];
                        if ($report['expired']) {
                            $subscription = $this->gEMWebPushSubscription()->findOneBy(['endpoint' => $endpoint]);
                            if ($subscription) {
                                $this->getEm()->remove($subscription);
                            }
                        }
                    }
                }
                $this->save();
            }
        }
    }

    public function saveForSend($notification)
    {
        if (is_numeric($notification)) {
            $notification = $this->get()->find($notification);
        }

        $subscriptions = $this->gEMWebPushSubscription()->findAll();

        if ($subscriptions) {
            foreach ($subscriptions as $s) {
                $ent = new WebPushNotificationFront();
                $ent->setNotification($notification);
                $ent->setSubscription($s);
                $this->getEm()->persist($ent);
            }
            $this->save();
        }
    }

    public function sendNotificationCron()
    {
        $notifications = $this->gEMWebPushNotificationFront()->findBy([], [], 50);
        if ($notifications) {
            $auth = [
                'VAPID' => [
                    'subject' => 'mailto:marek@webrex.cz',
                    'publicKey' => file_get_contents('../keys/public_key.txt'),
                    'privateKey' => file_get_contents('../keys/private_key.txt'),
                ],
            ];

            $webPush = new WebPush($auth);

            foreach ($notifications as $n) {
                $s = [
                    'endpoint' => $n->subscription->endpoint,
                    'key' => $n->subscription->key,
                    'token' => $n->subscription->token
                ];

                $payload = [
                    'body' => $n->notification->body,
                    'title' => $n->notification->name,
                    'icon' => 'front/design/favicon/favicon-96x96.png',
                    'image' => $n->notification->image,
                    'url' => $n->notification->link
                ];

                if ($n->notification->product) {
                    $slug = Strings::webalize($n->notification->product->name);
                    $payload['url'] = $this->linkGenerator->link('ProductDetail:detail', [
                        'id' => $n->notification->product->id,
                        'slug' => $slug,
                        'utm_source' => 'web-push',
                        'utm_medium' => 'notification',
                        'utm_campaign' => Strings::webalize($n->notification->name)
                    ]);
                    if (isset($n->notification->product->images[0]->path)) {
                        $payload['image'] = $n->notification->product->images[0]->path;
                    }
                } else {
                    $query = parse_url($payload['url'], PHP_URL_QUERY);

                    if ($query) {
                        $payload['url'] .= '&utm_source=web-push&utm_medium=notification&utm_campaign=' . Strings::webalize($n->notification->name);
                    } else {
                        $payload['url'] .= '?utm_source=web-push&utm_medium=notification&utm_campaign=' . Strings::webalize($n->notification->name);
                    }
                }

                $webPush->sendNotification(
                    $s['endpoint'],
                    json_encode($payload),
                    $s['key'],
                    $s['token']
                );

                $this->getEm()->remove($n);
            }

            $this->save();

            $flush = $webPush->flush();
            if ($flush !== true) {
                foreach ($flush as $report) {
                    if (!$report['success']) {
                        $endpoint = $report['endpoint'];
                        if ($report['expired']) {
                            $subscription = $this->gEMWebPushSubscription()->findOneBy(['endpoint' => $endpoint]);
                            if ($subscription) {
                                $front = $this->gEMWebPushNotificationFront()->findBy(['subscription' => $subscription]);
                                if ($front) {
                                    foreach ($front as $f) {
                                        $this->getEm()->remove($f);
                                    }
                                }
                                $this->getEm()->remove($subscription);
                            }
                        }
                    }
                }
            }

            $this->save();
        }
    }
}