<?php

namespace UnderScorer\ORM\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use UnderScorer\ORM\Eloquent\Model;

/**
 * Class TermTaxonomy
 * @package WPK\Core\Models\WP
 *
 * @property int          term_taxonomy_id
 * @property int          term_id
 * @property string       taxonomy
 * @property string       description
 * @property int          parent
 * @property int          count
 * @property Term         term
 * @property TermTaxonomy parentTaxonomy
 * @property Post[]       posts
 */
class TermTaxonomy extends Model
{

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $primaryKey = 'term_taxonomy_id';

    /**
     * @var array
     */
    protected $fillable = [
        'term_taxonomy_id',
        'term_id',
        'taxonomy',
        'description',
        'parent',
        'count',
    ];

    /**
     * @return BelongsToMany
     */
    public function posts()
    {
        $pivotTable = $this->getConnection()->db->prefix . 'term_relationships';

        return $this->belongsToMany( Post::class, $pivotTable, 'term_taxonomy_id', 'object_id' );
    }

    /**
     * @return BelongsTo
     */
    public function parentTaxonomy()
    {
        return $this->belongsTo( static::class, 'parent' );
    }

    /**
     * @return HasOne
     */
    public function term()
    {
        return $this->hasOne( Term::class, 'term_id' );
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->getConnection()->db->prefix . 'term_taxonomy';
    }

}
