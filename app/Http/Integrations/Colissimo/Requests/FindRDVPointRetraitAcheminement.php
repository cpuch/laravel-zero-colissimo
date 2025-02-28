<?php

namespace App\Http\Integrations\Colissimo\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasXmlBody;
use Saloon\XmlWrangler\Data\RootElement;
use Saloon\XmlWrangler\XmlWriter;

class FindRDVPointRetraitAcheminement extends Request implements HasBody
{
    use HasXmlBody;

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $address,
        protected readonly string $zipCode,
        protected readonly string $city,
        protected readonly string $countryCode,
        protected readonly int $filterRelay,
        protected readonly int $optionInter,
        protected readonly int $weight,
    ) {}

    protected function defaultBody(): string
    {
        $writer = new XmlWriter;

        $rootElement = RootElement::make('soapenv:Envelope')->addNamespace('soapenv', 'http://schemas.xmlsoap.org/soap/envelope/')->addNamespace('v2', 'http://v2.pointretrait.geopost.com/');
        $xml = $writer->make()->write($rootElement, [
            'soapenv:Body' => [
                'v2:findRDVPointRetraitAcheminement' => [
                    'accountNumber' => config('colissimo.account_number'),
                    'password' => config('colissimo.password'),
                    'address' => $this->address,
                    'zipCode' => $this->zipCode,
                    'city' => $this->city,
                    'countryCode' => $this->countryCode,
                    'shippingDate' => date('d/m/Y'),
                    'requestId' => md5(rand(0, 99999)),
                    'filterRelay' => $this->filterRelay, // 0 => post offices only, 1 => post offices and shops
                    'optionInter' => $this->optionInter,
                    'weight' => ((floatval($this->weight)) <= 1) ? 1 : ceil(floatval($this->weight)),
                ],
            ],
        ]);

        return $xml;
    }

    public function defaultHeaders(): array
    {
        return [
            'Content-Length' => strlen($this->body()),
        ];
    }

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/';
    }
}
