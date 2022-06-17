Imager X Video Adapter plugin for Craft CMS
===

A plugin for extracting and transforming images from videos using Imager X.   
Also, an example of [how to make a custom file adapter for Imager X](https://imager-x.spacecat.ninja/extending.html#file-adapters).

## Requirements

This plugin requires Imager X 4.1.0+, Craft CMS 4.0.0+, PHP 8.0+ and [a working ffmpeg installation](https://ffmpeg.org/download.html). 

## Installation

To install the plugin, follow these instructions:

1. Install with composer via `composer require spacecatninja/imager-x-video-adapter` from your project directory.
2. Install the plugin in the Craft Control Panel under Settings → Plugins, or from the command line via `./craft install/plugin imager-x-video-adapter`.

---

## Usage

Install and configure the adapter as described below. 

You can now extract frames simply by adding a video file to Imager's `transformImage` method:

```
{% set transform = craft.imagerx.transformImage(myVideoAsset, { width: 200 }) %}
```

You can pass configuration parameters to the adapter, using the `adapterParams` transform parameter:

```
{% set transform = craft.imagerx.transformImage(myVideoAsset, { width: 200, adapterParams: { time: '50%' } }) %}
```

You can also create the adapter as a separate step, with the advantage of being able to better control settings, 
and inspecting the video.

```
{% set video = craft.videoadapter.load(asset, { format: 'png' }) %}

Video duration: {{ video.getDuration() }}<br>

{% do video.setTime(video.getDuration()/2) %}
{% set thumbAtCenter = craft.imagerx.transformImage(video, { width: 600 }) %}
```

### Caveat regarding time

Sync issues have always plagued video manipulation, and this plugin is no exception. If you try to 
extract "the last frame" of a video, for instance by passing in `100%` to the `time` parameter, you'll 
most likely get an error. Even if you've used `video.getDuration()` and pass that in, ffmpeg most often 
won't be able to extract a frame. 

The only solution is backing up half a second or so, which will usually get you a usable frame.

### Auto generation

The video adapter works perfectly with the [auto generation functionality](https://imager-x.spacecat.ninja/usage/generate.html) 
in Imager X. The only thing you need to do, is add the video file extensions that 
the video adapter is configured to register for (see `addFileFormats` below), to the 
list of safe file formats to transform, using the config setting ['safeFileFormats'](https://imager-x.spacecat.ninja/configuration.html#safefileformats-array).

```
'safeFileFormats' => ['jpg', 'jpeg', 'gif', 'png', 'mp4', 'mov', 'avi']
```

## Configuring

You can configure the adapter by creating a file in your config folder called
`imager-x-video-adapter.php`, and override settings as needed.

### ffmpegConfig [array]
Default: `[]`  
Under the hood, this adapter uses the excellent [PHP-FFMPEG](https://github.com/PHP-FFMpeg/PHP-FFMpeg)
library. It tries to auto-detect your ffmpeg and ffprobe installation, and have reasonable defaults.
But if it isn't able to detect your runtimes, you'll need to configure it manually.

This config setting takes an array which is passed directly to [FFMpeg/FFProbe's create method](https://github.com/PHP-FFMpeg/PHP-FFMpeg#ffmpeg).
Here's an example with all the available settings:

```
'ffmpegConfig' => [
    'ffmpeg.binaries'     => '/usr/local/bin/ffmpeg',
    'ffprobe.binaries'    => '/usr/local/bin/ffprobe',
    'timeout'             => 3600, // The timeout for the underlying process
    'ffmpeg.threads'      => 12,   // The number of threads that FFMpeg should use
    'temporary_directory' => '/var/ffmpeg-tmp'
]
```

### defaultFormat [string]
Default: `'jpg'`  
Sets the default format of the temporary bitmap image that the adapter generates. You 
can of course transform this to whatever format you'd like later, using Imager.

### defaultTime [int|float|string]
Default: `'25%'`  
Sets the default time in the video that the thumbnail should be extracted from. It can be either
an integer or float value representing seconds, or a string with a percentage value of the relative 
time in the video.

### addFileFormats [array]
Default: `['mp4','mov','avi']`  
Specifies the file extensions that should be added to use this adapter when passed to Imager. Make 
sure that any file formats added, can actually be used by your ffmpeg installation.

### cacheEnabled [bool]
Default: `true`  
Enables/disables caching of generated images. Only disable this if testing, as it
will seriously impact performance.

### cacheDuration [bool|int]
Default: `false`  
Sets the cacheDuration that's used if `cacheEnabled` is `true`. By default, forever. 
Clearing the Imager runtime cache will also clear this cache.

---

Price, license and support
---
The plugin is released under the MIT license. It requires Imager X, which is a commercial 
plugin [available in the Craft plugin store](https://plugins.craftcms.com/imager-x). 
