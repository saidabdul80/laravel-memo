<?php
namespace Saidabdulsalam\LaravelMemo\ModelFilters;

use EloquentFilter\ModelFilter;

class MemoFilter extends ModelFilter
{
    public $relations = [];

    public function title($title)
    {
        return $this->where('title', 'LIKE', "%$title%");
    }

    public function content($content)
    {
        return $this->where('content', 'LIKE', "%$content%");
    }

    public function ownerType($ownerType)
    {
        return $this->where('owner_type', $ownerType);
    }

    public function ownerId($ownerId)
    {
        return $this->where('owner_id', $ownerId);
    }


}