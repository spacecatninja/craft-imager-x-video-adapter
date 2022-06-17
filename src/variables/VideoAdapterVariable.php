<?php
/**
 * Video adapter for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2022 André Elvan
 */

namespace spacecatninja\imagerxvideoadapter\variables;

use craft\elements\Asset;
use spacecatninja\imagerxvideoadapter\adapters\Video;
use spacecatninja\imagerxvideoadapter\VideoAdapter;

/**
 * VideoAdapterVariable Variable
 *
 * @author    SpaceCatNinja
 * @package   ImagerXVideoAdapter
 * @since     1.0.0
 */

class VideoAdapterVariable
{
    public function load(Asset|string $asset, array $opts = []): ?Video
    {
        return VideoAdapter::getInstance()->loader->loadVideo($asset, $opts);
    }
}
