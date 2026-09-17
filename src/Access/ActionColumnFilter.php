<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Access;

/**
 * Filters an action list by the parent control route, returning a string template.
 *
 * Kept for feature parity with the Yii2 `Mimin::filterActionColumn()`. Since Yii3 has no
 * official grid widget, consumers may use the returned template strings however they like.
 *
 * `$actions` accepts either a list (`['update', 'delete']`) or a map of route action => button label
 * (`['update' => 'update', 'drop' => 'delete']`).
 */
final class ActionColumnFilter
{
    public function __construct(private readonly AccessService $access)
    {
    }

    /**
     * @param array<int|string, string> $actions
     */
    public function filter(array $actions, string $currentRoute): string
    {
        $currentRoute = AccessService::normalizeRoute($currentRoute);
        $separator = strrpos($currentRoute, '/');
        $parent = $separator === false ? '' : substr($currentRoute, 0, $separator);

        $template = '';
        $seen = [];

        foreach ($actions as $key => $value) {
            $actionName = is_int($key) ? $value : $key;

            if (isset($seen[$actionName])) {
                continue;
            }
            $seen[$actionName] = true;

            $permission = $parent !== '' ? $parent . '/' . $actionName : $actionName;

            if ($this->access->checkRoute($permission, strict: true) || $this->access->checkRoute($permission)) {
                $template .= '{' . $value . '} ';
            }
        }

        return trim($template);
    }
}
