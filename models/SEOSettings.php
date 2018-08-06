<?php

namespace Plugins\Accio\SEO\Models;


use Accio\App\Traits\CacheTrait;
use Accio\App\Traits\CollectionTrait;
use App\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SEOSettings extends Model{
    use CacheTrait, CollectionTrait;

    /**
     * @var string table name
     */
    protected $table = "accio_seo_settings";

    public static function getIDsFromSettingsValue($key, $value){
        $tmp = [];
        foreach ($value as $objKey => $object){
            if (!isset($tmp[$objKey])){
                $tmp[$objKey] = [];
            }
            foreach ($object as $objData){
                if($key == 'postsIgnoredInSitemap'){
                    $tmp[$objKey][] = $objData['postID'];
                }elseif ($key == 'categoriesIgnoredInSitemap'){
                    $tmp[$objKey][] = $objData['categoryID'];
                }elseif ($key == 'tagsIgnoredInSitemap'){
                    $tmp[$objKey][] = $objData['tagID'];
                }
            }
        }
        return json_encode($tmp);
    }

    /**
     * Used to get the object from their specific IDs (Posts, Categories and Tags)
     * Used in SEOController@getAll
     *
     * @param string $key of the SEO settings table
     * @param string $value of the SEO settings table
     * @return array|mixed
     */
    public static function getObjectsFromIDs($key, $value){
        if(env("DB_ARCHIVE")){
            $DB = DB::connection("mysql_archive");
        }else{
            $DB = DB::connection("mysql");
        }

        if(count(get_object_vars($value))){
            $tmp = [];
            foreach ($value as $objKey => $object){
                if($key == 'postsIgnoredInSitemap'){
                    $tmp[$objKey] = Language::filterRows($DB->table($objKey)->whereIn("postID", $object)->get(), false);
                }elseif($key == 'categoriesIgnoredInSitemap'){
                    $tmp[$objKey] = Language::filterRows(DB::table("categories")->whereIn("categoryID", $object)->get(), false);
                }elseif($key == 'tagsIgnoredInSitemap'){
                    $tmp[$objKey] = DB::table("tags")->whereIn("tagID", $object)->get();
                }
            }
            return $tmp;
        }
        return json_decode("{}");
    }

    /**
     * Get all meta settings.
     *
     * @return array
     * @throws \Exception
     */
    public static function getAllSettings(){
        $settings = [];
        $SEOSettings = self::cache();
        foreach($SEOSettings as $setting){
            $settings[$setting['belongsTo']][$setting['key']] = $setting['value'];
        }
        return $settings;
    }
}