<?php print "<?xml version='1.0' encoding='utf-8'?>"; ?>
<urlset
        xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
>
    @foreach($items as $item)
        <url>
            <?php
            $params = [
                'postTypeSlug' => cleanPostTypeSlug($item->getTable()),
                'year' => $item->year,
                'month' => $item->month,
            ];

            if(getLocale() == \App\Models\Language::getDefault('slug')){
                $route = 'Accio.SEO.postType.posts';
            }else{
                $route = 'Accio.SEO.postType.posts.lang';
            }
            ?>
            <loc>{{route($route,$params)}}</loc>
        </url>
    @endforeach
</urlset>