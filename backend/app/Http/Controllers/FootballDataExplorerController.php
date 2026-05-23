<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FootballDataExplorerController extends Controller
{
    public function __invoke(Request $request)
    {
        $apiKey = (string) config('services.football_data.key');
        $baseUrl = (string) config('services.football_data.base', 'https://api.football-data.org/v4');

        $endpoint = (string) $request->query('endpoint', 'competitions/WC/matches');
        $rawQuery = (string) $request->query('query', 'status=SCHEDULED');

        $responseData = null;
        $status = null;
        $error = null;

        if ($request->has('endpoint')) {
            if ($apiKey === '') {
                $error = 'FOOTBALL_DATA_API_KEY is not configured in your .env file.';
            } else {
                $normalizedEndpoint = $this->normalizeEndpoint($endpoint);
                $query = $this->parseQueryString($rawQuery);

                try {
                    $response = Http::acceptJson()
                        ->timeout(20)
                        ->withHeaders([
                            'X-Auth-Token' => $apiKey,
                        ])
                        ->baseUrl(rtrim($baseUrl, '/'))
                        ->get($normalizedEndpoint, $query);

                    $status = $response->status();
                    $responseData = $response->json();

                    if (! is_array($responseData)) {
                        $responseData = [
                            'raw' => $response->body(),
                        ];
                    }
                } catch (\Throwable $exception) {
                    $error = $exception->getMessage();
                }
            }
        }

        return view('football-data-explorer', [
            'endpoint' => $endpoint,
            'rawQuery' => $rawQuery,
            'status' => $status,
            'responseData' => $responseData,
            'error' => $error,
        ]);
    }

    private function normalizeEndpoint(string $endpoint): string
    {
        $endpoint = trim($endpoint);

        if ($endpoint === '') {
            return 'competitions/WC/matches';
        }

        $endpoint = preg_replace('#^https?://[^/]+/#i', '', $endpoint) ?? $endpoint;
        $endpoint = preg_replace('#^v4/#', '', ltrim($endpoint, '/')) ?? ltrim($endpoint, '/');

        return $endpoint;
    }

    private function parseQueryString(string $rawQuery): array
    {
        $rawQuery = trim($rawQuery);

        if ($rawQuery === '') {
            return [];
        }

        $result = [];
        parse_str($rawQuery, $result);

        return is_array($result) ? $result : [];
    }
}
