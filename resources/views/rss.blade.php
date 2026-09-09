<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0">
    <channel>
        <title>{{ $siteName }} — Insights</title>
        <link>{{ $siteUrl }}/insights</link>
        <description>Latest insights from {{ $siteName }}</description>
        @foreach($posts as $post)
            <item>
                <title>{{ $post->title }}</title>
                <link>{{ $siteUrl }}/insights/{{ $post->slug }}</link>
                <guid>{{ $siteUrl }}/insights/{{ $post->slug }}</guid>
                <pubDate>{{ $post->published_at?->toRfc2822String() }}</pubDate>
                <description>{{ $post->excerpt }}</description>
            </item>
        @endforeach
    </channel>
</rss>
