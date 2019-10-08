<?php

namespace UnderScorer\ORM\Traits;

use Illuminate\Support\Arr;
use UnderScorer\ORM\Eloquent\Model;

/**
 * @mixin Model
 */
trait Aliases
{
    /**
     * @param string $new
     * @param string $old
     */
    public static function addAlias( $new, $old )
    {
        static::$aliases[ $new ] = $old;
    }

    /**
     * Get alias value from mutator or directly from attribute
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    public function mutateAttribute( $key, $value )
    {
        if ( $this->hasGetMutator( $key ) ) {
            return parent::mutateAttribute( $key, $value );
        }

        return $this->getAttribute( $key );
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute( $key )
    {
        $aliases = static::getAliases();

        $value = parent::getAttribute( $key );

        if ( $value === null && count( $aliases ) ) {
            if ( $value = Arr::get( $aliases, $key ) ) {
                if ( is_array( $value ) ) {
                    $meta = Arr::get( $value, 'meta' );

                    return $meta ? $this->meta->$meta : null;
                }

                return parent::getAttribute( $value );
            }
        }

        return $value;
    }

    /**
     * @return array
     */
    public static function getAliases()
    {
        return array_merge( self:: $aliases, static::$aliases );
    }

    /**
     * Sets model value basing on alias key
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    public function setAttribute( $key, $value )
    {
        $aliases    = static::getAliases();
        $aliasedKey = Arr::get( $aliases, $key );

        if ( is_array( $aliasedKey ) ) {

            $metaKey = Arr::get( $aliasedKey, 'meta' );

            if ( $metaKey ) {
                $this->updateMeta( $metaKey, $value );

                return $this;
            }
        }

        return parent::setAttribute(
            $aliasedKey ? $aliasedKey : $key,
            $value
        );
    }
}
