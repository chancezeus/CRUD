<?php

namespace Backpack\CRUD\ModelTraits\SpatieTranslatable;

use Illuminate\Database\Eloquent\Builder;
use Cviebrock\EloquentSluggable\Sluggable as OriginalSluggable;

trait Sluggable
{
    use OriginalSluggable;

    /**
     * Query scope for finding "similar" slugs, used to determine uniqueness.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $attribute
     * @param array $config
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindSimilarSlugs(Builder $query, string $attribute, array $config, string $slug): Builder
    {
        $separator = $config['separator'];

        if (in_array(HasTranslations::class, class_uses_recursive($this)) && $this->isTranslatableAttribute($attribute)) {
            $attribute = $attribute . '->' . $this->getLocale();
        }

        return $query->where(function (Builder $q) use ($attribute, $slug, $separator) {
            $q->where($attribute, '=', $slug)
                ->orWhere($attribute, 'LIKE', $slug . $separator . '%');
        });
    }
}
