<?php
/**
 * Video adapter for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2022 André Elvan
 */

namespace SpaceCatNinja\ImagerXVideoAdapter\Services;

use CraftCms\Cms\Asset\Elements\Asset;
use SpaceCatNinja\ImagerXVideoAdapter\Adapters\Video;

/**
 * VideoLoaderService
 *
 * @author    SpaceCatNinja
 * @package   ImagerXVideoAdapter
 * @since     1.0.0
 */
class VideoLoaderService
{
    public function loadVideo(Asset|string $asset, array $opts = []): ?Video
    {
        return new Video($asset, $opts);
    }
}
