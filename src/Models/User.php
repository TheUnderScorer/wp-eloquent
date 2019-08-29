<?php

namespace UnderScorer\ORM\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use UnderScorer\ORM\Eloquent\Model;
use UnderScorer\ORM\Traits\Aliases;
use WP_User;

/**
 * Class User
 * @package UnderScorer\ORM\WP
 *
 * @property int        ID
 * @property string     login
 * @property string     password
 * @property string     slug
 * @property string     email
 * @property string     url
 * @property Carbon     createdAt
 * @property string     user_activation_key
 * @property string     user_status
 * @property string     firstName
 * @property string     lastName
 * @property UserMeta[] meta
 */
class User extends Model
{

    use WithMeta, Aliases;

    /**
     * @var string
     */
    const CREATED_AT = 'user_registered';

    /**
     * @var static
     */
    protected static $current;

    /**
     * @var array
     */
    protected static $aliases = [
        'login'         => 'user_login',
        'password'      => 'user_pass',
        'email'         => 'user_email',
        'firstName'     => [
            'meta' => 'first_name',
        ],
        'lastName'      => [
            'meta' => 'last_name',
        ],
        'url'           => 'user_url',
        'user_nicename' => 'nicename',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $primaryKey = 'ID';

    /**
     * @var string
     */
    protected $metaRelation = UserMeta::class;

    /**
     * @var string
     */
    protected $metaForeignKey = 'user_id';

    /**
     * @var array
     */
    protected $dates = [
        'user_registered',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'firstName',
        'lastName',
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'login',
        'email',
        'nicename',
        'url',
        'password',
        'firstName',
        'lastName',
        'nickname',
    ];

    /**
     * @param string $pass
     */
    public function setUserPassAttribute( $pass ): void
    {
        $this->attributes[ 'user_pass' ] = wp_hash_password( $pass );
    }

    /**
     * @return HasMany
     */
    public function posts(): HasMany
    {
        return $this->hasMany( Post::class, 'post_author' );
    }

    /**
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany( Comment::class, 'user_id' );
    }

    /**
     * Return instance of currently logged in user
     *
     * @return static | null
     */
    public static function current()
    {
        if ( empty( self::$current ) ) {
            $userID = get_current_user_id();

            /**
             * @var static $user
             */
            self::$current = static::query()->find( $userID );
        }

        return self::$current;
    }

    /**
     * @param array $options
     *
     * @return bool
     */
    public function save( array $options = [] ): bool
    {
        $exists   = $this->exists;
        $prevData = null;

        if ( $exists ) {
            $prevData = $this->toWpUser();
        }

        $result = parent::save( $options );

        wp_cache_delete( $this->ID, 'users' );
        wp_cache_delete( $this->login, 'userlogins' );

        if ( $exists ) {
            do_action( 'profile_update', $this->ID, $prevData );
        } else {
            do_action( 'user_register', $this->ID );
        }

        return $result;
    }

    /**
     * @return WP_User
     */
    public function toWpUser()
    {
        $user = new WP_User();
        $user->init( (object) $this->toArray(), get_current_blog_id() );

        return $user;
    }

}
