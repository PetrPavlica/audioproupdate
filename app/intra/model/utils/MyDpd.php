<?php

namespace Intra\Model\Utils;

use App\Core\Model\Database\Entity\Setting;
use Intra\Model\Database\Entity\DpdOrder;
use Intra\Model\Database\Entity\DpdPickup;
use Intra\Model\Database\Entity\Orders;
use Kdyby\Doctrine\EntityManager;
//use Nette\Object;
use Tracy\Debugger;

/**
 * Adaptér pro API Moje DPD
 * Class MyDpd
 * @package Intra\Model\Utils
 */
class MyDpd
{
    private $services = [
        'shipment' => 'https://www.mojedpd.cz/IT4EMWebServices/eshop/ShipmentServiceImpl?wsdl',
        'manifest' => 'https://www.mojedpd.cz/IT4EMWebServices/eshop/ManifestServiceImpl?wsdl',
        'pickupOrder' => 'https://www.mojedpd.cz/IT4EMWebServices/eshop/PickupOrderServiceImpl?wsdl'
    ];

    private $config = null;

    private $em;

    public function __construct(EntityManager $em)
    {
        if (!extension_loaded('soap')) {
            throw new \Exception('Extension soap is not loaded.');
        }

        $this->em = $em;
    }

    public function call($service, $method, $params)
    {
        try {
            $client = new \SoapClient($this->services[$service], ['cache_wsdl' => false, 'trace' => true]);
            if (!$this->config) {
                $this->config = [
                    'wsUserName' => $this->setting('dpd_username'),
                    'wsPassword' => $this->setting('dpd_password'),
                    'wsLang' => 'CZ',
                    'applicationType' => 9
                ];
            }
            $params = array_merge($this->config, $params);
            $response = $client->__soapCall($method, array($params));
            if (isset($response->result->resultList->error)) {
                Debugger::log($response->result->resultList->error->text, 'DPDError');
                //throw new \Exception($response['result']['resultList']['error']['text'], $response['result']['resultList']['error']['code']);
            }
            if (isset($response->result->resultList)) {
                return $response->result->resultList;
            } else if (isset($response->result)) {
                return $response->result;
            } else if (isset($response->return)) {
                return $response->return;
            }
            return $response;
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        return null;
    }

    public function getFunctions($service)
    {
        if (!isset($this->services[$service])) {
            throw new \Exception('Service '.$service.' not exist.');
        }
        try {
            $client = new \SoapClient($this->services[$service]);
            return $client->__getFunctions();
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        return null;
    }

    public function setting($code)
    {
        return $this->em->getRepository(Setting::class)->findOneBy(['codeSetting' => $code])->value;
    }

    public function settingEntity($code)
    {
        return $this->em->getRepository(Setting::class)->findOneBy(['codeSetting' => $code]);
    }

    public function createShipment(Orders $order, $packages, $addressId, $orderNumber, $shipmentExist)
    {
        $mainServiceCode = 109;
        if ($order->deliveryMethod && $order->deliveryMethod->isDPD && $order->deliveryPlace) {
            $mainServiceCode = 50101;
        }
        $params = [
            'priceOption' => 'WithoutPrice',
            'shipmentList' => [
                'shipmentReferenceNumber' => $order->variableSymbol.'-'.$orderNumber,
                'payerId' => $this->setting('dpd_payerid'),
                'senderAddressId' => $addressId,
                'mainServiceCode' => $mainServiceCode,
                'receiverPhoneNo' => $order->phone,
                'receiverEmail' => $order->email
            ]
        ];

        if ($order->deliveryToOther) {
            $params['shipmentList'] = array_merge($params['shipmentList'], [
                'receiverName' => $order->contactPerson ? $order->contactPerson : $order->name,
                'receiverFirmName' => $order->company,
                'receiverCountryCode' => $order->countryDelivery ? $order->countryDelivery : $order->country,
                'receiverZipCode' => str_replace(' ', '', $order->zipDelivery),
                'receiverCity' => $order->cityDelivery,
                'receiverStreet' => $order->streetDelivery
            ]);
        } else {
            $params['shipmentList'] = array_merge($params['shipmentList'], [
                'receiverName' => $order->name,
                'receiverFirmName' => $order->company,
                'receiverCountryCode' => $order->country,
                'receiverZipCode' => str_replace(' ', '', $order->zip),
                'receiverCity' => $order->city,
                'receiverStreet' => $order->street
            ]);
        }

        $params['shipmentList']['additionalServices'] = [];

        if ($mainServiceCode == 109) {
            $params['shipmentList']['additionalServices']['predictSms'] = [
                'telephoneNr' => $order->phone
            ];
        }

        if ($order->deliveryMethod && $order->deliveryMethod->isDPD && $order->deliveryPlace) {
            $dpdPickup = $this->em->getRepository(DpdPickup::class)->findOneBy(['code' => $order->deliveryPlace]);
            if ($dpdPickup) {
                $params['shipmentList']['additionalServices']['parcelShop'] = [
                    'parcelShopId' => $order->deliveryPlace,
                    'companyName' => $dpdPickup->company,
                    'houseNo' => $dpdPickup->houseNumber,
                    'street' => $dpdPickup->street,
                    'zipCode' => $dpdPickup->postcode,
                    'city' => $dpdPickup->city,
                    'countryCode' => $order->country,
                    'phoneNo' => $dpdPickup->phone,
                    'email' => $dpdPickup->email,
                    'fetchGsPUDOpoint' => 1
                ];
            } else {
                $params['shipmentList']['additionalServices']['parcelShop'] = [
                    'parcelShopId' => $order->deliveryPlace
                ];
            }
        }

        if (!$shipmentExist && $order->paymentMethod->deliveryCash) { // pokud je platba na dobírku
            $params['shipmentList']['additionalServices'] = array_merge($params['shipmentList']['additionalServices'], [
                'cod' => [
                    'amount' => $order->totalPrice,
                    'currency' => $order->currency->code,
                    'paymentType' => 'CrossedCheck',
                    'referenceNumber' => $order->variableSymbol
                ]
            ]);
        }

        if ($packages) {
            foreach ($packages as $p) {
                $params['shipmentList']['parcels'][] = [
                    'parcelReferenceNumber' => $p->id,
                    'weight' => $p->weight
                ];

                if ($p->height) {
                    $params['shipmentList']['parcels'][count($params['shipmentList']['parcels']) - 1]['dimensionsHeight'] = $p->height;
                }
                if ($p->width) {
                    $params['shipmentList']['parcels'][count($params['shipmentList']['parcels']) - 1]['dimensionsWidth'] = $p->width;
                }
                if ($p->length) {
                    $params['shipmentList']['parcels'][count($params['shipmentList']['parcels']) - 1]['dimensionsLength'] = $p->length;
                }
                if ($p->additionalInfo && !empty(trim($p->additionalInfo))) {
                    $params['shipmentList']['receiverAdditionalAddressInfo'] = trim($p->additionalInfo);
                }
            }
        }

        return $this->call('shipment', 'createShipment', $params);
    }

    public function deleteShipment($shipment)
    {
        $params = [
            'shipmentReferenceList' => [
                'id' => $shipment
            ]
        ];
        return $this->call('shipment', 'deleteShipment', $params);
    }

    public function getShipmentLabel($shipmentList)
    {
        $params = [
            'shipmentReferenceList' => [],
            'printOption' => 'Pdf',
            'printFormat' => 'A4',
            'printPosition' => 'LeftTop',
        ];
        if ($shipmentList) {
            foreach ($shipmentList as $s) {
                $params['shipmentReferenceList'][] = [
                    'id' => $s
                ];
            }
        }

        return $this->call('shipment', 'getShipmentLabel', $params);
    }

    public function closeManifest($shipmentList, $referenceNumber)
    {
        $params = [
            'manifest' => [
                'manifestReferenceNumber' => $referenceNumber,
                'shipmentReferenceList' => []
            ],
            'manifestPrintOption' => 'PrintManifestWithAllParcels',
            'printOption' => 'Pdf'
        ];

        if ($shipmentList) {
            foreach ($shipmentList as $s) {
                $params['manifest']['shipmentReferenceList'][] = ['id' => $s];
            }
        }

        return $this->call('manifest', 'closeManifest', $params);
    }

    public function reprintManifest($referenceNumber)
    {
        $params = [
            'manifestReference' => [
                'id' => $referenceNumber
            ],
            'manifestPrintOption' => 'PrintManifestWithAllParcels',
            'printOption' => 'Pdf'
        ];

        return $this->call('manifest', 'reprintManifest', $params);
    }

    public function createPickupOrder(DpdOrder $order)
    {
        $params = [
            'pickupOrderList' => [
                'payerId' => $this->setting('dpd_payerid'),
                'senderAddress' => [
                    'countryCode' => $order->dpdAddress->countryCode,
                    'city' => $order->dpdAddress->city,
                    'street' => $order->dpdAddress->street,
                    'zipCode' => $order->dpdAddress->zipCode
                ],
                'senderAdderssId' => [$order->dpdAddress->addressId],
                'date' => $order->date->format('Ymd'),
                'fromTime' => $order->fromTime->format('His'),
                'toTime' => $order->toTime->format('His'),
                'contactName' => $this->setting('dpd_contact_name'),
                'contactPhone' => $this->setting('dpd_contact_phone'),
                'contactEmail' => $this->setting('dpd_contact_email'),
                'specialInstruction' => $order->specialInstruction,
                'referenceNumber' => $order->id,
                'pieces' => [
                    'serviceCode' => 109,
                    'quantity' => $order->quantity,
                    'weight' => $order->weight,
                    'destinationCountryCode' => $order->destinationCountryCode,
                ]
            ]
        ];

        return $this->call('pickupOrder', 'createPickupOrder', $params);
    }

    public function deletePickupOrder(DpdOrder $order)
    {
        $params = [
            'pickupOrderReferenceList' => [
                'id' => $order->referenceNumber
            ],
            'description' => $order->description
        ];

        return $this->call('pickupOrder', 'deletePickupOrder', $params);
    }

    public function getShipmentStatus($shipments)
    {
        $params = [
            'shipmentReferenceList' => []
        ];

        if ($shipments) {
            foreach ($shipments as $s) {
                $params['shipmentReferenceList'][] = [
                    'id' => $s
                ];
            }
        }

        return $this->call('shipment', 'getShipmentStatus', $params);
    }

    public function getParcelStatus($shipments)
    {
        $params = [
            'parcelReferenceList' => []
        ];

        if ($shipments) {
            foreach ($shipments as $s) {
                $params['parcelReferenceList'][] = [
                    'id' => $s
                ];
            }
        }

        return $this->call('shipment', 'getParcelStatus', $params);
    }
}