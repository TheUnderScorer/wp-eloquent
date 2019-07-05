<?php

namespace UnderScorer\ORM\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use UnderScorer\ORM\Contracts\MetaInterface;

/**
 * @method HasMany hasMany( string $related, string $foreignKey = null, string $localKey = null )
 * @property string $metaRelation
 * @property string $metaForeignKey
 */
trait WithMeta
{

    /**
     * @param string $key
     *
     * @return MetaInterface
     */
    public function getSingleMeta( string $key ): ?MetaInterface
    {
        return $this
            ->meta()
            ->where( 'meta_key', '=', $key )
            ->limit( 1 )
            ->get()
            ->first();
    }

    /**
     * @return HasMany
     */
    public function meta()
    {
        return $this->hasMany( $this->metaRelation, $this->metaForeignKey );
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return MetaInterface
     */
    public function addMeta( string $key, $value ): MetaInterface
    {
        /**
         * @var MetaInterface $meta
         */
        $meta = $this->meta()->create( [
            'meta_key'   => $key,
            'meta_value' => $value,
        ] );

        return $meta;
    }

    /**
     * @param string $key
     *
     * @return Collection|MetaInterface[]
     */
    public function getMeta( string $key )
    {
        return $this
            ->meta()
            ->where( 'meta_key', '=', $key )
            ->get();
    }

    /**
     * @param string $key
     * @param        $value
     * @param null   $prevValue
     *
     * @return bool|false|\Illuminate\Database\Eloquent\Model
     */
    public function updateMeta( string $key, $value, $prevValue = null )
    {
        $query = $this->meta()->where( 'meta_key', '=', $key );

        if ( ! is_null( $prevValue ) ) {
            $query->where( 'meta_value', '=', $prevValue );
        }

        /**
         * @var MetaInterface $meta
         */
        $meta = $query->firstOrCreate( [
            'meta_key' => $key,
        ] );

        if ( ! $meta ) {
            return false;
        }

        $meta->setMetaValue( $value );

        return $meta->save();
    }

}
