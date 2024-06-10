<?php

namespace Front\Components\HomeCredit;

use Front\Model\Utils\ArrayValidator\ArrayValidator;
use Intra\Model\Utils\DPHCounter;
use Nette;
use Nette\Utils;

use Nette\Application\UI\ITemplateFactory;
use Kdyby\Translation\Translator;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\Control;
use Symfony\Component\Config\Definition\Exception\Exception;
use Tracy\Debugger;

class HomeCredit extends Control
{
    /** @var ITemplateFactory */
    public $templateFactory;

    /** @var LinkGenerator */
    public $linkGenerator;

    /** @var Translator */
    public $trans;

    private $productionMode;
    private $mode;
    private $locale;

    private $config = [
        'cs' => [
            'test' => [
                'productSetCode' => 'COCHCONL',
                'apiKey' => 'calculator_test_key',
                'baseUrl' => 'https://apicz-test.homecredit.net/verdun-train/',
                'username' => '024242tech',
                'password' => '024242tech',
                'calcUrl' => 'https://kalkulacka.train.hciapp.net/',
                'secretKey' => 'n#z?9:;a%&amp;(*er2'
            ],
            'prod' => [
                'productSetCode' => 'COCHCONL',
                'apiKey' => 'NxzSCamzCfNukEeQAg2H',
                'baseUrl' => 'https://api.homecredit.cz/',
                'username' => '023621tech',
                'password' => 'igKk48C9BuaF',
                'calcUrl' => 'https://kalkulacka.homecredit.cz/',
                'secretKey' => '}$&t6y9:$v?a'
            ]
        ],
        'sk' => [
            'test' => [
                'productSetCode' => 'COCHCONL',
                'apiKey' => 'calculator_test_key',
                'baseUrl' => 'https://apisk-test.homecredit.net/verdun-train/',
                'username' => '024242tech',
                'password' => '024242tech',
                'calcUrl' => null,
                'secretKey' => 'n#z?9:;a%&amp;(*er2'
            ],
            'prod' => [
                'productSetCode' => 'COCHCONL',
                'apiKey' => null,
                'baseUrl' => null,
                'username' => null,
                'password' => null,
                'calcUrl' => null,
                'secretKey' => null
            ]
        ]
    ];

    function __construct(ITemplateFactory $templateFactory, LinkGenerator $linkGenerator, Translator $trans)
    {
        $this->templateFactory = $templateFactory;
        $this->linkGenerator = $linkGenerator;
        $this->trans = $trans;
        $this->locale = $trans->getLocale();

        parent::__construct();
    }

    public function setProductionMode($mode)
    {
        $this->productionMode = $mode;

        if ($this->productionMode) {
            $this->mode = 'prod';
        } else {
            $this->mode = 'test';
        }
    }

    private function repairPhoneNumber($phone, $country)
    {
        $phone = str_replace([' ', '00421'], ['', '+421'], $phone);
        $length = strlen($phone);

        if ($length == 9) {
            if ($country == "SK") {
                $phone = '+421'.$phone;
            } else {
                $phone = '+420'.$phone;
            }
        }

        return $phone;
    }

    private function getStreet($street)
    {
        $cutWords = explode(' ', $street);

        $streetAddress = [];
        $streetNumber = [];


        foreach($cutWords as $k => $w) {
            if ($k == 0) {
                $streetAddress[] = $w;
            } elseif (count($streetNumber) > 0) {
                $streetNumber[] = $w;
            } else {
                if (preg_match('/\\d/', $w) > 0) {
                    $streetNumber[] = $w;
                } else {
                    $streetAddress[] = $w;
                }
            }
        }

        return [implode(' ', $streetAddress), implode(' ', $streetNumber)];
    }

    public function iShop($order, $productsInOrder)
    {
        try {
            $prices = $productsInOrder['totalPrices'];
            $products = $productsInOrder['products'];
            // Zalozime si autentizacni process
            $authorizationProcess = new \HomeCredit\OneClickApi\RestClient\AuthorizationProcess\AuthTokenAuthorizationProcess(
                $this->config[$this->locale][$this->mode]['username'],
                $this->config[$this->locale][$this->mode]['password']
            );

            // Pripravime si tovarnu na HTTP clienta
            $httpClientFactory = new \HomeCredit\OneClickApi\HttpClientFactory([
                'baseUrl' => $this->config[$this->locale][$this->mode]['baseUrl']
            ]);

            // Vytvorime si REST clienta
            $client = new \HomeCredit\OneClickApi\RestClient\Application(
                $httpClientFactory,
                $authorizationProcess
            );

            $dphCounter = new DPHCounter();
            $items = [];

            foreach ($products as $p) {
                $dphCounter->setPriceWithDPH($p['price'], $p['product']->vat->value, $p['count']);
                $totalPrice = $dphCounter->getTotalPrice() + $p['nastaveni']->getTotalPrice() + $p['pojisteni']->getTotalPrice();
                $totalVat = $dphCounter->getTotalDPH() + $p['nastaveni']->getTotalDPH() + $p['pojisteni']->getTotalDPH();
                $items[] = [
                    'code' => $p['product']->id,
                    'name' => $p['name'],
                    'totalPrice' => [
                        'amount' => round($totalPrice * 100),
                        'currency' => $order->currency->code
                    ],
                    'totalVat' => [
                        'amount' => round($totalVat * 100),
                        'currency' => $order->currency->code,
                        'vatRate' => $p['product']->vat->value
                    ]
                ];
            }

            $vats = [];

            foreach ($items as $i) {
                $vats[$i['totalVat']['vatRate']] = $vats[$i['totalVat']['vatRate']] + $i['totalVat']['amount'];
            }

            list($streetAddress, $streetNumber) = $this->getStreet($order->customer->street);
            list($orderStreetAddress, $orderStreetNumber) = $this->getStreet($order->street);

            $applicationRequest = [
                'customer' => [
                    'firstName' => $order->customer->name,
                    'lastName' => $order->customer->surname,
                    'email' => $order->customer->email,
                    'phone' => $this->repairPhoneNumber($order->customer->phone, $order->customer->country),
                    'addresses' => [
                        [
                            'name' => $order->customer->name . ' ' . $order->customer->surname,
                            'country' => $order->customer->country,
                            'city' => $order->customer->city,
                            'streetAddress' => $streetAddress,
                            'streetNumber' => $streetNumber,
                            'zip' => str_replace(' ', '', $order->customer->zip),
                            'addressType' => 'PERMANENT'
                        ]
                    ]
                ],
                'order' => [
                    'number' => $order->variableSymbol,
                    'totalPrice' => [
                        'amount' => round($prices['totalWithSale'] * 100),
                        'currency' => $order->currency->code
                    ],
                    'addresses' => [
                        [
                            'name' => $order->name,
                            'country' => $order->country,
                            'city' => $order->city,
                            'streetAddress' => $orderStreetAddress,
                            'streetNumber' => $orderStreetNumber,
                            'zip' => str_replace(' ', '', $order->zip),
                            'addressType' => 'BILLING'
                        ]
                    ],
                ],
                'type' => 'INSTALLMENT',
                'settingsInstallment' => [
                    /*'preferredMonths' => 6,
                    'preferredInstallment' => [
                        'amount' => round($prices['totalWithSale'] * 100),
                        'currency' => $order->currency->code
                    ],
                    'preferredDownPayment' => [
                        'amount' => 0,
                        'currency' => $order->currency->code
                    ]*/
                ],
                'merchantUrls' => [
                    'approvedRedirect' => $this->linkGenerator->link('Basket:returnFromHomecreditApproved'),
                    'rejectedRedirect' => $this->linkGenerator->link('Basket:returnFromHomecreditRejected'),
                    'notificationEndpoint' => $this->linkGenerator->link('Cron:notificationHomecredit', ['id' => $order->variableSymbol])
                ]
            ];

            foreach ($vats as $k => $v) {
                $applicationRequest['order']['totalVat'][] = [
                    'amount' => $v,
                    'currency' => $order->currency->code,
                    'vatRate' => $k,
                ];
            }

            $applicationRequest['order']['items'] = $items;

            $request = \HomeCredit\OneClickApi\Entity\CreateApplicationRequest::fromArray($applicationRequest);

            // Zalozime application pres API. V response budeme mi objekt HomeCredit\OneClickApi\Entity\CreateApplicationResponse
            $response = $client->create($request);

            return $response->getGatewayRedirectUrl();
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        return null;
    }

    public function renderICalc($data = [])
    {
        $template = $this->templateFactory->createTemplate();

        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/iCalc.latte');

        $template->id = uniqid();
        $template->url = $this->config[$this->locale][$this->mode]['calcUrl'];
        $template->productSetCode = $this->config[$this->locale][$this->mode]['productSetCode'];
        $template->apiKey = $this->config[$this->locale][$this->mode]['apiKey'];
        $template->price = $data['price'] * 100;

        $template->render();
    }

    public function getDetail($order)
    {
        // Zalozime si autentizacni process
        $authorizationProcess = new \HomeCredit\OneClickApi\RestClient\AuthorizationProcess\AuthTokenAuthorizationProcess(
            $this->config[$this->locale][$this->mode]['username'],
            $this->config[$this->locale][$this->mode]['password']
        );

        // Pripravime si tovarnu na HTTP clienta
        $httpClientFactory = new \HomeCredit\OneClickApi\HttpClientFactory([
            'baseUrl' => $this->config[$this->locale][$this->mode]['baseUrl']
        ]);

        // Vytvorime si REST clienta
        $client = new \HomeCredit\OneClickApi\RestClient\Application(
            $httpClientFactory,
            $authorizationProcess
        );

        $request = \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailRequest::fromArray(['number' => $order->variableSymbol]);

        $response = $client->create($request);

        return $response;
    }

    public function checkChecksum($orderNumber, $state, $checksum)
    {
        $message = utf8_encode($orderNumber.':'.$state);
        return $checksum == strtoupper(hash_hmac('sha512', $message, $this->config[$this->locale][$this->mode]['secretKey']));
    }
}