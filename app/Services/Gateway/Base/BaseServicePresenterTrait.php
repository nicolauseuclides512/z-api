<?php
/**
 * @author Jehan Afwazi Ahmad <jee.archer@gmail.com>.
 */

namespace App\Services\Gateway\Base;


use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\UriTemplate;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

trait BaseServicePresenterTrait
{

    private function buildUri($path, array $options = [])
    {
        if (!isset($options['parameters'])) {
            $options['parameters'] = [];
        }

        return (new UriTemplate())->expand($this->baseUri . $path, $options['parameters']);
    }

    public function get($path, array $params = []): ResponseInterface
    {
        $options = [];
        if ($params) {
            $path = $path . "{?" . implode(", ", array_keys($params)) . "}";
            $options['parameters'] = $params;
        }

        Log::info('get data from => ' . $this->buildUri($path, $options));

        return $this->client->get($this->buildUri($path, $options), array_merge($this->headers, $options));
    }

    public function getAsync($path, array $params = []): PromiseInterface
    {
        $options = [];
        if ($params) {
            $path = $path . "{?" . implode(", ", array_keys($params)) . "}";
            $options['parameters'] = $params;
        }

        Log::info('get async data from => ' . $this->buildUri($path, $options));

        return $this->client->getAsync($this->buildUri($path, $options), array_merge($this->headers, $options));
    }

    public function postAsync($path, array $data = [], array $params = [], $method = 'post'): PromiseInterface
    {
        $options = [];
        if ($params) {
            $path = $path . "{?" . implode(", ", array_keys($params)) . "}";
            $options['parameters'] = $params;
        }

        if ($data) {
            $options['json'] = $data;
        }

        $method = $method === 'put' ? 'put' : 'post';

        Log::info($method . ' data from => ' . $this->buildUri($path, $options));

        return $this->client->{$method . "Async"}($this->buildUri($path, $options), array_merge($this->headers, $options));
    }

    public function destroyAsync($path, array $params = [])
    {
        $options = [];
        if ($params) {
            $path = $path . "{?" . implode(", ", array_keys($params)) . "}";
            $options['parameters'] = $params;
        }

        Log::info('destroy data from => ' . $this->buildUri($path, $options));

        return $this->client->deleteAsync($this->buildUri($path, $options), array_merge($this->headers, $options));
    }
}