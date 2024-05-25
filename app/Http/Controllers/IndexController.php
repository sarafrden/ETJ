<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class IndexController extends Controller
{
    private $googleScholarApiKey;
    private $scopusApiKey;

    public function __construct()
    {
        $this->googleScholarApiKey = '40227d4d082c35c9455701c46f746d04f5f0cbbcc444f9e0d0bb2ce2377ff0c0';
        $this->scopusApiKey = '9b2951a83c3936ae65f1929f734af02a';
    }

    public function index(Request $request)
    {
        $query = $request->input('query');

        $googleScholarData = $this->getGoogleScholarData($query);
        $scopusData = $this->getScopusData($query);

        $citationData = array_merge($googleScholarData, $scopusData);

        return response()->json($citationData);
    }

    private function getGoogleScholarData(string $query): array
    {
        $client = new Client();

        $response = $client->get('https://serpapi.com/search.json', [
            'query' => [
                'engine' => 'google_scholar',
                'q' => $query,
                'api_key' => $this->googleScholarApiKey,
            ],
        ]);

        $googleScholarData = [];
        foreach (json_decode($response->getBody()->getContents(), true)['organic_results'] as $result) {
            $googleScholarData[] = [
                'title' => $result['title'],
                'authors' => $result['publication_info']['summary'],
                'year' => null, // You can try to extract the year from the snippet or publication info
                'citations' => $result['inline_links'],
                'url' => $result['link'],
            ];
        }

        return $googleScholarData;
    }

    private function getScopusData(string $query): array
    {
        $client = new Client();

        $response = $client->get('https://api.elsevier.com/content/search/scopus', [
            'query' => [
                'query' => $query,
                'apiKey' => $this->scopusApiKey,
                'httpAccept' => 'application/json',
            ],
        ]);

        $scopusData = [];
        foreach (json_decode($response->getBody()->getContents(), true)['search-results']['entry'] as $result) {
            $scopusData[] = [
                'title' => $result['dc:title'],
                'authors' => $result['dc:creator'],
                'year' => $result['prism:coverDate'],
                'citations' => $result['citedby-count'],
                'url' => $result['prism:url'],
            ];
        }

        return $scopusData;
    }
}
