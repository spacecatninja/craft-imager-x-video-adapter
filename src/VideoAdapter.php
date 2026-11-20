<?php
/**
 * Video adapter for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2022 André Elvan
 */

namespace SpaceCatNinja\ImagerXVideoAdapter;

use CraftCms\Cms\Plugin\Plugin;
use CraftCms\Cms\Plugin\PluginSettings;
use CraftCms\Cms\Twig\Variables\CraftVariable;
use SpaceCatNinja\ImagerX\Adapters\Adapters;
use SpaceCatNinja\ImagerXVideoAdapter\Adapters\Video;
use SpaceCatNinja\ImagerXVideoAdapter\Twig\VideoAdapterVariable;

/**
 * @author    SpaceCatNinja
 * @package   ImagerXVideoAdapter
 * @since     1.0.0
 */
class VideoAdapter extends Plugin
{
    public function register(): void
    {
        parent::register();

        $this->app->singleton(Settings::class, fn() => $this->getSettings());
    }

    public function boot(Adapters $adapters, Settings $settings): void
    {
        // Register adapter in Imager X, for each file format
        foreach ($settings->addFileFormats as $format) {
            $adapters->register($format, Video::class);
        }

        // Register our variable, craft.videoadapter
        CraftVariable::macro('videoadapter', static fn() => app(VideoAdapterVariable::class));
    }

    protected static function createSettings(): ?PluginSettings
    {
        return new Settings();
    }
}
