<?php

namespace Ndinhbang\Support\Model\Concerns;

use Illuminate\Support\Str;
use ReflectionMethod;
use SplFileObject;
use Illuminate\Database\Eloquent\Relations\Relation;

trait AvailableRelations
{
    /**
     * The methods that can be called in a model to indicate a relation.
     *
     * @var array
     */
    protected static array $relationMethods = [
        'hasMany',
        'hasManyThrough',
        'hasOneThrough',
        'belongsToMany',
        'hasOne',
        'belongsTo',
        'morphOne',
        'morphTo',
        'morphMany',
        'morphToMany',
        'morphedByMany',
    ];

    /**
     * Available relationships for the model.
     *
     * @var array
     */
    protected static array $availableRelations = [];

    /**
     * Gets list of available relations for this model
     * And stores it in the variable for future use
     *
     * @return array
     */
    public static function getAvailableRelations(): array
    {
        if (!empty(static::$availableRelations[static::class])) {
            return static::$availableRelations[static::class];
        }

        $model = resolve(static::class);

        return static::setAvailableRelations(
            collect(get_class_methods($model))
                ->map(fn ($method) => new ReflectionMethod($model, $method))
                ->reject(
                    fn (ReflectionMethod $method) => $method->isStatic()
                        || $method->isAbstract()
                        || $method->getDeclaringClass()->getName() !== get_class($model)
                )
                ->filter(function (ReflectionMethod $method) {
                    $file = new SplFileObject($method->getFileName());
                    $file->seek($method->getStartLine() - 1);
                    $code = '';
                    while ($file->key() < $method->getEndLine()) {
                        $code .= trim($file->current());
                        $file->next();
                    }

                    return collect(static::$relationMethods)
                        ->contains(fn ($relationMethod) => str_contains($code, '$this->'.$relationMethod.'('));
                })
                ->map(function (ReflectionMethod $method) use ($model) {
                    $relation = $method->invoke($model);

                    if (! $relation instanceof Relation) {
                        return null;
                    }

                    $relatedModel = $relation->getRelated();

                    return [
                        'name' => $method->getName(),
                        'type' => Str::afterLast(get_class($relation), '\\'),
                        'related' => get_class($relatedModel),
                        'table' => $relatedModel->getConnection()->getTablePrefix() . $relatedModel->getTable()
                    ];
                })
                ->filter()
                ->values()
                ->toArray()
        );
    }

    /**
     * Stores relationships for future use
     *
     * @param array $relations
     * @return array
     */
    public static function setAvailableRelations(array $relations): array
    {
        static::$availableRelations[static::class] = $relations;

        return $relations;
    }
}
