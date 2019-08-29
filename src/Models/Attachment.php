<?php

namespace UnderScorer\ORM\Models;

/**
 * Class Attachment
 * @package UnderScorer\ORM\Models
 *
 * @property string alt
 * @property string url
 * @property string type
 * @property string description
 * @property string caption
 * @property string altText
 */
class Attachment extends Post
{

    const POST_TYPE = 'attachment';

    /**
     * @var array
     */
    protected static $aliases = [
        'title'       => 'post_title',
        'url'         => 'guid',
        'type'        => 'post_mime_type',
        'description' => 'post_content',
        'caption'     => 'post_excerpt',
        'altText'     => [ 'meta' => '_wp_attachment_image_alt' ],
    ];
    /**
     * @var array
     */
    protected $appends = [
        'title',
        'url',
        'type',
        'description',
        'caption',
        'altText',
    ];

}
