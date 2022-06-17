<?php
/**
* Video adapter for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2022 André Elvan
  */

namespace spacecatninja\imagerxvideoadapter\models;

use craft\base\Model;

class Settings extends Model
{
    public bool $cacheEnabled = true;
    public int|bool|string $cacheDuration = false;
    
    public string $defaultFormat = 'jpg';
    public int|float|string $defaultTime = '25%';
    public array $addFileFormats = ['mp4','mov','avi'];

    /**
     * @var array An array of options that is passed directly to FFMpeg's create method.
     *
     * Example options:
     *            
     *   'ffmpeg.binaries'  => '/opt/local/ffmpeg/bin/ffmpeg',
     *   'ffprobe.binaries' => '/opt/local/ffmpeg/bin/ffprobe',
     *   'timeout'          => 3600, // The timeout for the underlying process
     *   'ffmpeg.threads'   => 12,   // The number of threads that FFMpeg should use
     *            
     * Refer to https://github.com/PHP-FFMpeg/PHP-FFMpeg#ffmpeg for more info.           
     */
    public array $ffmpegConfig = [];
}
