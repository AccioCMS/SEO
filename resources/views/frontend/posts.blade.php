<?php print "<?xml version='1.0' encoding='utf-8'?>"; ?>
<urlset
        xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
>

@foreach($items as $item)
    <?php
    // exclude no-index posts
    if(isset($postMetaData[$item->postID])){
        if(!$postMetaData[$item->postID]['isIndex']->{App::getLocale()}){
            continue;
        }
    }
    ?>
    <url>
        <loc>{{ $item->href }}</loc>
        <lastmod>{{ $item->updated_at->toAtomString()}}</lastmod>

        @if($item->hasFeaturedImage())
        <image:image>
            <image:loc>
                {{$item->featuredImageURL()}}
            </image:loc>

            @if($item->featuredImage->title)
            <image:title>
                {{$item->featuredImage->title}}
            </image:title>
            @endif
            <image:publication_date>{{ $item->featuredImage->created_at->toAtomString()}}</image:publication_date>
        </image:image>
        @endif

        @if($item->hasFeaturedVideo())
        <video:video>
            <video:thumbnail_loc>
                {{url($item->featuredVideo->url)}}
            </video:thumbnail_loc>
            @if($item->featuredVideo->title)
            <video:title>
                {{$item->featuredVideo->title}}
            </video:title>
            @endif
            <video:publication_date>{{ $item->featuredVideo->created_at->toAtomString()}}</video:publication_date>
            <video:category/>
        </video:video>
        @endif
    </url>
@endforeach
</urlset>