<?php print "<?xml version='1.0' encoding='utf-8'?>"; ?>
<urlset
        xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
>
@foreach($items as $item)
    <?php
    // exclude no-index tags
    if(isset($postMetaData[$item->tagID])){
        if(!$postMetaData[$item->tagID]['isIndex']->{App::getLocale()}){
            continue;
        }
    }
    ?>
    <url>
        <loc>{{ $item->href }} {{$item->tagID}}</loc>
        <lastmod>{{ $item->updated_at->toAtomString()}}</lastmod>
        @if($item->featuredImage)
            <image:image>
                <image:loc>
                    {{url($item->featuredImage->url)}}
                </image:loc>

                @if($item->featuredImage->title)
                    <image:title>
                        {{$item->featuredImage->title}}
                    </image:title>
                @endif
                <image:publication_date>{{ $item->featuredImage->created_at->toAtomString()}}</image:publication_date>
            </image:image>
        @endif
    </url>
@endforeach
</urlset>