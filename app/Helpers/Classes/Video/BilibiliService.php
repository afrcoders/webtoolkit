<?php

namespace App\Helpers\Classes\Video;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Helpers\Classes\Video\Downloader;
use App\Helpers\Classes\Video\DownloadResult;

class BilibiliService extends Downloader
{
    protected string $source = 'bilibili';

    public function fetch(): ?DownloadResult
    {
        return Cache::remember($this->process_id, job_cache_time(), function () {
            Str::contains($this->video_url, 'bilibili.tv')
                ? $this->handleIntlVideo()
                : $this->handleCnVideo();

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

    protected function handleIntlVideo(): void
    {
        if (preg_match('#/video/(\d+)#', $this->video_url, $matches)) {
            $aid = $matches[1];
            $apiUrl = "https://api.bilibili.tv/intl/gateway/web/playurl?s_locale=en_US&platform=web&aid={$aid}&qn=64&type=0&device=wap&tf=0";
        } elseif (preg_match('#/play/\d+/(\d+)#', $this->video_url, $matches)) {
            $ep_id = $matches[1];
            $apiUrl = "https://api.bilibili.tv/intl/gateway/web/playurl?s_locale=en_US&platform=web&ep_id={$ep_id}&qn=64&type=0&device=wap&tf=0";
        } else {
            throw new \Exception(__('tools.failedToFetchService', ['service' => 'Bilibili']), 500);
        }

        $html = Cache::remember($this->process_id . '-html', job_cache_time(), function () {
            try {
                return $this->fetchUrl($this->video_url);
            } catch (\Throwable $e) {
                \Log::error('Bilibili intl fetch HTML failed', ['error' => $e->getMessage()]);
                return null;
            }
        });

        if (!$html) {
            throw new \Exception(__('tools.failedToFetchService', ['service' => 'Bilibili']), 500);
        }

        $this->title = $this->matchHtml($html, '/<title>(.*?)<\/title>/is');
        $this->thumbnail = $this->matchHtml($html, '/<meta[^>]+(?:property|name)="og:image"[^>]+content="([^"]+)"/i');
        $this->saveThumbnail();

        $json = Cache::remember($this->process_id . '-json', job_cache_time(), function () use ($apiUrl) {
            try {
                $response = $this->fetchUrl($apiUrl);
                return json_decode($response, true);
            } catch (\Throwable $e) {
                \Log::info('Bilibili fetch JSON failed', ['error' => $e->getMessage()]);
                return null;
            }
        });

        if (!$json) {
            throw new \Exception(__('tools.failedToFetchService', ['service' => 'Bilibili']), 500);
        }

        $this->duration = ($json['data']['playurl']['duration'] ?? 0) / 1000;

        if (!empty($json['data']['playurl']['audio_resource'])) {
            $audio = end($json['data']['playurl']['audio_resource']);
            $audioUrl = $audio['url'] ?? $audio['backup_url'][0] ?? null;

            if ($audioUrl) {
                $this->medias[] = $this->createMedia($audioUrl, '320kbps', 'm4a', false, true, $audio['size']);
            }

            foreach ($json['data']['playurl']['video'] ?? [] as $video) {
                $videoRes = $video['video_resource'] ?? [];
                $videoUrl = $videoRes['url'] ?? $videoRes['backup_url'][0] ?? null;
                $desc = $video['stream_info']['desc_words'] ?? 'Unknown';

                if ($videoUrl) {
                    $this->medias[] = $this->createMedia($videoUrl, $desc, 'mp4', true, false, $videoRes['size']);
                }
            }

            $this->sortMediasByQuality();
        }
    }

    protected function handleCnVideo(): void
    {
        $html = Cache::remember($this->process_id, job_cache_time(), function () {
            try {
                return $this->fetchUrl($this->video_url);
            } catch (\Throwable $e) {
                \Log::error('Bilibili CN fetch HTML failed', ['error' => $e->getMessage()]);
                return null;
            }
        });

        if (!$html) {
            throw new \Exception(__('tools.failedToFetchService', ['service' => 'Bilibili']), 500);
        }

        if ($data = $this->extractJson($html, '/window\.__playinfo__\s*=\s*({.*?})\s*<\/script>/is')) {
            $this->processCnHtml($html, $data['data']['dash'] ?? []);
        } elseif ($data = $this->extractJson($html, '/console\.log\s*\(\s*[\'"]responseData[\'"]\s*,\s*({.*?})\s*\)/is')) {
            $this->processCnHtml($html, $data['data']['result']['video_info']['dash'] ?? []);
        }
    }

    private function processCnHtml(string $html, array $dash): void
    {
        $this->title = $this->matchHtml($html, '/<meta[^>]*name=["\']title["\'][^>]*content=["\']([^"\']+)["\']/i')
            ?? $this->matchHtml($html, '/<meta[^>]*itemProp=["\']name["\'][^>]*content=["\']([^"\']+)["\']/i');

        $this->thumbnail = $this->matchHtml($html, '/<meta[^>]*property=["\']og:image["\'][^>]*content=["\']([^"\']+)["\']/i')
            ?? $this->matchHtml($html, '/data-vue-meta="true"[^>]*itemprop="image"[^>]*content="([^"@]+)/i');

        $this->saveThumbnail();
        $this->duration = $dash['duration'] ?? 0;

        foreach ($dash['video'] ?? [] as $video) {
            $url = $video['base_url'] ?? $video['backup_url'][0] ?? null;
            $size = $this->estimateVideoSize($video['bandwidth'], $this->duration);
            $this->medias[] = $this->createMedia($url, $video['height'] . 'p', 'mp4', true, false, $size);
        }

        foreach ($dash['audio'] ?? [] as $audio) {
            $url = $audio['base_url'] ?? $audio['backup_url'][0] ?? null;
            $kbps = (int)($audio['bandwidth'] / 1000) . ' kbps';
            $size = $this->estimateVideoSize($video['bandwidth'], $this->duration);
            $this->medias[] = $this->createMedia($url, $kbps, 'm4a', false, true, $size);
        }

        $this->sortMediasByQuality();
    }
}
