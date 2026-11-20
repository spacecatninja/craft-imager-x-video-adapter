<?php
/**
 * Video adapter for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2022 André Elvan
 */

namespace SpaceCatNinja\ImagerXVideoAdapter\Adapters;

use CraftCms\Cms\Asset\Elements\Asset;
use CraftCms\Cms\Cms;
use CraftCms\Cms\Support\Config;
use CraftCms\Cms\Support\Facades\Path;
use CraftCms\Cms\Support\File;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Exception\ExceptionInterface as FFMpegException;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use SpaceCatNinja\ImagerX\Adapters\ImagerAdapterInterface;
use SpaceCatNinja\ImagerX\Data\LocalSourceImageModel;
use SpaceCatNinja\ImagerX\Exceptions\ImagerException;
use SpaceCatNinja\ImagerXVideoAdapter\Settings;

class Video implements ImagerAdapterInterface
{
    public const DURATION_CACHE_KEY = 'videoAdapterDurationCacheKey';

    public Asset|string|null $source = null;
    public LocalSourceImageModel|null $sourceModel = null;

    public int|float|string $time = '25%';
    public string $format = 'png';

    private bool $isReady = false;
    private string $path = '';
    private string $transformPath = '';

    public function __construct(Asset|string|null $asset, array $opts = [])
    {
        $settings = app(Settings::class);

        $this->format = $opts['format'] ?? $settings->defaultFormat;
        $this->time = $opts['time'] ?? $settings->defaultTime;

        if ($asset) {
            $this->load($asset);
        }
    }

    /*
     * -- Public interface methods -----------------------------------
     */

    public function getPath(): string
    {
        if (!$this->isReady) {
            $this->getVideoThumbnail();
        }

        return $this->path;
    }

    public function getTransformPath(): string
    {
        if (!$this->isReady) {
            $this->getVideoThumbnail();
        }

        return $this->transformPath;
    }

    /*
     * -- Other public methods -----------------------------------
     */

    public function load(Asset|string $asset): void
    {
        $this->source = $asset;
        $this->isReady = false;

        try {
            $this->sourceModel = new LocalSourceImageModel($asset);
        } catch (ImagerException $imagerException) {
            Log::error('An error occured when trying to open file with Video Adapter: '.$imagerException->getMessage());
            $this->sourceModel = null;
        }
    }


    public function getDuration(): int|float
    {
        $settings = app(Settings::class);

        if ($this->sourceModel === null) {
            return 0;
        }

        try {
            $this->sourceModel->getLocalCopy();
        } catch (ImagerException $imagerException) {
            Log::error('An error occured when trying to open file with Video Adapter: '.$imagerException->getMessage());

            return 0;
        }

        $sourceModel = $this->sourceModel;

        $duration = Cache::get(self::DURATION_CACHE_KEY.'_'.base64_encode($this->sourceModel->getFilePath()));

        if ($duration) {
            return $duration;
        }

        try {
            $ffprobe = FFProbe::create($settings->ffmpegConfig);
            $duration = $ffprobe->format($sourceModel->getFilePath())->get('duration');
        } catch (FFMpegException $ffmpegException) {
            Log::error('An error occured when trying to get the duration of video: '.$ffmpegException->getMessage());

            return 0;
        }

        Cache::put(self::DURATION_CACHE_KEY.'_'.base64_encode($this->sourceModel->getFilePath()), $duration, Config::durationInSeconds(Cms::config()->cacheDuration) ?: null);

        return $duration;
    }

    public function setTime(int|float|string $time): void
    {
        $this->time = $time;
        $this->isReady = false;
    }

    /*
     * -- Protected methods -----------------------------------
     */

    protected function getVideoThumbnail(): void
    {
        $settings = app(Settings::class);

        // Only once for the current source and time, since Imager calls both getTransformPath() and getPath()
        $this->isReady = true;

        if ($this->sourceModel === null) {
            return;
        }

        try {
            $this->sourceModel->getLocalCopy();
        } catch (ImagerException $imagerException) {
            Log::error('An error occured when trying to open file with Video Adapter: '.$imagerException->getMessage());
            return;
        }

        $this->transformPath = $this->sourceModel->transformPath;

        $cachePath = Path::runtime().DIRECTORY_SEPARATOR.'imager'.DIRECTORY_SEPARATOR.'video-adapter'.$this->sourceModel->transformPath.'/';

        if (!is_dir($cachePath)) {
            try {
                File::ensureDirectoryExists($cachePath);
            } catch (\Throwable) {
                // just ignore
            }

            if (!is_dir($cachePath)) {
                Log::error('Could not create path: '.$cachePath);

                return;
            }
        }

        $this->path = $cachePath.$this->getTempFilename($this->sourceModel->basename);

        if (!$this->shouldCreateTempFile($this->path)) {
            return;
        }

        $ffmpeg = FFMpeg::create($settings->ffmpegConfig);
        $video = $ffmpeg->open($this->sourceModel->getFilePath());
        $frame = $video->frame(TimeCode::fromSeconds($this->convertTime($this->time)));
        $frame->save($this->path);
    }

    protected function getTempFilename(string $basename): string
    {
        return strtr('$basename_$time.$format', ['$basename' => $basename, '$time' => str_replace('.', '-', $this->convertTime($this->time)), '$format' => $this->format]);
    }

    protected function shouldCreateTempFile(string $path): bool
    {
        $settings = app(Settings::class);

        return !$settings->cacheEnabled ||
            !file_exists($path) ||
            (($settings->cacheDuration !== false) && (File::lastModified($path) + $settings->cacheDuration < time()));
    }

    protected function convertTime(int|float|string $time): int|float
    {
        if (!is_string($time)) {
            return $time;
        }

        $duration = $this->getDuration();
        $percent = ((int)str_replace('%', '', $time))/100;

        return $duration * $percent;
    }
}
