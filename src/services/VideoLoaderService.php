<?php
/**
 * Video adapter for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2022 André Elvan
 */

namespace spacecatninja\imagerxvideoadapter\services;

use craft\base\Component;
use craft\elements\Asset;

use spacecatninja\imagerxvideoadapter\adapters\Video;


/**
 * VideoLoaderService
 *
 * @author    SpaceCatNinja
 * @package   ImagerXVideoAdapter
 * @since     1.0.0
 */
class VideoLoaderService extends Component
{
    public function loadVideo(Asset|string $asset, array $opts = []): ?Video
    {
        return new Video($asset, $opts);
    }
}
