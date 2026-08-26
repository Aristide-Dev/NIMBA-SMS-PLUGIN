<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms\Exceptions;

use Illuminate\Http\Client\Response;
use RuntimeException;
use Throwable;

class NimbaSmsException extends RuntimeException
{
    /**
     * @param  array<string, mixed>|list<mixed>  $body
     */
    public function __construct(
        string $message,
        public readonly int $status = 0,
        public readonly array $body = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $status, $previous);
    }

    public static function fromResponse(Response $response): self
    {
        $body = $response->json();
        $payload = is_array($body) ? $body : ['body' => $response->body()];
        $message = self::extractMessage($payload) ?? ($response->reason() ?: 'Nimba SMS API error');
        $status = $response->status();

        return match (true) {
            $status === 401 => new AuthenticationException($message, $status, $payload),
            $status === 404 => new NotFoundException($message, $status, $payload),
            $status === 400, $status === 422 => new ValidationException($message, $status, $payload),
            $status === 420, $status === 429 => new RateLimitException($message, $status, $payload),
            $status >= 500 => new ServerException($message, $status, $payload),
            default => new self($message, $status, $payload),
        };
    }

    /**
     * @param  array<string, mixed>|list<mixed>  $payload
     */
    public static function extractMessage(array $payload): ?string
    {
        $detail = $payload['detail'] ?? null;

        if (is_string($detail) && $detail !== '') {
            return $detail;
        }

        if (is_array($detail) && $detail !== []) {
            $first = reset($detail);

            return is_string($first) ? $first : self::extractMessage(is_array($first) ? $first : []);
        }

        $messages = [];

        foreach ($payload as $value) {
            if (is_string($value) && $value !== '') {
                $messages[] = $value;

                continue;
            }

            if (is_array($value)) {
                $nested = self::extractMessage($value);

                if (is_string($nested)) {
                    $messages[] = $nested;
                }
            }
        }

        return $messages === [] ? null : implode(' ', $messages);
    }
}
