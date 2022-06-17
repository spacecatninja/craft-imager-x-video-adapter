<?php
/**
 * Video adapter for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2022 André Elvan
 */

namespace spacecatninja\imagerxvideoadapter\adapters;

use Craft;
use craft\elements\Asset;
use craft\helpers\FileHelper;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use spacecatninja\imagerx\adapters\ImagerAdapterInterface;
use spacecatninja\imagerx\exceptions\ImagerException;
use spacecatninja\imagerx\models\LocalSourceImageModel;
use spacecatninja\imagerxvideoadapter\models\Settings;
use spacecatninja\imagerxvideoadapter\VideoAdapter;
use yii\base\Exception;

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
        /* @var Settings $settings */
        $settings = VideoAdapter::getInstance()?->getSettings();

        if ($settings) {
            $this->format = $opts['format'] ?? $settings->defaultFormat;
            $this->time = $opts['time'] ?? $settings->defaultTime;
        }

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

        try {
            $this->sourceModel = new LocalSourceImageModel($asset);
        } catch (ImagerException $imagerException) {
            Craft::error('An error occured when trying to open file with Video Adapter: '.$imagerException->getMessage(), __METHOD__);
            $this->sourceModel = null;
        }
    }


    public function getDuration(): int|float
    {
        /* @var Settings $settings */
        $settings = VideoAdapter::getInstance()?->getSettings();
        
        if ($this->sourceModel === null) {
            return 0;
        }

        try {
            $this->sourceModel->getLocalCopy();
        } catch (ImagerException $imagerException) {
            Craft::error('An error occured when trying to open file with Video Adapter: '.$imagerException->getMessage(), __METHOD__);

            return 0;
        }

        $sourceModel = $this->sourceModel;
        
        $duration = Craft::$app->getCache()->get(self::DURATION_CACHE_KEY.'_'.base64_encode($this->sourceModel->getFilePath()));
        
        if ($duration) {
            return $duration;
        }
        
        $ffprobe = FFProbe::create($settings->ffmpegConfig);
        $duration = $ffprobe->format($sourceModel->getFilePath())->get('duration');      
        
        Craft::$app->getCache()->set(self::DURATION_CACHE_KEY.'_'.base64_encode($this->sourceModel->getFilePath()), $duration);

        return $duration;
    }

    public function setTime(int|float|string $time): void
    {
        $this->time = $time;
    }

    /*
     * -- Protected methods -----------------------------------
     */

    protected function getVideoThumbnail(): void
    {
        /* @var Settings $settings */
        $settings = VideoAdapter::getInstance()?->getSettings();

        if ($this->sourceModel === null) {
            return;
        }

        try {
            $this->sourceModel->getLocalCopy();
        } catch (ImagerException $imagerException) {
            Craft::error('An error occured when trying to open file with Video Adapter: '.$imagerException->getMessage(), __METHOD__);
            return;
        }
        
        $this->transformPath = $this->sourceModel->transformPath;
        
        try {
            $cachePath = Craft::$app->getPath()->getRuntimePath().DIRECTORY_SEPARATOR.'imager'.DIRECTORY_SEPARATOR.'video-adapter'.$this->sourceModel->transformPath.'/';
        } catch (Exception $exception) {
            Craft::error('An error occured when trying to set cache path in Video Adapter: '.$exception->getMessage(), __METHOD__);
            return;
        }

        if (!realpath($cachePath)) {
            try {
                FileHelper::createDirectory($cachePath);
            } catch (\Exception) {
                // just ignore
            }

            if (!realpath($cachePath)) {
                Craft::error('Could not create path: '.$cachePath, __METHOD__);

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
        /* @var Settings $settings */
        $settings = VideoAdapter::getInstance()?->getSettings();

        return !$settings->cacheEnabled ||
            !file_exists($path) ||
            (($settings->cacheDuration !== false) && (FileHelper::lastModifiedTime($path) + $settings->cacheDuration < time()));
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
