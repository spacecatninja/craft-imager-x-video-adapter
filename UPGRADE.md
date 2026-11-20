# Upgrading to Imager X Video Adapter 2 (Craft 6)

This guide lists what changed in version 2 of the video adapter for Imager X, and what you need to update in your project and custom code. It applies when upgrading from version 1 (Craft 4 or 5, and Imager X 4, 5 or 6).

Templates aren't affected: `craft.imagerx.transformImage()` with a video, the `adapterParams` transform parameter and `craft.videoadapter.load()` work as before. Projects need to move and convert the config file, if they have one. The class changes only affect custom code that uses the plugin's classes directly.

## Requirements

- Craft CMS 6.0.0 or later
- Imager X 7.0.0-beta.2 or later
- PHP 8.5 or later
- `php-ffmpeg/php-ffmpeg` 1.4.0 or later, which Composer installs. Version 1.x of this plugin allowed older versions, which don't support PHP 8.5.
- ffmpeg and ffprobe, as before
- The plugin doesn't need the Yii 2 adapter (`craftcms/yii2-adapter`).

## Upgrade steps

1. Upgrade your project to Craft 6, and Imager X to version 7.
2. Update the plugin: `composer require spacecatninja/imager-x-video-adapter:^2.0.0`.
3. If you have a config file, move it to `config/craft/imager-x-video-adapter.php`, and convert it (see below).
4. Review the breaking changes below, and update your code.

## Breaking changes

### The config file

Craft 6 reads plugin config files from `config/craft/`, as plain Laravel config. Move `config/imager-x-video-adapter.php` to `config/craft/imager-x-video-adapter.php`. If it uses the multi-environment `'*'` format, return a flat array instead, and use `env()` instead of `craft\helpers\App::env()`. The settings themselves are unchanged.

Before:

```php
use craft\helpers\App;

return [
    '*' => [
        'defaultTime' => '25%',
        'ffmpegConfig' => [
            'ffmpeg.binaries' => App::env('FFMPEG_BINARY'),
            'ffprobe.binaries' => App::env('FFPROBE_BINARY'),
        ],
    ],
];
```

After:

```php
return [
    'defaultTime' => '25%',
    'ffmpegConfig' => [
        'ffmpeg.binaries' => env('FFMPEG_BINARY'),
        'ffprobe.binaries' => env('FFPROBE_BINARY'),
    ],
];
```

Config files are loaded before Laravel knows the environment, so use `env()` for per-environment values, not `app()->environment()`.

### Namespace and classes

The plugin moved from `spacecatninja\imagerxvideoadapter` to the `SpaceCatNinja\ImagerXVideoAdapter` namespace, with Laravel-style folders.

| Before | After |
| --- | --- |
| `spacecatninja\imagerxvideoadapter\VideoAdapter` | `SpaceCatNinja\ImagerXVideoAdapter\VideoAdapter` |
| `spacecatninja\imagerxvideoadapter\models\Settings` | `SpaceCatNinja\ImagerXVideoAdapter\Settings` |
| `spacecatninja\imagerxvideoadapter\adapters\Video` | `SpaceCatNinja\ImagerXVideoAdapter\Adapters\Video` |
| `spacecatninja\imagerxvideoadapter\services\VideoLoaderService` | `SpaceCatNinja\ImagerXVideoAdapter\Services\VideoLoaderService` |
| `spacecatninja\imagerxvideoadapter\variables\VideoAdapterVariable` | `SpaceCatNinja\ImagerXVideoAdapter\Twig\VideoAdapterVariable` |

`Video` implements Imager X 7's `SpaceCatNinja\ImagerX\Adapters\ImagerAdapterInterface`, and its `$sourceModel` is a `SpaceCatNinja\ImagerX\Data\LocalSourceImageModel`. `VideoLoaderService` no longer extends `craft\base\Component`.

### Settings and the loader service

`VideoAdapter::getInstance()->getSettings()` and the plugin's `loader` component were removed. Resolve the settings and the service from the container instead.

Before:

```php
use spacecatninja\imagerxvideoadapter\VideoAdapter;

$formats = VideoAdapter::getInstance()->getSettings()->addFileFormats;
$video = VideoAdapter::getInstance()->loader->loadVideo($asset, ['time' => 2]);
```

After:

```php
use SpaceCatNinja\ImagerXVideoAdapter\Services\VideoLoaderService;
use SpaceCatNinja\ImagerXVideoAdapter\Settings;

$formats = app(Settings::class)->addFileFormats;
$video = app(VideoLoaderService::class)->loadVideo($asset, ['time' => 2]);
```
