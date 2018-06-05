<?php
/**
 * Created by PhpStorm.
 * User: sopa
 * Date: 14/02/2018
 * Time: 11:41 AM
 */

namespace Plugins\Accio\SEO\Controllers;


use App\Models\Category;
use App\Models\Language;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostType;
use App\Models\Tag;
use App\Models\Theme;
use App\Models\User;
use function foo\func;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Accio\App\Traits\PluginTrait;
use Plugins\Accio\SEO\Models\PositionManager;
use Plugins\Accio\SEO\Models\SEOSettings;
use Datetime;

class SitemapsController extends Controller
{
    use PluginTrait;

    /**
     * Plugin settings
     *
     * @var array
     */
    private $settings;

    /**
     * If sitemaps are active
     *
     * @var bool $isActive
     */
    private $isActive = false;

    /**
     * List all sitemaps
     *
     * @var array
     */
    private static $sitemaps = [];

    /**
     * Set db connection
     * @var string
     */
    private $connection = 'mysql';

    /**
     * Max number of entries allowed per sitemap
     * @var
     */
    private $maxEntries = 0;

    /**
     * Post SEO meta data
     * @var array $postMetaData
     */
    private $postMetaData;

    /**
     * SitemapsController constructor.
     */
    public function __construct()
    {
        $this->settings = SEOSettings::getAllSettings();
        if($this->settings) {
            $this->isActive = $this->settings['sitemap']['isActive'];
            $this->maxEntries = $this->settings['sitemap']['maxEntriesAllowed'];
            if (env('DB_ARCHIVE')) {
                $this->connection = "mysql_archive";
            }
        }
    }

    /**
     * Sitemaps's index.
     * It lists sitemaps
     *
     * @return $this
     */
    public function index()
    {
        if(!$this->isActive){
            return error404();
        }
        $languages = Language::where('isVisible', true)->where('isDefault', false)->get();

        self::addSitemap(route('Accio.SEO.daily'));
        self::addSitemap(route('Accio.SEO.categories'));

        // Create sitemaps for other languages
        foreach($languages as $language){
            self::addSitemap(route('Accio.SEO.daily.lang',['lang' => $language->slug]));
            self::addSitemap(route('Accio.SEO.categories.lang',['lang' => $language->slug]));
        }

        // tags and categories for each post type
        $postTypes = PostType::where('isVisible', true)->get();
        foreach($postTypes as $postType){
            if($postType->slug !== config('project.default_post_type')){
                self::addSitemap(route('Accio.SEO.postType', ['postTypeSlug' => cleanPostTypeSlug($postType->slug)]));
                if($postType->hasCategories) {
                    self::addSitemap(route('Accio.SEO.categoriesByPostType', ['postTypeSlug' => cleanPostTypeSlug($postType->slug)]));
                }

                // Create sitemaps for other languages
                foreach($languages as $language){
                    self::addSitemap(route('Accio.SEO.postType.lang', ['lang' => $language->slug,'postTypeSlug' => cleanPostTypeSlug($postType->slug)]));
                    if($postType->hasCategories) {
                        self::addSitemap(route('Accio.SEO.categoriesByPostType.lang', ['lang' => $language->slug, 'postTypeSlug' => cleanPostTypeSlug($postType->slug)]));
                    }
                }
            }
        }

        self::addSitemap(route('Accio.SEO.tags'));
        self::addSitemap(route('Accio.SEO.authors'));

        return $this->xml(self::getSitemaps(),"index");
    }

    /**
     * List posts published x time ago.
     * This helps to speed up indexing process
     *
     * @return \Illuminate\Http\Response|Response
     */
    public function daily(){
        if(!$this->isActive){
            return error404();
        }
        $posts = Post::on($this->connection)
            ->published()
            ->with("featuredImage")
            ->with("featuredVideo")
            ->where("published_at", '>=',new DateTime('-9 weeks'))
            ->whereNotIn('postID', $this->getIgnoredPosts())
            ->limit($this->maxEntries)
            ->get();

        $this->getMetaData('post_articles', $posts->pluck('postID')->toArray());

        return $this->xml($posts, 'posts');
    }

    /**
     * Get post SEO meta data
     *
     * @param string $postTypeSlug
     * @param array $postID
     *
     * @return array
     */
    private function getMetaData($postTypeSlug, $postID){
        $this->postMetaData = PositionManager::where('belongsTo', $postTypeSlug)
            ->whereIn('belongsToID', $postID)
            ->get()
            ->keyBy('belongsToID')
            ->toArray();
        return $this->postMetaData;
    }
    public function categories(){
        if(!$this->isActive){
            return error404();
        }

        // We need Post type
        if(request('postTypeSlug')){
            $postType = PostType::where('slug', 'post_'.request('postTypeSlug'))->first();
            if(!$postType){
                return error404();
            }
        }else{
            $postType = PostType::where('slug', config('project.default_post_type'))->first();
        }

        $categories = Category::on($this->connection)
            ->where('postTypeID', $postType->postTypeID)
            ->whereNotIn('categoryID', $this->getIgnoredCategories())
            ->visible()
            ->with('featuredImage')
            ->get();

        $this->getMetaData('category', $categories->pluck('categoryID')->toArray());

        return $this->xml($categories, 'categories');
    }

    public function category(){
        if(!$this->isActive){
            return error404();
        }
        // get category
        $category = Category::on($this->connection)->where('slug->'.App::getLocale(),request('categorySlug'))->first();
        if(!$category){
            return error404();
        }

        // We need Post type
        $postType = PostType::where('postTypeID', $category->postTypeID)->first();

        // Set post table
        $postsObj = (new Post())->setConnection($this->connection);
        $postsObj->setTable($postType->slug);

        // Get all months of posts
        $archives = $postsObj->selectRaw('year(created_at) year, month(created_at) month')
            ->published()
            ->groupBy('year', 'month')
            ->orderByRaw('min(created_at)')
            ->get();

        return $this->xml($archives, 'category');
    }

    public function posts(){
        if(!$this->isActive){
            return error404();
        }
        // get category
        $category = Category::on($this->connection)->where('slug->'.App::getLocale(),request('categorySlug'))->first();
        if(!$category){
            return error404();
        }
        // We need Post type
        $postType = PostType::where('postTypeID', $category->postTypeID)->first();

        // get posts
        $postsObj = (new Post())->setConnection($this->connection);
        $posts = $postsObj->setTable($postType->slug)
            ->join('categories_relations','categories_relations.belongsToID',$postType->slug.'.postID')
            ->where('categories_relations.categoryID', '=', $category->categoryID)
            ->with("featuredImage")
            ->with("featuredVideo")
            ->whereNotIn($postType->slug.'.postID', $this->getIgnoredPosts())
            ->published()
            ->date(request(request('year'), request('month')))
            ->limit($this->maxEntries)
            ->orderBy($postType->slug.'.published_at','DESC')
            ->get();

        $this->getMetaData($postType->slug, $posts->pluck('postID')->toArray());

        return $this->xml($posts, 'posts');
    }

    public function postType(){
        if(!$this->isActive){
            return error404();
        }
        // We need Post type
        $postType = PostType::where('slug', 'post_'.request('postTypeSlug'))->first();
        if(!$postType){
            return error404();
        }

        // Set post table
        $postsObj = (new Post())->setConnection($this->connection);
        $postsObj->setTable($postType->slug);

        // Get all months of posts
        $archives = $postsObj->selectRaw('year(created_at) year, month(created_at) month')
            ->published()
            ->groupBy('year', 'month')
            ->orderByRaw('min(created_at)')
            ->get();

        return $this->xml($archives, 'post_type');
    }

    public function postsByPostType(){
        if(!$this->isActive){
            return error404();
        }

        // We need Post type
        $postType = PostType::where('slug', 'post_'.request('postTypeSlug'))->first();
        if(!$postType){
            return error404();
        }

        // get posts
        $postsObj = (new Post())->setConnection($this->connection);
        $posts = $postsObj->setTable($postType->slug)
            ->with("featuredImage")
            ->with("featuredVideo")
            ->whereNotIn($postType->slug.'.postID', $this->getIgnoredPosts())
            ->published()
            ->date(request(request('year'), request('month')))
            ->limit($this->maxEntries)
            ->orderBy($postType->slug.'.published_at','DESC')
            ->get();

        $this->getMetaData($postType->slug, $posts->pluck('postID')->toArray());

        return $this->xml($posts, 'posts');
    }

    public function tags(){
        if(!$this->isActive){
            return error404();
        }
        $tags = Tag::on($this->connection)
            ->whereNotIn('tagID', $this->getIgnoredTags())
            ->with('featuredImage')
            ->limit($this->maxEntries)
            ->get();

        $this->getMetaData('tags', $tags->pluck('tagID')->toArray());

        return $this->xml($tags, 'tags');
    }

    /**
     * List all users that have published posts
     * @return \Illuminate\Http\Response|Response
     */
    public function authors(){
        if(!$this->isActive){
            return error404();
        }

        $users = User::whereHas('posts', function ($query){
            $query->published();
        })
            ->with('profileimage')
            ->get();

        $this->getMetaData('author', $users->pluck('userID')->toArray());

        return $this->xml($users, 'authors');
    }

    /**
     * Set xml response
     *
     * @param $items
     * @param string $bladeTemplate
     * @return \Illuminate\Http\Response|Response
     */
    private function xml($items, $bladeTemplate = 'items'){
        $maxEntries = $this->maxEntries;
        $languages = Language::all();
        $postMetaData = $this->postMetaData;

        return response()
            ->view('Accio.SEO::frontend.'.$bladeTemplate, compact('items', 'maxEntries', 'languages', 'postMetaData'))
            ->header('Content-Type', 'text/xml');
    }
    /**
     * Add a sitemap
     *
     * @param string $url
     *
     */
    public static function addSitemap($url){
        self::$sitemaps[] = url($url);
    }

    /**
     * @return array
     */
    public static function getSitemaps(){
        return self::$sitemaps;
    }

    /**
     * Get ignored posts
     * @return array
     */
    private  function getIgnoredPosts(){
        $ignoredPosts = [];
        if($this->settings['post']['postsIgnoredInSitemap']){
            $getIgnoredPosts = json_decode($this->settings['post']['postsIgnoredInSitemap'], true);
            if(isset($getIgnoredPosts['post_articles'])){
                $ignoredPosts = $getIgnoredPosts['post_articles'];
            }
        }

        return $ignoredPosts;
    }

    /**
     * Get ignored categories
     * @return array
     */
    private function getIgnoredCategories(){
        $ignoredCategories = [];
        if($this->settings['categories']['categoriesIgnoredInSitemap']){
            $getIgnoredCategories = json_decode($this->settings['categories']['categoriesIgnoredInSitemap'], true);
            if(isset($getIgnoredCategories['post_articles'])){
                $ignoredCategories = $getIgnoredCategories['post_articles'];
            }
        }
        return $ignoredCategories;
    }
    /**
     * Get ignored tags
     * @return array
     */
    public function getIgnoredTags(){
        $ignoredTags = [];
        if($this->settings['tags']['tagsIgnoredInSitemap']){
            $getIgnoredTags = json_decode($this->settings['tags']['tagsIgnoredInSitemap'], true);
            if(isset($getIgnoredTags['post_articles'])){
                $ignoredTags = $getIgnoredTags['post_articles'];
            }
        }
        return $ignoredTags;
    }

}