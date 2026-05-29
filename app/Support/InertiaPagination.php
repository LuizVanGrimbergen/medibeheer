<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;

final class InertiaPagination
{
    public const PER_PAGE = 10;

    public static function payload(LengthAwarePaginator $paginator, array $data): array
    {
        return [
            'data' => $data,
            'meta' => self::meta($paginator),
        ];
    }

    public static function empty(int $perPage = self::PER_PAGE): array
    {
        return [
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => $perPage,
                'total' => 0,
                'from' => null,
                'to' => null,
            ],
        ];
    }

    private static function meta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
}
