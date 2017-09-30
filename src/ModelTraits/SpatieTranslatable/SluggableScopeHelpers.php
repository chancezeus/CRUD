<?php

namespace Backpack\CRUD\ModelTraits\SpatieTranslatable;

use Cviebrock\EloquentSluggable\SluggableScopeHelpers as OriginalSluggableScopeHelpers;

trait SluggableScopeHelpers
{
    use OriginalSluggableScopeHelpers;

    /**
     * Primary slug column of this model.
     *
     * @return string
     */
    public function getSlugKeyName(): string
    {
        if (property_exists($this, 'slugKeyName')) {
            $key = $this->slugKeyName;
        } else {
            $config = $this->sluggable();
            $name = reset($config);
            $key = key($config);

            // check for short configuration
            if ($key === 0) {
                $key = $name;
            }
        }

        if (in_array(HasTranslations::class, class_uses_recursive($this)) && $this->isTranslatableAttribute($key) && strpos($key, '->') === false) {
            $key = $key . '->' . $this->getLocale();
        }

        return $key;
    }
}
