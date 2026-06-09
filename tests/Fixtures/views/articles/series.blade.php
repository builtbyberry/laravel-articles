<!DOCTYPE html>
<html>
<head><title>{{ $series['title'] }}</title></head>
<body>
<h1 data-series="{{ $series['slug'] }}">{{ $series['title'] }}</h1>
<ul>
@foreach ($series['articles'] as $article)
    <li data-part="{{ $article['seriesPart'] ?? '' }}">{{ $article['slug'] }}</li>
@endforeach
</ul>
</body>
</html>
