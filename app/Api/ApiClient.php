<?php

declare(strict_types=1);

namespace App\Api;

use Http;
use Illuminate\Http\Client\PendingRequest;

final readonly class ApiClient
{
    public PendingRequest $request;

    public string $endPoint;

    public function __construct()
    {
        $this->request = Http::acceptJson()->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);

        $this->endPoint = 'http://172.17.0.1:8080/api/rest';
    }
}
