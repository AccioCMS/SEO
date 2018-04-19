<?php print "<?xml version='1.0' encoding='utf-8'?>"; ?>
<urlset
        xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
>
    @foreach($items as $item)
        <?php
        // exclude no-index categories
        if(isset($postMetaData[$item->authorID])){
            if(!$postMetaData[$item->authorID]['isIndex']->{App::getLocale()}){
                continue;
            }
        }
        ?>
        <url>
            <loc>{{ $item->href }} {{$item->postID  }}</loc>
            <lastmod>{{ $item->updated_at->toAtomString()}}</lastmod>
            @if($item->profileImage)
                <image:image>
                    <image:loc>
                        {{$item->avatar()}}
                    </image:loc>

                    @if($item->profileImage->title)
                        <image:title>
                            {{$item->profileImage->title}}
                        </image:title>
                    @endif
                    <image:publication_date>{{ $item->profileImage->created_at->toAtomString()}}</image:publication_date>
                </image:image>
            @endif
        </url>
    @endforeach
</urlset>