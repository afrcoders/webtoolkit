<?php

namespace App\Helpers\Classes\Video;

use Illuminate\Support\Str;

class DownloadResult
{
    public string $process_id;
    public string $video_url;
    public string $title;
    public string $thumbnail;
    public float|string $duration;
    public float|string $formatted_duration;
    public string $source;
    public array $medias;

    public function __construct(
        string $process_id,
        string $video_url,
        string $title,
        string $thumbnail,
        float|string $duration,
        string $source,
        array $medias
    ) {
        $this->process_id = $process_id;
        $this->video_url = $video_url;
        $this->title = $title;
        $this->thumbnail = $thumbnail;
        $this->duration = $duration;
        $this->source = $source;
        $this->medias = $this->normalizeMedias($medias);
        $this->formattedDuration($duration);
    }

    /**
     * Normalize media items to a uniform format
     */
    private function normalizeMedias(array $medias): array
    {
        return array_map(function ($media) {
            return [
                'url' => $media['url'] ?? '',
                'quality' => $media['quality'] ?? '',
                'format' => $media['format'] ?? '',
                'is_video' => $media['is_video'] ?? false,
                'is_audio' => $media['is_audio'] ?? false,
                'size' => $media['size'] ?? null,
                'size_formatted' => $this->getFormattedSize($media['size'] ?? 0),
                'token' => Str::random(100),
            ];
        }, $medias);
    }

    /**
     * Return formatted array for JSON responses
     */
    public function toArray(): array
    {
        return [
            'process_id' => $this->process_id,
            'video_url' => $this->video_url,
            'title' => $this->title,
            'thumbnail' => $this->thumbnail,
            'duration' => $this->duration,
            'source' => $this->source,
            'media' => $this->medias,
        ];
    }

    /**
     * Return JSON-ready object
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * set duration (e.g., "4:32" or "1:02:45").
     */
    public function formattedDuration($duration)
    {
        $this->formatted_duration = getFormattedDuration($duration);
    }

    /**
     * Get formatted size using global helper.
     */
    public function getFormattedSize($size): string
    {
        return formatSizeUnits($size);
    }

    /**
     * Check if a media item with the given token (quality) exists in the media collection.
     *
     * @param string $token The quality or unique identifier to search for in the media list.
     * @return bool Returns true if a media item with the given quality is found, otherwise false.
     */
    public function mediaExists(string $token): bool
    {
        foreach ($this->medias as $media) {
            if ($media['token'] == $token) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a media item with the given token (quality) exists in the media collection.
     *
     * @param string $token The quality or unique identifier to search for in the media list.
     * @return bool Returns true if a media item with the given quality is found, otherwise false.
     */
    public function getMedia(string $token): ?array
    {
        foreach ($this->medias as $media) {
            if ($media['token'] === $token) {
                return $media;
            }
        }

        return null;
    }

    /**
     * Extract the base referer from a given URL.
     */
    function getRefererFromUrl(): ?string
    {
        $parts = parse_url($this->video_url);

        if (!isset($parts['scheme'], $parts['host'])) {
            return null; // Invalid URL
        }

        $referer = $parts['scheme'] . '://' . $parts['host'];

        // Optional: include trailing slash
        if (substr($referer, -1) !== '/') {
            $referer .= '/';
        }

        return $referer;
    }
}
