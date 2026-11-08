<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Shared image rules for media-library models: allowed MIME types and WebP
 * size conversions so the storefront never ships an original upload.
 *
 * @mixin Model
 */
trait HasImageConversions
{
    use InteractsWithMedia;

    /**
     * Raster formats only — SVG uploads are refused because they can carry scripts.
     *
     * @var list<string>
     */
    public const IMAGE_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/avif'];

    /**
     * @param  list<string>  $collections
     * @param  array<string, int>  $widths  conversion name => max width in px
     * @param  list<string>  $responsive  conversions that also get a srcset
     */
    protected function addWebpConversions(array $collections, array $widths, array $responsive = []): void
    {
        foreach ($widths as $name => $width) {
            $this->addMediaConversion($name)
                ->performOnCollections(...$collections)
                ->format('webp')
                ->width($width)
                ->quality(82)
                ->withResponsiveImages(in_array($name, $responsive, true));
        }
    }
}
