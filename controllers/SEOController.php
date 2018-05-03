<?php

namespace Plugins\Accio\SEO\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use App\Http\Controllers\MainPluginsController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Plugins\Accio\SEO\Models\SEOPost;
use Plugins\Accio\SEO\Models\SEOSettings;

class SEOController extends MainPluginsController{


    /**
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
            $seo = DB::table('accio_seo_settings')->insert($tmp);

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
        $result = [];
        foreach ($data as $row){
            if(!isset($result[$row->belongsTo])){
                $result[$row->belongsTo] = [];
            }

            $value = json_decode($row->value);
            $objectKeys = ['postsIgnoredInSitemap','categoriesIgnoredInSitemap', 'tagsIgnoredInSitemap'];
            if(is_object($value)){
                if(in_array($row->key, $objectKeys)){
                    $value = SEOSettings::getObjectsFromIDs($row->key, $value);
                }
            }else if(is_array($value) && in_array($row->key, $objectKeys) && !count($value)) {
                $value = json_decode("{}");
            }else if(!is_object($value) && !is_array($value)){
                $value = $row->value;
            }

            $result[$row->belongsTo][$row->key] = $value;
        }
        return $result;
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

        // or query it on archive
        if(!$seoData && env("DB_ARCHIVE")){
            $seoObj = new SEOPost();
            $seoObj->setConnection('mysql_archive');
            $seoData = $seoObj->where('belongsToID', $postID)->where('belongsTo', $postType)->first();
        }

        if(!$seoData){
            return $response;
        }

        $media = [];
        $facebookImage = Media::where('mediaID',$seoData->facebookMediaID)->get();
        if($facebookImage && $facebookImage->count()){
            $media['plugin_accio_seo_facebook_image_'.$lang] = Media::where('mediaID',$seoData->facebookMediaID)->get();
        }

        $twitterImage = Media::where('mediaID',$seoData->twitterMediaID)->get();
        if($twitterImage && $twitterImage->count()){
            $media['plugin_accio_seo_twitter_image_'.$lang] = Media::where('mediaID',$seoData->twitterMediaID)->get();
        }

        if($seoData){
            $response['data'] = $seoData;
            $response['media'] = $media;
        }

        return $response;
    }
}