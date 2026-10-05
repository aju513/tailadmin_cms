<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class RecordOrder
{
    public static function replace(array $ids, array $originalOrder, array $allIds): array
    {
        $ids = array_map('intval', $ids);
        $originalOrder = array_map('intval', $originalOrder);
        $submitted = $ids;
        $expected = $originalOrder;
        sort($submitted);
        sort($expected);
        if ($ids === [] || $submitted !== $expected) {
            throw ValidationException::withMessages(['records' => 'The list changed. Reload before reordering.']);
        }
        $offset = array_search($originalOrder[0], $allIds, true);
        if ($offset === false || array_slice($allIds, $offset, count($originalOrder)) !== $originalOrder) {
            throw ValidationException::withMessages(['records' => 'The order changed. Reload before reordering.']);
        }
        array_splice($allIds, $offset, count($originalOrder), $ids);

        return $allIds;
    }
}
