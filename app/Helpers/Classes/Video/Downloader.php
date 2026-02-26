<?php

namespace App\Helpers\Classes\Video;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Helpers\Classes\Video\DownloadResult;

abstract class Downloader
{
    protected string $video_url = '';
    protected string $title = '';
    protected string $thumbnail = '';
    protected float|string $duration = 0;
    protected float|string $formatted_duration = 0;
    protected string $source = '';
    protected array $medias = [];
    protected $size = 0;
    protected string $process_id;

    public function __construct(string $process_id, string $videoUrl)
    {
        $this->video_url = $videoUrl;
        $this->process_id = $process_id;
    }
    /**
     * Abstract fetch method to be implemented by child downloaders.
     *
     * @return DownloadResult|null
     */
    abstract public function fetch(): ?DownloadResult;

    /**
     * Make an HTTP GET request.
     */
    protected function fetchUrl(string $url): ?string
    {
        try {
            return Http::withHeaders($this->getRequestHeaders())
                // ->withOptions($this->getRequestOptions())
                ->get($url)
                ->body();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Default HTTP headers (can be overridden by child classes).
     */
    protected function getRequestHeaders(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0',
        ];
    }

    /**
     * Default Guzzle options (can be overridden by child classes).
     */
    protected function getRequestOptions(): array
    {
        return [
            'verify' => false,
            'allow_redirects' => true,
            'timeout' => 0,
            'curl' => [
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_MAXREDIRS => 10,
            ],
        ];
    }

    /**
     * Extract string between two substrings.
     */
    protected function getStringBetween(string $content, string $start, string $end): string
    {
        $posStart = strpos($content, $start);
        if ($posStart === false) return '';

        $posStart += strlen($start);
        $posEnd = strpos($content, $end, $posStart);

        return $posEnd !== false ? substr($content, $posStart, $posEnd - $posStart) : '';
    }

    /**
     * Save and cache a thumbnail from a URL to local storage.
     */
    protected function saveThumbnail(): void
    {
        if (!$this->thumbnail) return;

        $hash = sha1($this->thumbnail);
        $cacheKey = 'thumb_' . $hash;

        $cached = Cache::rememberForever($cacheKey, function () {
            return uploadFileFromUrl($this->thumbnail, true, true, $this->source);
        });

        $this->thumbnail = $cached ?? $this->thumbnail;
    }

    /**
     * Estimate video file size in bytes.
     *
     * @param float $bandwidth Bandwidth in Mbps (Megabits per second)
     * @param float $duration Duration in seconds
     */
    protected function estimateVideoSize($bandwidth, $duration)
    {
        $this->size = ($duration / 60.0) * ($bandwidth * 10.0);

        return $this->size;
    }

    /**
     * Sort media list by quality label descending.
     */
    protected function sortMediasByQuality(): void
    {
        usort($this->medias, function ($a, $b) {
            return strcmp($b['quality'], $a['quality']);
        });
    }

    /**
     * Return a uniform media array.
     */
    protected function createMedia(string $url, string $quality, string $format, bool $isVideo, bool $isAudio, ?int $size = null): array
    {
        return [
            'url' => $url,
            'quality' => $quality,
            'format' => $format,
            'is_video' => $isVideo,
            'is_audio' => $isAudio,
            'size' => $size,
        ];
    }

    /**
     * Get the thumbnail hash for storage.
     */
    protected function thumbnailHash(): string
    {
        return sha1($this->thumbnail);
    }

    protected function matchHtml(string $html, string $regex): ?string
    {
        return preg_match($regex, $html, $matches) ? $matches[1] : null;
    }

    protected function extractJson(string $html, string $pattern): ?array
    {
        if (!preg_match($pattern, $html, $matches)) return null;

        return json_decode($matches[1], true);
    }
}
