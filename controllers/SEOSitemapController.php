<?php

namespace Plugins\Accio\SEO\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostType;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Plugins\Accio\SEO\Models\SEOSettings;


class SEOSitemapController extends Controller {
    private $seoSettings = [];
    private $paginateNR = 20000;
    private $generalSitemapActive = true;
    private $categorySitemap = true;
    private $tagSitemap = true;
    private $authorSitemap = true;
    private $postTypeSitemap = [];

    /**
     * SEOSitemapController constructor.
     *
     * Set instances (Seo General Settings)
     */
    public function __construct(){
        $this->seoSettings = SEOSettings::all();
        if($this->seoSettings){
            $this->paginateNR = $this->seoSettings->where("key", "maxEntriesAllowed")->first()->value;
            $this->generalSitemapActive = $this->seoSettings->where("key", "isActive")->first()->value;
            $this->categorySitemap = $this->seoSettings->where("key", "categoriesSitemap")->first()->value;
            $this->tagSitemap = $this->seoSettings->where("key", "tagsSitemap")->first()->value;
            $this->authorSitemap = $this->seoSettings->where("key", "authorSitemap")->first()->value;
            $this->postTypeSitemap = json_decode($this->seoSettings->where("key", "postTypesInSitemap")->first()->value);
        }
    }

    /**
     * Create index sitemap with links for all apps
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        if(!$this->generalSitemapActive){
            return error404();
        }
        // get allowed post types
        $postTypes = PostType::select("slug")->whereIn("postTypeID", $this->postTypeSitemap)->get()->toArray();
        $appList = array_flatten($postTypes);

        // add categories if allowed
        if($this->categorySitemap){
            $appList[] = 'categories';
        }

        // add tags if allowed
        if($this->tagSitemap){
            $appList[] = 'tags';
        }

        // construct array with xml data (data to be displayed in xml)
        $xmlData = [];
        foreach ($appList as $app){
            $data = DB::table($app);
            if(!in_array($app,['categories', 'tags'])){
                $data = $data->where('published_at', '<=', date('Y-m-d H:i:s'));
            }
            $data = $data->select('updated_at')->orderBy('updated_at', 'desc')->paginate($this->paginateNR);
            $lastDateMod = (new Carbon($data->first()->updated_at))->tz('UTC')->toAtomString();

            for($i = 1; $i <= $data->lastPage(); $i++){
                $xmlData[] = [
                    "url" => route("Accio.SEO.sitemap.single", [$app, $i]),
                    "lastMod" => $lastDateMod
                ];
            }
        }

        // add author in xml data if it is allowd
        if($this->authorSitemap){
            $lastUserMod = User::orderBy('updated_at', 'desc')->whereNotNull("updated_at")->select("updated_at")->first()['updated_at'];
            $xmlData[] = [
                "url" => route("Accio.SEO.sitemap.single", ["authors", 1]),
                "lastMod" => (new Carbon($lastUserMod))->tz('UTC')->toAtomString()
            ];
        }

        return response()->view('sitemap', [
            'xmlData' => $xmlData,
            "styleUrl" => URL::asset('public/css/sitemap/main-sitemap.xsl')
        ])->header('Content-Type', 'text/xml');
    }

    /**
     * Create single sitemap list for each app
     *
     * @param $slug
     * @param $page
     * @return \Illuminate\Http\Response
     */
    public function single($slug, $page){
        if($slug == "categories"){
            $data = Category::orderBy('updated_at', 'desc')->paginate($this->paginateNR, ['*'], 'page', $page);
        }elseif ($slug == "tags"){
            $data = Tag::orderBy('updated_at', 'desc')->paginate($this->paginateNR, ['*'], 'page', $page);
        }elseif ($slug == "authors"){

            $data = [];
            $users = User::all();
            foreach ($users as $author){
                if($author->hasDataInPostsType()){
                    $data[] = $author;
                }
            }

        }else{
            $data = (new Post())->setTable($slug)
                ->where('published_at', '<=', date('Y-m-d H:i:s'))
                ->orderBy('updated_at', 'desc')->paginate($this->paginateNR, ['*'], 'page', $page);
        }

        $xmlData = [];

        foreach ($data as $item){
            if($item->updated_at){
                $xmlData[] = [
                    "url" => $item->href,
                    "lastMod" => (new Carbon($item->updated_at))->tz('UTC')->toAtomString()
                ];
            }
        }

        return response()->view('sitemap', [
            'xmlData' => $xmlData,
            "styleUrl" => URL::asset('public/css/sitemap/main-sitemap.xsl')
        ])->header('Content-Type', 'text/xml');
    }

}