<?php

namespace BuiltByBerry\LaravelArticles\Http\Controllers;

use BuiltByBerry\LaravelArticles\Feed\AtomFeedBuilder;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class FeedController extends Controller
{
    public function __construct(private AtomFeedBuilder $feed) {}

    public function __invoke(): Response
    {
        return response($this->feed->build(), 200)
            ->header('Content-Type', 'application/atom+xml; charset=UTF-8');
    }
}
