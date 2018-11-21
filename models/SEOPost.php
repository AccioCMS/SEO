<?php

namespace Plugins\Accio\SEO\Models;


use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Accio\App\Traits\TranslatableTrait;

class SEOPost extends Model{
    use Cachable,
        TranslatableTrait;
    /**
     * @var string table name
     */
    protected $table = "accio_seo_posts_data";

    /**
     * @var string primary ID name
     */
    protected $primaryKey = "postDataID";

    public $casts = [
        'title' => 'object',
        'description' => 'object',
        'facebookTitle' => 'object',
        'facebookDescription' => 'object',
        'facebookMediaID' => 'object',
        'twitterTitle' => 'object',
        'twitterDescription' => 'object',
        'twitterMediaID' => 'object',
        'isIndex' => 'object',
        'isFollow' => 'object',
        'canonicalURL' => 'object',
    ];
}