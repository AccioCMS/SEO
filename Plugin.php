<?php

namespace Plugins\Accio\SEO;

use App\Models\Media;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Accio\App\Interfaces\PluginInterface;
use Accio\App\Traits\PluginTrait;
use Accio\Support\Facades\Meta;
use Mockery\Exception;
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

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register(){
        $this->settings = SEOSettings::getAllSettings();

        $this->redirectManager();

        // Saved Event
        Event::listen('post:stored', function ($data, $model) {
            $this->store($data, $model);
        });

        // Archiving event (when post does'nt exist in the main database)
        Event::listen('post:updated:archiving', function ($data, $post) {
            // create store task
            Task::create('Accio_SEO_post', 'store', $post, ['data' => $data]);
        });

        // When post is archived
        Event::listen('post:archived', function ($post) {
            $this->manageTasks($post);
        });

        // When posts are deleted, delete seo data too
        Event::listen('post:deleted', function ($post) {
            SEOPost::where("belongsToID", $post->postID)->where("belongsTo", $post->getTable())->delete();
            Task::create('Accio_SEO_post', 'delete', $post, ['postType' => $post->getTable()]);
        });

        $this->setMetaTags();
    }

    /**
     * Redirect urls as defined in Plugin page
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
                    if($currentURL == $explodeLink[0]){
                        Header("Location: ".$explodeLink[1], true, 301);
                        exit;
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
        }
        return $this;
    }


    /**
     * Set twitter meta data
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
     * @param array $data from request
     * @param object $model saved data ( post object )
     */
    public function store($data, $post){
        $seoData = $data['pluginsData']['Accio_SEO_post'];

        if($seoData) {
            $tmp = $this->prepare($seoData);

            $seoPost = SEOPost::where('belongsToID', $post->postID)->where('belongsTo', $data['postType'])->first();
            if (!$seoPost){
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
            }else{
                // create store task
                Task::create('Accio_SEO_post','store', $post, ['postType' => $data['postType'], 'data' => $data]);
            }
        }else{
            $post->noty("error", "SEO data not received! Please check SEO Plugin Panel");
        }
    }


    /**
     * Used to transfer all seo data into archive database, if the archive is empty.
     * Creates and Updates new records of seo Data
     *
     * @param object $post
     */
    private function manageTasks($post){
        $seoDataTable = (new SEOPost())->getTable();
        
        $hasData = DB::connection('mysql_archive')->table($seoDataTable)->count();
        // IF no data in archive
        if(!$hasData){
            // transfer all data from main DB to the archive
            $allData = DB::connection('mysql')->table($seoDataTable)->get();
            $tmp = [];
            foreach ($allData as $key => $seoData){
                $tmp[$key] = (array) $seoData;
            }

            DB::connection('mysql_archive')->table($seoDataTable)->insert($tmp);
        }else{
            // loop throw all tasks
            foreach(Task::get() as $task){
                if($task->belongsTo == 'Accio_SEO_post'){

                    // task of this plugin
                    if($task->type == 'store'){
                        $postType = $task->additional['data']['postType'];

                        $pluginData = $task->additional['data']['pluginsData']['Accio_SEO_post'];
                        
                        // if seo data already exists
                        $doesPostExist = DB::connection('mysql_archive')->table($seoDataTable)->where('belongsToID',$post->postID)->where('belongsTo',$postType)->count();

                        $seoDataOBJ = DB::connection('mysql_archive')->table($seoDataTable);
                        
                        // post seo data is being updated
                        if($doesPostExist){
                            $seoDataOBJ->where('belongsToID',$post->postID)->where('belongsTo',$postType)->update($this->encode($this->prepare($pluginData)));
                        }else{
                            $seoData = $this->encode($this->prepare($pluginData));
                            $seoData['belongsToID'] = $post->postID;
                            $seoData['belongsTo'] = $postType;
                            $seoDataOBJ->insert($seoData);
                        }
                    }elseif($task->type == 'delete'){
                        $postType = $task->additional['postType'];
                        DB::connection('mysql_archive')->table($seoDataTable)->where('belongsTo',$postType)->where('belongsToID',$post->postID)->delete();
                    }
                }
            }
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
     * Query and set post meta data
     *
     * @param $belongsTo
     * @param $belongsToID
     *
     * @return $this
     */
    public function setPostMetaData($belongsTo, $belongsToID){
        $modelMetaDataObj = new SEOPost();
        $this->postMetaData = $modelMetaDataObj->where('belongsTo', $belongsTo)
            ->where('belongsToID', $belongsToID)->first();

        if($this->postMetaData){
            $this->postMetaData->facebookMedia = null;
            $this->postMetaData->twitterMedia = null;

            if($this->postMetaData->facebookMediaID){
                $this->postMetaData->facebookMedia = Media::find($this->postMetaData->facebookMediaID);
            }

            if($this->postMetaData->twitterMediaID){
                $this->postMetaData->twitterMedia = Media::find($this->postMetaData->twitterMediaID);
            }
        }

        return $this;
    }

    /**
     * Get post meta data
     * @param $key
     * @return string|null
     */
    public function getPostMetaData($key){
        if($this->postMetaData && $this->postMetaData->$key){
            return $this->postMetaData->$key;
        }
        return;
    }



    /**
     * Check if a particular setting exist
     *
     * @param string $belongsTo
     * @param string $key
     * @return string|null
     */
    private function getSettings($belongsTo, $key){
        return (isset($this->settings[$belongsTo][$key]) ? $this->settings[$belongsTo][$key] : null);
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
                $table->string("value", 45)->nullable();
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
}