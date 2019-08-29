<?php

namespace UnderScorer\ORM\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use UnderScorer\ORM\Builders\PostBuilder;
use UnderScorer\ORM\Eloquent\Model;
use UnderScorer\ORM\Traits\Aliases;
use WP_Post;

/**
 * Class Post
 *
 * @package UnderScorer\ORM\WP
 *
 * @property int           ID
 * @property int           authorID
 * @property string        title
 * @property string        content
 * @property string        excerpt
 * @property string        commentStatus
 * @property string        status
 * @property string        type
 * @property string        contentFiltered
 * @property string        parentID
 * @property string        guid
 * @property string        mimeType
 * @property string        commentCount
 * @property int           menuOrder
 * @property Carbon        createdAt
 * @property Carbon        postDateGmt
 * @property Carbon        updatedAt
 * @property Carbon        post_modified_gmt
 * @property User          author
 * @property Comment[]     comments
 * @property PostMeta[]    meta
 * @property ThumbnailMeta thumbnail
 * @property Attachment    attachment
 */
class Post extends Model
{

    use WithMeta, Aliases;

    /**
     * @var string
     */
    const POST_TYPE = 'post';

    /**
     * @var string
     */
    const CREATED_AT = 'post_date';


    /**
     * @var string
     */
    const UPDATED_AT = 'post_modified';

    /**
     * @var array
     */
    protected static $aliases = [
        'title'           => 'post_title',
        'content'         => 'post_content',
        'contentFiltered' => 'post_content_filtered',
        'excerpt'         => 'post_excerpt',
        'slug'            => 'post_name',
        'type'            => 'post_type',
        'mimeType'        => 'post_mime_type',
        'url'             => 'guid',
        'authorID'        => 'post_author',
        'parentID'        => 'post_parent',
        'createdAt'       => 'post_date',
        'updatedAt'       => 'post_modified',
        'status'          => 'post_status',
        'commentStatus'   => 'comment_status',
        'commentCount'    => 'comment_count',
        'postDateGmt'     => 'post_date_gmt',
    ];

    /**
     * @var string
     */
    protected $primaryKey = 'ID';

    /**
     * @var string
     */
    protected $metaRelation = PostMeta::class;

    /**
     * @var string
     */
    protected $metaForeignKey = 'post_id';

    /**
     * @var array
     */
    protected $attributes = [
        'post_type' => 'post',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'post_date',
        'post_date_gmt',
        'post_modified',
        'post_modified_gmt',
    ];

    /**
     * @var string
     */
    protected $postType = 'post';


    /**
     * @var array
     */
    protected $fillable = [
        'post_content',
        'post_title',
        'post_excerpt',
        'post_type',
        'to_ping',
        'pinged',
        'post_content_filtered',
    ];

    /**
     * Filter by post type
     *
     * @param        $query
     * @param string $type
     *
     * @return mixed
     */
    public function scopeType( $query, $type = 'post' )
    {
        return $query->where( 'post_type', '=', $type );
    }

    /**
     * Filter by post status
     *
     * @param        $query
     * @param string $status
     *
     * @return mixed
     */
    public function scopeStatus( $query, $status = 'publish' )
    {
        return $query->where( 'post_status', '=', $status );
    }

    /**
     * Filter by post author
     *
     * @param      $query
     * @param null $author
     *
     * @return mixed
     */
    public function scopeAuthor( $query, $author = null )
    {
        if ( $author ) {
            return $query->where( 'post_author', '=', $author );
        }

        return null;
    }

    /**
     * Get comments from the post
     *
     * @return HasMany
     */
    public function comments()
    {
        return $this->hasMany( Comment::class, 'comment_post_ID' );
    }

    /**
     * @param string $taxonomy
     *
     * @return TermTaxonomy[] | Collection
     */
    public function taxonomy( string $taxonomy )
    {
        return $this->taxonomies()->where( 'taxonomy', '=', $taxonomy )->get();
    }

    /**
     * @return BelongsToMany
     */
    public function taxonomies()
    {
        $pivotTable = $this->getConnection()->db->prefix . 'term_relationships';

        return $this->belongsToMany( TermTaxonomy::class, $pivotTable, 'object_id', 'term_taxonomy_id' );
    }

    /**
     * Attaches provided array of terms into post instance
     *
     * @param string $taxonomy
     * @param array  $terms Array of terms with "name" and "slug" keys
     *
     * @return void
     */
    public function addTerms( string $taxonomy, array $terms )
    {
        foreach ( $terms as $term ) {

            $name = $term[ 'name' ];
            $slug = $term[ 'slug' ];

            /**
             * @var Term $term
             */
            $term = Term::query()->firstOrCreate( [
                'name' => $name,
                'slug' => $slug,
            ] );

            /**
             * @var TermTaxonomy $termTaxonomy Relation between term and taxonomy
             */
            $termTaxonomy = TermTaxonomy::query()->firstOrCreate(
                [
                    'term_taxonomy_id' => $term->term_id,
                    'term_id'          => $term->term_id,
                    'taxonomy'         => $taxonomy,
                ]
            );

            // Attach created term taxonomy into post instance
            $this->taxonomies()->attach( $termTaxonomy );

        }
    }

    /**
     * @return HasMany
     */
    public function attachment()
    {
        return $this->children()->where( 'post_type', 'attachment' );
    }

    /**
     * @return HasMany
     */
    public function children()
    {
        return $this->hasMany( static::class, 'post_parent' );
    }

    /**
     * @param array $options
     *
     * @return bool
     */
    public function save( array $options = [] )
    {
        $preWpPost = new WP_Post( (object) $this->original );

        $didExist = $this->exists;

        if ( $didExist ) {
            do_action( 'pre_post_update', $this->ID, $preWpPost );
        }

        $result = parent::save( $options );
        $wpPost = $this->toWpPost();

        if ( $result && $didExist ) {
            do_action( 'edit_post', $this->ID, $wpPost );
            do_action( 'post_updated', $this->ID, $wpPost, $preWpPost );
        }

        do_action( 'save_post', $this->ID, $wpPost, $didExist );
        do_action( 'wp_insert_post', $this->ID, $wpPost, $didExist );

        clean_post_cache( $this->ID );

        return $result;
    }

    /**
     * @return WP_Post
     */
    public function toWpPost(): WP_Post
    {
        return new WP_Post( (object) $this->toArray() );
    }

    /**
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo( User::class, 'post_author' );
    }

    /**
     * @return BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo( static::class, 'post_parent' );
    }

    /**
     * Returns posts thumbnail
     *
     * @return HasOne
     */
    public function thumbnail()
    {
        return $this->hasOne( ThumbnailMeta::class, 'post_id' )
                    ->where( 'meta_key', '_thumbnail_id' );
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param \Illuminate\Database\Query\Builder $query
     *
     * @return PostBuilder
     */
    public function newEloquentBuilder( $query )
    {
        $builder = new PostBuilder( $query );

        if ( static::POST_TYPE ) {
            return $builder->where( 'post_type', '=', static::POST_TYPE );
        }

        return $builder;
    }

    /**
     * @return Builder
     */
    public function newQuery()
    {
        return static::POST_TYPE ?
            parent::newQuery()->where( 'post_type', '=', static::POST_TYPE ) :
            parent::newQuery();
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->getConnection()->db->posts;
    }

    /**
     * Returns currently queried post
     *
     * @return static|null
     */
    public static function current()
    {
        $ID = get_the_ID();

        /**
         * @var static $model
         */
        $model = static::query()->find( $ID );

        return $ID ? $model : null;
    }

    /**
     * This method acts as pure annotation for IDEs
     *
     * @return PostBuilder
     */
    public static function query()
    {
        /**
         * @var PostBuilder $query
         */
        $query = parent::query();

        return $query;
    }

    /**
     * @return void
     */
    protected static function boot()
    {
        // Combines child and parent aliases
        if ( ! empty( static::$aliases ) ) {
            self::$aliases = static::$aliases + self::$aliases;
        }

        parent::boot();
    }

}
