<?php

namespace App\Http\Integrations\Colissimo;

use Saloon\Http\Connector;

class ApiConnector extends Connector
{
    /**
     * The Base URL of the API
     */
    public function resolveBaseUrl(): string
    {
        return 'https://ws.colissimo.fr/pointretrait-ws-cxf/PointRetraitServiceWS/2.0?wsdl';
    }

    /**
     * Default headers for every request
     */
    protected function defaultHeaders(): array
    {
        return [
            'Accept' => '*/*',
            'Content-Type' => 'application/xml',
        ];
    }

    /**
     * Default HTTP client options
     */
    protected function defaultConfig(): array
    {
        return [];
    }
}
