<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms\Support;

final class Pagination
{
    /**
     * @return array{limit?: int, offset?: int}
     */
    public static function query(?int $limit, ?int $offset): array
    {
        return array_filter([
            'limit' => $limit,
            'offset' => $offset,
        ], fn (?int $value): bool => $value !== null);
    }
}
