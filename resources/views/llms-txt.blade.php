# {{ $siteName }}

> @if($jobTitle){{ $jobTitle }}. @endif{{ $shortBio ?: 'Professional portfolio site for Lindsey Wegmann.' }}@if($location) Based in {{ $location }}.@endif

This site is the public portfolio for {{ $siteName }}. It includes published case studies, insights articles, and background information intended for recruiters, collaborators, and prospective clients. Draft or preview URLs are not listed here.

## Main pages

@foreach($corePages as $page)
- [{{ $page['title'] }}]({{ $page['url'] }})@if(!empty($page['description'])): {{ $page['description'] }}@endif

@endforeach
@if($projects->isNotEmpty())
## Work

@foreach($projects as $project)
- [{{ $project['title'] }}]({{ $project['url'] }})@if(!empty($project['description'])): {{ $project['description'] }}@endif

@endforeach
@endif
@if($posts->isNotEmpty())
## Insights

@foreach($posts as $post)
- [{{ $post['title'] }}]({{ $post['url'] }})@if(!empty($post['description'])): {{ $post['description'] }}@endif

@endforeach
@endif
@if($pages->isNotEmpty())
## Additional pages

@foreach($pages as $page)
- [{{ $page['title'] }}]({{ $page['url'] }})@if(!empty($page['description'])): {{ $page['description'] }}@endif

@endforeach
@endif
## Optional

- [Privacy policy]({{ url('/privacy') }}): Site privacy and data handling information.
- [RSS feed]({{ url('/feed.xml') }}): Latest published insights posts.
- [Sitemap]({{ url('/sitemap.xml') }}): Machine-readable list of indexable URLs.
@if($linkedinUrl)
- [LinkedIn]({{ $linkedinUrl }}): Professional profile and background.
@endif
