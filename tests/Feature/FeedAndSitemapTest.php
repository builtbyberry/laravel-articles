<?php

test('renders feed.xml as a valid Atom 1.0 feed', function () {
    $response = $this->get('/feed.xml');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toStartWith('application/atom+xml');

    $body = $response->getContent();

    expect($body)->toContain('<feed xmlns="http://www.w3.org/2005/Atom">');
    expect($body)->toContain('<title>Example — Articles</title>');
    expect($body)->toContain('<link rel="self" type="application/atom+xml" href="https://example.test/feed.xml"/>');
    expect($body)->toContain('<link rel="alternate" type="text/html" href="https://example.test/articles"/>');
    expect($body)->toContain('https://example.test/articles/published-article');
    expect($body)->not->toContain('sample-article');
    expect($body)->toContain('</feed>');
});

test('feed.xml is well-formed XML', function () {
    $body = $this->get('/feed.xml')->getContent();

    $previous = libxml_use_internal_errors(true);
    $doc = simplexml_load_string($body);
    $errors = libxml_get_errors();
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    expect($doc)->not->toBeFalse();
    expect($errors)->toBeEmpty();
});

test('renders sitemap.xml with article URLs', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toStartWith('application/xml');

    $body = $response->getContent();

    expect($body)->toContain('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
    expect($body)->toContain('<loc>https://example.test/articles</loc>');
    expect($body)->toContain('/articles/published-article');
    expect($body)->not->toContain('/articles/sample-article');
});

test('sitemap.xml is well-formed XML', function () {
    $body = $this->get('/sitemap.xml')->getContent();

    $previous = libxml_use_internal_errors(true);
    $doc = simplexml_load_string($body);
    $errors = libxml_get_errors();
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    expect($doc)->not->toBeFalse();
    expect($errors)->toBeEmpty();
});
