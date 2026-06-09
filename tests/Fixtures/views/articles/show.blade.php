<!DOCTYPE html>
<html>
<head><title>{{ $meta['title'] ?? 'Article' }}</title></head>
<body>
<article data-slug="{{ $slug }}">{!! $html !!}</article>
</body>
</html>
