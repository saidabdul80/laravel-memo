<?php
namespace Saidabdulsalam\LaravelMemo\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait Filterable
{
    public function scopeFilter(Builder $query, Request $request)
    {
        $filters = $request->all();

        foreach ($filters as $filter => $value) {
            if (is_null($value)) {
                continue;
            }

            $method = 'filter' . ucfirst(Str::camel($filter));

            if (method_exists($this, $method)) {
                $this->$method($query, $value);
            }
        }

        return $query;
    }

    protected function filterTitle(Builder $query, $title)
    {
        return $query->where('title', 'LIKE', "%$title%");
    }

    protected function filterContent(Builder $query, $content)
    {
        return $query->where('content', 'LIKE', "%$content%");
    }

    protected function filterOwnerType(Builder $query, $ownerType)
    {
        return $query->where('owner_type', $ownerType);
    }

    protected function filterOwnerId(Builder $query, $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }

}
