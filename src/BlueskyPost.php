<?php

namespace Spatie\BlueskyNotificationChannel;

use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Traits\Tappable;
use Spatie\BlueskyNotificationChannel\Embeds\Embed;
use Spatie\BlueskyNotificationChannel\Facets\Facet;

class BlueskyPost
{
    use Conditionable;
    use Macroable;
    use Tappable;

    private bool $automaticallyResolvesEmbeds = true;

    private bool $automaticallyResolvesFacets = true;

    private function __construct(
        public string $text = '',
        public array $facets = [],
        public ?Embed $embed = null,
        public array $languages = [],
        public ?string $embedUrl = null,
    ) {}

    public static function make(): static
    {
        return new static;
    }

    public function text(?string $text): static
    {
        $this->text = $text ?? '';

        return $this;
    }

    public function embed(?Embed $embed = null): static
    {
        $this->embed = $embed;

        return $this;
    }

    public function embedUrl(string $embedUrl): static
    {
        $this->embedUrl = $embedUrl;

        return $this;
    }

    public function facet(Facet $facet): static
    {
        $this->facets[] = $facet;

        return $this;
    }

    /** @param  Facet[]  $facets */
    public function facets(array $facets): static
    {
        $this->facets = array_merge($this->facets, $facets);

        return $this;
    }

    /** @see https://www.docs.bsky.app/blog/create-post#setting-the-posts-language */
    public function language(string|array $language): static
    {
        $this->languages = Arr::wrap($language);

        return $this;
    }

    public function withoutAutomaticEmbeds(): static
    {
        $this->automaticallyResolvesEmbeds = false;

        return $this;
    }

    public function automaticallyResolvesEmbeds(): bool
    {
        return $this->automaticallyResolvesEmbeds;
    }

    public function withoutAutomaticFacets(): static
    {
        $this->automaticallyResolvesFacets = false;

        return $this;
    }

    public function automaticallyResolvesFacets(): bool
    {
        return $this->automaticallyResolvesFacets;
    }

    public function toArray(): array
    {
        return array_filter([
            'text' => $this->text,
            'facets' => array_map(
                callback: fn (array|Facet $facet) => \is_array($facet) ? $facet : $facet->toArray(),
                array: $this->facets,
            ),
            'embed' => $this->embed?->toArray(),
            'langs' => $this->languages,
        ]);
    }
}
