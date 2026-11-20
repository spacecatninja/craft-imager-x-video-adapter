<?php
/**
 * Video adapter for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2022 André Elvan
 */

namespace SpaceCatNinja\ImagerXVideoAdapter\Twig;

use CraftCms\Cms\Asset\Elements\Asset;
use SpaceCatNinja\ImagerXVideoAdapter\Adapters\Video;
use SpaceCatNinja\ImagerXVideoAdapter\Services\VideoLoaderService;

/**
 * The `craft.videoadapter` variable.
 *
 * @author    SpaceCatNinja
 * @package   ImagerXVideoAdapter
 * @since     1.0.0
 */
class VideoAdapterVariable
{
    public function load(Asset|string $asset, array $opts = []): ?Video
    {
        return app(VideoLoaderService::class)->loadVideo($asset, $opts);
    }
}
