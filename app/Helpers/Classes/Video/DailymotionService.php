<?php

namespace App\Helpers\Classes\Video;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class DailymotionService extends Downloader
{
    protected string $source = 'dailymotion';

    public function fetch(): ?DownloadResult
    {
        return Cache::remember($this->process_id, job_cache_time(), function () {
            $this->processUrl();

            if (!$this->title || empty($this->medias)) {
                return null;
            }

            return new DownloadResult(
                $this->process_id,
                $this->video_url,
                $this->title,
                $this->thumbnail,
                $this->duration,
                $this->source,
                $this->medias
            );
        });
    }

    private function processUrl(): void
    {
        $videoId = $this->extractVideoId($this->video_url);

        if (empty($videoId)) {
            throw new \Exception(__('tools.failedToExtractId', ['source' => $this->source]));
        }

        $metadataUrl = "https://www.dailymotion.com/player/metadata/video/{$videoId}";
        $metadataJson = $this->fetchUrl($metadataUrl);

        if (!$metadataJson) {
            throw new \Exception(__('tools.failedToExtractVideo', ['source' => $this->source]));
        }

        $data = json_decode($metadataJson, true);

        if (empty($data['qualities']['auto'][0]['url'])) {
            throw new \Exception(__('tools.failedToExtractVideo', ['source' => $this->source]));
        }

        $this->title = $data['title'] ?? '';
        if (!empty($data['posters']) && is_array($data['posters'])) {
            $this->thumbnail = end($data['posters']);
        } elseif (!empty($data['thumbnails']) && is_array($data['thumbnails'])) {
            $this->thumbnail = end($data['thumbnails']);
        }

        $this->saveThumbnail();
        $this->duration = $data['duration'] ?? 0;

        $m3u8Content = $this->fetchUrl($data['qualities']['auto'][0]['url']);
        if (!$m3u8Content) {
            throw new \Exception(__('tools.failedToExtractVideo', ['source' => $this->source]));
        }

        preg_match_all('/#EXT-X-STREAM-INF:(.*?)\n(.*)/', $m3u8Content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $info = $match[1];
            $url = $match[2];

            $quality = $this->matchHtml($info, '/NAME="([^"]+)"/');
            $bandwidth = $this->matchHtml($info, '/BANDWIDTH=(\d+)/');

            if (!$quality || !$url) continue;

            $size = $this->estimateVideoSize($bandwidth, $this->duration);
            $this->medias[] = $this->createMedia(
                $url,
                $quality . 'p',
                'mp4',
                true,
                true,
                $size
            );
        }

        $this->sortMediasByQuality();
    }

    private function extractVideoId(string $url): string
    {
        $host = Str::of(parse_url($url, PHP_URL_HOST))->replace('www.', '')->lower();

        if ($host->contains('dai.ly')) {
            return Str::of(parse_url($url, PHP_URL_PATH))->trim('/')->value();
        }

        if ($host->contains('dailymotion.com')) {
            $path = parse_url($url, PHP_URL_PATH);
            $segments = collect(explode('/', trim($path, '/')));

            $videoIndex = $segments->search('video');
            if ($videoIndex !== false && $segments->has($videoIndex + 1)) {
                return $segments->get($videoIndex + 1);
            }

            return $segments->get(1, '');
        }

        return '';
    }
}
