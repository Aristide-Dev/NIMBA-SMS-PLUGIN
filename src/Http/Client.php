<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms\Http;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Nimbasms\Nimbasms\Exceptions\NimbaSmsException;

final readonly class Client
{
    public const int MAX_RECIPIENTS = 30;

    public function __construct(
        public string $baseUrl,
        public string $serviceId,
        public string $secretToken,
        public int $timeout = 20,
        public string $senderName = '',
    ) {}

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>|list<mixed>
     */
    public function get(string $path, array $query = []): array
    {
        return $this->send('GET', $path, query: $query);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>|list<mixed>
     */
    public function post(string $path, array $body = []): array
    {
        return $this->send('POST', $path, body: $body);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>|list<mixed>
     */
    public function patch(string $path, array $body = []): array
    {
        return $this->send('PATCH', $path, body: $body);
    }

    /**
     * @return array<string, mixed>|list<mixed>
     */
    public function delete(string $path): array
    {
        return $this->send('DELETE', $path);
    }

    /**
     * @return array<string, mixed>|list<mixed>
     */
    public function upload(string $path, string $field, string $contents, string $filename): array
    {
        try {
            $response = $this->http(asJson: false)
                ->attach($field, $contents, $filename)
                ->patch($path);
        } catch (ConnectionException $exception) {
            throw new NimbaSmsException($exception->getMessage(), 0, [], $exception);
        }

        return $this->decode($response);
    }

    /**
     * @param  array<string, mixed>  $query
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>|list<mixed>
     */
    private function send(string $method, string $path, array $query = [], array $body = []): array
    {
        try {
            $pending = $this->http();

            $response = match (strtoupper($method)) {
                'GET' => $pending->get($path, $query),
                'POST' => $pending->post($path, $body),
                'PATCH' => $pending->patch($path, $body),
                'DELETE' => $pending->delete($path),
                default => throw new NimbaSmsException("Unsupported HTTP method [{$method}]."),
            };
        } catch (ConnectionException $exception) {
            throw new NimbaSmsException($exception->getMessage(), 0, [], $exception);
        }

        return $this->decode($response);
    }

    private function http(bool $asJson = true): PendingRequest
    {
        $pending = Http::baseUrl($this->baseUrl)
            ->withBasicAuth($this->serviceId, $this->secretToken)
            ->acceptJson()
            ->timeout($this->timeout)
            ->withHeaders([
                'X-Nimba-Client' => 'aristide/nimbasms',
            ]);

        return $asJson ? $pending->asJson() : $pending;
    }

    /**
     * @return array<string, mixed>|list<mixed>
     */
    private function decode(Response $response): array
    {
        if ($response->status() === 204) {
            return [];
        }

        if ($response->successful()) {
            $json = $response->json();

            return is_array($json) ? $json : [];
        }

        throw NimbaSmsException::fromResponse($response);
    }
}
