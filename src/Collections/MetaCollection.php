<?php

namespace UnderScorer\ORM\Collections;

use Illuminate\Database\Eloquent\Collection;

/**
 * Class MetaCollection
 * @package UnderScorer\ORM\Collections
 */
class MetaCollection extends Collection
{
    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset( $name )
    {
        return ! is_null( $this->__get( $name ) );
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function __get( $key )
    {
        if ( isset( $this->items ) && count( $this->items ) ) {
            $meta = $this->first( function ( $meta ) use ( $key ) {
                return $meta->meta_key === $key;
            } );

            return $meta ? $meta->meta_value : null;
        }

        return null;
    }
}
