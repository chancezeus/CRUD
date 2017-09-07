<?php

namespace Backpack\CRUD\ModelTraits\SpatieTranslatable;

use Illuminate\Database\Eloquent\Builder;
use Cviebrock\EloquentSluggable\Sluggable as OriginalSluggable;

trait Sluggable
{
    use OriginalSluggable;

    /**
     * Hook into the Eloquent model events to create or
     * update the slug as required.
     */
    public static function bootSluggable()
    {
        static::observe(app(SluggableObserver::class));
    }

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
        $attribute = $attribute.'->'.$this->getLocale();

        return $query->where(function(Builder $q) use ($attribute, $slug, $separator) {
            $q->where($attribute, '=', $slug)
                ->orWhere($attribute, 'LIKE', $slug . $separator . '%');
        });
    }
}
