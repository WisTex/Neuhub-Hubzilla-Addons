<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="{{$urlRoot}}/{{$xsl}}"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
{{foreach $urls as $url}}
    <url>
        <loc>{{$urlRoot}}/{{$url}}</loc>
        <xhtml:link rel="alternate" hreflang="en" href="{{$urlRoot}}/{{$url}}" />
        <lastmod>{{$timeUTC}}</lastmod>
        <changefreq>daily</changefreq>
    </url>	
{{/foreach}}
</urlset>