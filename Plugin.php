<?php

namespace Plugins\Accio\SEO;

use App\Models\Media;
use App\Models\Task;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Accio\App\Interfaces\PluginInterface;
use Accio\App\Traits\PluginTrait;
use Accio\Support\Facades\Meta;
use Plugins\Accio\SEO\Models\SEOPost;
use Plugins\Accio\SEO\Models\SEOSettings;
use Symfony\Component\Console\Command\Command;

class Plugin implements PluginInterface {
    use PluginTrait;

    /**
     * Saves post data
     * @var object $modelMetaData
     */
    private $modelMetaData;

    /**
     * SEO Settings
     * @var array $settings
     */
    private $settings;

    /**
     * The model where we will get meta data from
     * @var object $model
     */
    private $model;

    public function __construct(){

    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @throws \Exception
     */
    public function register(){
        $this->settings = SEOSettings::getAllSettings();

        $this->redirectManager();

        // Saved Event
        Event::listen('post:stored', function ($data, $model) {
            $this->store($data, $model);
        });

        // When posts are deleted, delete seo data too
        Event::listen('post:deleted', function ($post) {
            SEOPost::where("belongsToID", $post->postID)->where("belongsTo", $post->getTable())->delete();
            Task::create('Accio_SEO_post', 'delete', $post, ['postType' => $post->getTable()]);
        });

        $this->setMetaTags();
    }

    /**
     * It makes an url ready to be checked for regex.
     *
     * @param string $url
     * @return null|string|string[]
     */
    private function makeRegexURL(string $url){
        $url = '/'.str_replace(['/'],['\/'],$url).'/';
        $finalURL = preg_replace('/([a-zA-Z0-9])\./','$1\.$2',$url);

        if($finalURL){
            return $finalURL;
        }

        return null;
    }
    /**
     * Redirect urls as defined in Plugin page
     *
     * @return $this
     */
    private function redirectManager(){

        Event::listen('system:boot', function (){
            $redirectContent = $this->getSettings('redirectManager', 'content');
            $rows = explode("\n", $redirectContent);
            $currentURL = request()->GetRequestUri();

            foreach($rows as $link){
                $explodeLink = explode(' ', $link);
                if(count($explodeLink) == 2){
                    $fromUrl = $explodeLink[0];
                    $toUrl = $explodeLink[1];

                    if($currentURL == $fromUrl){
                        Header("Location: ".$toUrl, true, 301);
                        exit;
                    }else{ //try regex
                        if($fromUrl = $this->makeRegexURL($fromUrl)) {
                            if (preg_match($fromUrl, $currentURL)) {

                                preg_match_all($fromUrl, $currentURL, $patternMatches);
                                preg_match_all('/\$[0-9]/', $toUrl, $replacementMatches);

                                if(count($patternMatches) && count($replacementMatches)) {
                                    $paramsMatched = true;
                                    foreach($replacementMatches[0] as $match){
                                        $removeSign = str_replace('$', '', $match);
                                        // match & replace patterns
                                        if (isset($patternMatches[$removeSign]) && isset($patternMatches[$removeSign][0])) {
                                            $toUrl = str_replace($match, $patternMatches[$removeSign][0], $toUrl);
                                        }else{
                                            $paramsMatched = false;
                                            break;
                                        }
                                    }
                                    if($paramsMatched){
                                        Header("Location: ".$toUrl, true, 301);
                                        exit;
                                    }
                                }
                            }
                        }


                    }
                }
            }
        });
        return $this;
    }

    /**
     * Set plugin meta tags
     *
     * @return $this
     */
    private function setMetaTags(){
        // Saved Event
        Event::listen('meta:add', function($model = null){
            if($model){
                $this->model = $model;
                $this
                    ->setPostMetaData($this->model->getTable(), $this->model->getKey())
                    ->setTitle()
                    ->setMeta()
                    ->setDescription()
                    ->setOpenGraph()
                    ->setTwiterMeta();
            }
        });

        return $this;
    }

    /**
     * Set meta title
     *
     * @return $this
     */
    private function setTitle(){
        // Title
        if ($this->getPostMetaData('title')) {
            Meta::setTitle($this->getPostMetaData('title'));
        }elseif($this->getSettings($this->model->getTable(), 'title')) {
            Meta::setTitle(Meta::replaceWildcards($this->getSettings($this->model->getTable(), 'title'),  $this->model->getTable()));
        }elseif($this->model->getTable() == "menu_links"){
            $title = $this->getSettings("menu_link_".$this->model->menuLinkID, 'title');
            if($title){
                Meta::setTitle(Meta::replaceWildcards($title,  $this->model->getTable()));
            }
        }
        return $this;
    }

    private function setMeta(){
        $belongsTo = $this->model->getTable();
        if($this->model->getTable() == "menu_links"){
            $belongsTo = "menu_link_".$this->model->menuLinkID;
        }

        $areAllowed = SEOSettings::all()->where("belongsTo", $belongsTo)->where("key", "robots")->first();

        if(!$areAllowed){
            Meta::set("robots", "noindex, nofollow");
        }else{
            if($this->model->getTable() == "menu_links"){
                Meta::set("robots", "follow index");
            }else{
                if(!$this->getPostMetaData('isIndex') && !$this->getPostMetaData('isFollow')){
                    Meta::set("robots", "noindex, nofollow");
                }else{
                    $hasMeta = false;
                    if(!$this->getPostMetaData('isIndex')){
                        $hasMeta = true;
                        Meta::set("robots", "noindex");
                    }

                    if(!$this->getPostMetaData('isFollow')){
                        $hasMeta = true;
                        Meta::set("robots", "nofollow");
                    }

                    if(!$hasMeta){
                        Meta::set("robots", "follow index");
                    }
                }
            }

        }

        return $this;
    }

    /**
     * Set meta description
     *
     * @return $this
     */
    private function setDescription(){
        if ($this->getPostMetaData('description')) {
            Meta::set("description", $this->getPostMetaData('description'));
        }elseif($this->getSettings($this->model->getTable(),'description')) {
            Meta::set("description", Meta::replaceWildcards($this->getSettings($this->model->getTable(), 'description'), $this->model->getTable()));
        }elseif($this->model->getTable() == "menu_links"){
            $description = $this->getSettings("menu_link_".$this->model->menuLinkID, 'description');
            if($description){
                Meta::set("description", Meta::replaceWildcards($description,  $this->model->getTable()));
            }
        }
        return $this;
    }


    /**
     * Set twitter meta data
     *
     * @return $this
     */
    private function setTwiterMeta(){
        // twitter:title
        if ($this->getPostMetaData('twitterTitle')) {
            Meta::set("twitter:title", $this->getPostMetaData('twitterTitle'));
        }elseif($this->getSettings($this->model->getTable(), 'title')) {
            Meta::set("twitter:title", Meta::replaceWildcards($this->getSettings($this->model->getTable(), 'title'),  $this->model->getTable()));
        }

        // twitter:description
        if ($this->getPostMetaData('twitterDescription')) {
            Meta::set("twitter:description", $this->getPostMetaData('twitterDescription'));
        }elseif($this->getSettings($this->model->getTable(),'description')) {
            Meta::set("twitter:description", Meta::replaceWildcards($this->getSettings($this->model->getTable(),'description'), $this->model->getTable()));
        }

        // twitter:image
        if ($this->getPostMetaData('twitterMedia')) {
            Meta::set("twitter:image", asset($this->getPostMetaData('twitterMedia')->url));
            if ($this->getPostMetaData('twitterMedia')->description) {
                Meta::set("twitter:image:alt", $this->getPostMetaData('twitterMedia')->description);
            }
        }

        // twitter:title
        if (Meta::get('twitter:title') || Meta::get('twitter:description') || Meta::get('twitter:image')) {
            Meta::set('twitter:card', 'summary');
        }
        return $this;
    }

    /**
     * Set open graph meta data
     *
     * @return $this
     */
    private function setOpenGraph(){
        // og:title
        if ($this->getPostMetaData('facebookTitle')) {
            Meta::set("og:title", $this->getPostMetaData('facebookTitle'), "property");
        }elseif($this->getSettings($this->model->getTable(), 'title')) {
            Meta::set("og:title", Meta::replaceWildcards($this->getSettings($this->model->getTable(), 'title'), $this->model->getTable()), "property");
        }

        // og:description
        if ($this->getPostMetaData('facebookDescription')) {
            Meta::set("og:description", $this->getPostMetaData('facebookDescription'), "property");
        }elseif($this->getSettings($this->model->getTable(), 'description')) {
            Meta::set("og:description", Meta::replaceWildcards($this->getSettings($this->model->getTable(), 'description'), $this->model->getTable()), "property");
        }

        // og:url
        if ($this->getPostMetaData('canonicalURL')) {
            Meta::set("og:url",$this->getPostMetaData('canonicalURL'), "property");
            Meta::setCanonical($this->getPostMetaData('canonicalURL'));
        }

        // og:description
        Meta::set("og:type", "website", "property", false);

        // og:image
        if ($this->getPostMetaData('facebookMedia')) {
            Meta::set("og:image", asset($this->getPostMetaData('facebookMedia')->url), "property");
        }
        return $this;
    }

    /**
     * Store post data
     *
     * @param $data
     * @param $post
     */
    public function store($data, $post){
        $seoData = "";
        if(isset($data['pluginsData'])){
            $seoData = $data['pluginsData']['Accio_SEO_post'];
        }

        if($seoData) {
            $tmp = $this->prepare($seoData);

            $seoPost = SEOPost::where('belongsToID', $post->postID)->where('belongsTo', $data['postType'])->first();
            if(!$seoPost){
                $seoPost = new SEOPost();
            }

            $seoPost->belongsToID = $post->postID;
            $seoPost->belongsTo = $data['postType'];
            $seoPost->title = $tmp['title'];
            $seoPost->description = $tmp['description'];
            $seoPost->facebookTitle = $tmp['facebookTitle'];
            $seoPost->facebookDescription = $tmp['facebookDescription'];
            $seoPost->facebookMediaID = $tmp['facebookMediaID'];
            $seoPost->twitterTitle = $tmp['twitterTitle'];
            $seoPost->twitterDescription = $tmp['twitterDescription'];
            $seoPost->twitterMediaID = $tmp['twitterMediaID'];
            $seoPost->isIndex = $tmp['isIndex'];
            $seoPost->isFollow = $tmp['isFollow'];
            $seoPost->canonicalURL = $tmp['canonicalURL'];

            if (!$seoPost->save()){
                $post->noty("error", "SEO data not saved");
            }
        }else{
            $post->noty("error", "SEO data not received! Please check SEO Plugin Panel");
        }
    }


    /**
     * Object to array
     * @param array $seoData input array of objects
     * @return array multidimensional array
     */
    private function prepare($seoData){
        $tmp = [];
        foreach ($seoData as $langKey => $langValues) {
            foreach ($langValues as $key => $value) {
                if (!isset($tmp[$key])) {
                    $tmp[$key] = [];
                }
                $tmp[$key][$langKey] = $value;
            }
        }
        return $tmp;
    }

    /**
     * Encode object or array values of a array
     *
     * @param array $data
     * @return array result of the encoding
     */
    private function encode($data){
        $tmp = [];
        foreach ($data as $key => $value){
            $tmpValue = $value;
            if(is_object($value) || is_array($value)){
                $tmpValue = json_encode($value);
            }
            $tmp[$key] = $tmpValue;
        }
        return $tmp;
    }

    /**
     *  Do something after all plugins have been loaded,
     *
     * @return void
     */
    public function boot(){
    }

    /**
     * Get SEO Post data
     *
     * @param string $belongsTo
     * @param int $belongsToID
     * @return mixed
     * @throws \Exception
     */
    public function getPostData(string $belongsTo, int $belongsToID){
        // search in database
        $modelMetaDataObj = new SEOPost();
        $modelMetaData = $modelMetaDataObj
            ->where('belongsTo', $belongsTo)
            ->where('belongsToID', $belongsToID)
            ->first();

        return $modelMetaData;
    }


    /**
     * Query and set post meta data
     *
     * @param $belongsTo
     * @param $belongsToID
     * @return $this
     * @throws \Exception
     */
    public function setPostMetaData(string $belongsTo, int $belongsToID) {
        $this->modelMetaData = $this->getPostData($belongsTo,$belongsToID);

        if($this->modelMetaData){

            if($this->modelMetaData->facebookMediaID){
                $this->modelMetaData->facebookMedia = Media::find($this->modelMetaData->facebookMediaID);
            }

            if($this->modelMetaData->twitterMediaID){
                $this->modelMetaData->twitterMedia = Media::find($this->modelMetaData->twitterMediaID);
            }
        }

        return $this;
    }

    /**
     * Get post meta data
     *
     * @param $key
     * @return string|null
     */
    public function getPostMetaData($key){
        if($this->modelMetaData && isset($this->modelMetaData->$key)){
            $data = $this->modelMetaData->$key;
            $tmp = json_decode($data);
            $lang = App::getLocale();

            if(is_object($tmp)){
                if(property_exists($tmp, $lang)){
                    return $tmp->$lang;
                }
            }else{
                return $data;
            }
        }
        return null;
    }



    /**
     * Check if a particular setting exist
     *
     * @param string $belongsTo
     * @param string $key
     * @return string|null
     */
    private function getSettings($belongsTo, $key){
        if(isset($this->settings[$belongsTo][$key])){
            $data = $this->settings[$belongsTo][$key];
            $tmp = json_decode($data);
            $lang = App::getLocale();

            if(is_object($tmp)){
                if(property_exists($tmp, $lang)){
                    return $tmp->$lang;
                }
            }else{
                return $data;
            }
        }

        return null;
    }

    /**
     * @param Command $command
     * @return bool
     */
    public function install(Command $command){
        if(!Schema::hasTable('accio_seo_settings')) {
            Schema::create('accio_seo_settings', function ($table)  {
                $table->increments("settingsID");
                $table->string("belongsTo", 30);
                $table->string("key", 45);
                $table->text("value")->nullable();
            });}

        if(!Schema::hasTable('accio_seo_posts_data')) {
            Schema::create('accio_seo_posts_data', function ($table)  {
                $table->increments("postDataID");
                $table->integer("belongsToID")->unsigned();
                $table->string("belongsTo", 30);
                $table->json("title");
                $table->json("description");
                $table->json("facebookTitle");
                $table->json("facebookDescription");
                $table->json("facebookMediaID");
                $table->json("twitterTitle");
                $table->json("twitterDescription");
                $table->json("twitterMediaID");
                $table->json("isIndex");
                $table->json("isFollow");
                $table->json("canonicalURL");
                $table->timestamps();
            });
        }

        return true;
    }

    public function update(){
        return true;
    }
}