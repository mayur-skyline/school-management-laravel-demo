<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    /**
     * --------------------------------------------------
     * search the keywords in the given table fields.
     * --------------------------------------------------
     * @param Builder $builder
     * @param array $fields
     * @param string $keyword
     * @return Builder
     * --------------------------------------------------
     */
    public function scopeSearch(Builder $builder, array $fields, string $keyword): Builder
    {
        $keywords = [];
        // remove special chars
        $keyword = preg_replace('/\W+/im', ' ', $keyword);
        // extract unique words
        $words = array_unique(preg_split('/\s+/', $keyword, -1, PREG_SPLIT_NO_EMPTY));
        array_walk($words, function ($word) use (&$keywords) {
            // accept chars >= 2
            if (strlen($word) >= 2) {
                $keywords[] = $word;
            }
        });

        if (!empty($keywords)) {
            $builder->where(function ($query) use ($fields, $keywords) {
                // each keyword
                foreach ($keywords as $keyword) {
                    $query->orWhere(function ($query) use ($fields, $keyword) {
                        // travel every fields
                        foreach ($fields as $field) {
                            $query->orWhere($field, 'like', '%' . $keyword . '%');
                        }
                    });
                }
            });
        }
        return $builder;
    }
}