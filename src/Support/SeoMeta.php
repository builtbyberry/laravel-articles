<?php

namespace BuiltByBerry\LaravelArticles\Support;

/**
 * Per-request SEO/social meta container.
 */
class SeoMeta
{
    /**
     * @var array{
     *     title: ?string,
     *     description: ?string,
     *     ogType: string,
     *     ogImage: ?string,
     *     canonical: ?string,
     *     articleSchema: ?array<string, mixed>,
     *     articleAuthor: ?string,
     *     articlePublishedTime: ?string,
     *     articleModifiedTime: ?string,
     * }
     */
    protected array $data = [
        'title' => null,
        'description' => null,
        'ogType' => 'website',
        'ogImage' => null,
        'canonical' => null,
        'articleSchema' => null,
        'articleAuthor' => null,
        'articlePublishedTime' => null,
        'articleModifiedTime' => null,
    ];

    /**
     * @param  array<string, mixed>  $values
     */
    public function set(array $values): self
    {
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $this->data)) {
                $this->data[$key] = $value;
            }
        }

        return $this;
    }

    public function title(): ?string
    {
        return $this->data['title'];
    }

    public function description(): ?string
    {
        return $this->data['description'];
    }

    public function ogType(): string
    {
        return $this->data['ogType'];
    }

    public function ogImage(): ?string
    {
        return $this->data['ogImage'];
    }

    public function canonical(): ?string
    {
        return $this->data['canonical'];
    }

    /**
     * @return ?array<string, mixed>
     */
    public function articleSchema(): ?array
    {
        return $this->data['articleSchema'];
    }

    public function articleAuthor(): ?string
    {
        return $this->data['articleAuthor'];
    }

    public function articlePublishedTime(): ?string
    {
        return $this->data['articlePublishedTime'];
    }

    public function articleModifiedTime(): ?string
    {
        return $this->data['articleModifiedTime'];
    }

    public function isSet(): bool
    {
        return $this->data['title'] !== null
            || $this->data['description'] !== null
            || $this->data['canonical'] !== null;
    }
}
