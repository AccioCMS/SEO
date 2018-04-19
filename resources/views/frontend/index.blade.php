<?php print "<?xml version='1.0' encoding='utf-8'?>"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($items as $item)
<url>
    <loc>{{ $item }}</loc>
</url>
@endforeach
</urlset>
