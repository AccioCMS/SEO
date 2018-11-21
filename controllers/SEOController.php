<?php

namespace Plugins\Accio\SEO\Controllers;

use App\Models\Language;
use App\Models\Media;
use App\Models\MenuLink;
use App\Models\PostType;
use Illuminate\Http\Request;
use App\Http\Controllers\MainPluginsController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Plugins\Accio\SEO\Models\SEOPost;
use Plugins\Accio\SEO\Models\SEOSettings;

class SEOController extends MainPluginsController{


    /**
     * Store SEO settings in DB
     *
     * @param Request $request data from frontend
     * @return array response data
     */
    public function store(Request $request){
        // truncate table
        $truncate = SEOSettings::truncate();

        if($truncate){
            $tmp = [];
            foreach ($request->all() as $belongsTo => $group){
                foreach ($group as $key => $value){
                    // check if $value is array or object
                    if(is_object($value) || is_array($value)){
                        // if value is post, tag or category object get only the specific IDs
                        $objectKeys = ['postsIgnoredInSitemap','categoriesIgnoredInSitemap', 'tagsIgnoredInSitemap'];
                        if(in_array($key, $objectKeys)){
                            if(count($value)){
                                $value = SEOSettings::getIDsFromSettingsValue($key, $value);
                            }else{
                                $value = "[]";
                            }
                        }else{
                            $value = json_encode($value);
                        }

                    }
                    $tmp[] = ["belongsTo" => $belongsTo, 'key' => $key, 'value' => $value];
                }
            }

            // insert data in DB
            $seo = SEOSettings::insert($tmp);

            // Write robots.txt file
            $writeRobots = $this->writeRobotsTXT($request->all()['robots']['content']);

            if($seo){
                return $this->response('Data saved');
            }
        }

        return $this->response('Data couldn\'t be saved. Please try again later', 500);
    }


    /**
     * Write robots.txt content
     *
     * @param $content
     * @return bool
     */
    public function writeRobotsTXT($content){
        // Ensure robots.txt file exists
        $robotsPath = base_path('robots.txt');
        if(File::put($robotsPath, $content)){
            return true;
        }
        return false;
    }

    /**
     * @return array all data of seo settings table
     */
    public function getAll(){
        $data = SEOSettings::all();
        $languages = Language::all();
        $defaultLangSlug = Language::getDefault()->slug;

        $postTypes = PostType::all();
        $postTypeSlugs = array_keys($postTypes->keyBy("slug")->toArray());

        $result = [];
        foreach ($data as $row){
            if(!isset($result[$row->belongsTo])){
                $result[$row->belongsTo] = [];
            }

            $value = json_decode($row->value);
            if(!is_object($value) && !is_array($value)){
                $value = $row->value;
            }
            $result[$row->belongsTo][$row->key] = $value;
        }

        // create arrays for new post type
        foreach($postTypeSlugs as $postTypeSlug){
            if (!array_key_exists($postTypeSlug, $result)){
                $result[$postTypeSlug] = [
                    "title" => null,
                    "description" => null,
                    "robots" => 1,
                ];
            }
        }

        $menuLinksTmp = MenuLink::all();
        $menuLinks = [];
        foreach($menuLinksTmp as $key => $menuLink){
            $menuLinkKey = "menu_link_".$menuLink->menuLinkID;
            if (!array_key_exists($menuLinkKey, $result)){
                $result[$menuLinkKey] = [
                    "title" => null,
                    "description" => null,
                    "robots" => 1,
                ];
            }
            $menuLinks[$key] = $menuLink->toArray();
            $menuLinks[$key]["label"] = $menuLink->label;
            $menuLinks[$key]["settingsKey"] = $menuLinkKey;
        }

        $tmp = [];
        $translatableKeys = ["title", "description", "robots"];
        foreach ($result as $key => $item){
            $tmp[$key] = [];

            foreach ($item as $itemKey => $itemValue){
                $val = (in_array($itemKey, $translatableKeys)) ? $this->makeValueForEachLang($languages, $itemValue) : $itemValue;
                $tmp[$key][$itemKey] = $val;
            }
        }
        $result = $tmp;

        return [
            "data" => $result,
            "postTypes" => $postTypes,
            "menuLinks" => $menuLinks,
            "languages" => $languages,
            "defaultLangSlug" => $defaultLangSlug,
        ];
    }

    /**
     * Make a value in the data array for each languages using language slug as key for the data array
     *
     * @param object $languages
     * @param array $data
     * @return array
     */
    private function makeValueForEachLang($languages, $data){
        if(!$data){
            $data = [];
        }
        $data = (array) $data;
        foreach ($languages as $lang){
            if(!array_has($data, $lang->slug)){
                $data[$lang->slug] = "";
            }
        }

        return $data;
    }

    /**
     * @param $lang
     * @param $postID
     * @param $postType
     * @return array
     */
    public function details($lang, $postID, $postType){
        $response = [
            'data' => [],
            'media' => []
        ];

        $seoData = SEOPost::where('belongsToID', $postID)->where('belongsTo', $postType)->first();

        if(!$seoData){
            return $response;
        }

        $media = [];
        if($seoData->facebookMediaID){
            $facebookImage = Media::where('mediaID',$seoData->facebookMediaID)->get();
            if($facebookImage && $facebookImage->count()){
                $media['plugin_accio_seo_facebook_image_'.$lang] = Media::where('mediaID',$seoData->facebookMediaID)->get();
            }
        }

        if ($seoData->twitterMediaID){
            $twitterImage = Media::where('mediaID',$seoData->twitterMediaID)->get();
            if($twitterImage && $twitterImage->count()){
                $media['plugin_accio_seo_twitter_image_'.$lang] = Media::where('mediaID',$seoData->twitterMediaID)->get();
            }
        }

        if($seoData){
            $response['data'] = $seoData;
            $response['media'] = $media;
        }

        return $response;
    }
}