<!DOCTYPE html>
<html>
<head><title>{{ $title ?? 'Articles' }}</title></head>
<body>
<h1>Articles Index</h1>
<ul>
@foreach ($articles as $article)
    <li>{{ $article['slug'] }}</li>
@endforeach
</ul>
</body>
</html>
