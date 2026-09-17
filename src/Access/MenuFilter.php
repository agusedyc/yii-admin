<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Access;

use function is_array;
use function is_string;

/**
 * Recursively filters menu item lists by route access.
 *
 * Menu items support two URL shapes:
 *  - `'url' => 'routeName'`
 *  - `'url' => ['routeName', 'routeParams']`
 *
 * This is the Yii3 equivalent of the Yii2 `Mimin::filterMenu()`.
 */
final class MenuFilter
{
    public function __construct(private readonly AccessService $access)
    {
    }

    /**
     * @param array[] $items
     *
     * @return array[]
     */
    public function filter(array $items, bool $strict = false): array
    {
        $result = [];

        foreach ($items as $item) {
            $children = $item['items'] ?? null;

            if (is_array($children)) {
                $filtered = $this->filter($children, $strict);
                if ($filtered === []) {
                    continue;
                }

                $item['items'] = $filtered;
                $result[] = $item;
                continue;
            }

            $url = $item['url'] ?? null;
            $route = $this->extractRoute($url);

            if ($route !== null) {
                if (!$this->access->checkRoute($route, $strict)) {
                    continue;
                }
            }

            $result[] = $item;
        }

        return $result;
    }

    private function extractRoute(mixed $url): ?string
    {
        if (is_string($url)) {
            return $url;
        }

        if (is_array($url) && isset($url[0]) && is_string($url[0])) {
            return $url[0];
        }

        return null;
    }
}
