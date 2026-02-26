<?php

namespace App\Helpers\Classes\Video;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http as HttpClient;

class DownloadHelper
{
    protected string $url;
    protected array $headers = [];
    protected static string $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)';
    protected static int $chunkSize = 10_000_000; // 10MB
    public static bool $enableProxy = false;
    public static ?array $proxy = null;

    public function __construct(string $url)
    {
        $this->url = $url;
        $this->headers['User-Agent'] = self::$userAgent;
    }

    public function addHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    public function setReferer(string $referer): self
    {
        if (!empty($referer)) {
            $this->headers['Referer'] = $referer;
        }

        return $this;
    }

    protected function formattedHeaders(): array
    {
        return array_map(
            fn($k, $v) => "$k: $v",
            array_keys($this->headers),
            $this->headers
        );
    }

    public function request(string $method = 'GET', array $options = [])
    {
        $client = HttpClient::withHeaders($this->headers);

        if (self::$enableProxy && self::$proxy) {
            $client->withOptions(['proxy' => self::$proxy]);
        }

        switch (strtoupper($method)) {
            case 'HEAD':
                return $client->withOptions($options)->head($this->url);
            case 'POST':
                return $client->withOptions($options)->post($this->url);
            default:
                return $client->withOptions($options)->get($this->url);
        }
    }

    public function getLongUrl(): string
    {
        $response = $this->request('HEAD');
        return method_exists($response, 'effectiveUri') && $response->effectiveUri()
            ? $response->effectiveUri()
            : $this->url;
    }

    public function getFileSize(): ?int
    {
        $response = $this->request('HEAD');
        if ($response->ok() && $response->header('Content-Length')) {
            return (int) $response->header('Content-Length');
        }

        $response = $this->request('GET', [
            'headers' => ['Range' => 'bytes=0-0'],
        ]);

        if (
            $response->header('Content-Range') &&
            preg_match('/bytes \d+-\d+\/(\d+)/', $response->header('Content-Range'), $matches)
        ) {
            return (int) $matches[1];
        }

        return null;
    }

    public function download(string $name, string $extension, ?int $size = null)
    {
        $filename = Str::slug($name) . '.' . strtolower($extension);

        if ($size !== false) {
            $size = $this->getFileSize();
        }

        $responseHeaders = [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Accept-Ranges'       => 'bytes',
            'Pragma'              => 'public',
            'Cache-Control'       => 'no-store',
        ];

        if ($size) {
            $responseHeaders['Content-Length'] = $size;
        }

        return Response::stream(function () use ($name, $extension, $size) {
            $chunkSize = self::$chunkSize;
            $chunkStart = 0;
            $chunkEnd = $chunkSize;
            $count = $tries = 0;

            $ch = curl_init();

            while (!$size || $chunkStart < $size) {
                curl_setopt_array($ch, [
                    CURLOPT_URL            => $this->url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_BUFFERSIZE     => $chunkSize,
                    CURLOPT_HEADER         => false,
                    CURLOPT_RANGE          => "$chunkStart-" . min($chunkEnd, ($size ?: $chunkEnd)),
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTPHEADER     => $this->formattedHeaders(),
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_USERAGENT      => self::$userAgent,
                ]);

                $chunk = curl_exec($ch);
                $info = curl_getinfo($ch);

                if ($info['http_code'] === 403 && $count === 0) {
                    curl_close($ch);
                    $this->downloadChunk($name, $extension, $size);
                    return;
                }

                if (!in_array($info['http_code'], [200, 206])) {
                    if (++$tries < 5) {
                        usleep(200_000);
                        continue;
                    }
                    break;
                }

                echo $chunk;
                flush();

                $tries = 0;
                $chunkStart += $chunkSize;
                if ($count === 0) $chunkStart++;
                $chunkEnd += $chunkSize;
                $count++;
            }

            curl_close($ch);
        }, 200, $responseHeaders);
    }

    /**
     * Download the file in a single request (no chunking).
     */
    public function downloadLegacy(string $name, string $extension, ?int $size = null): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = Str::slug($name) . '.' . strtolower($extension);

        $headers = [
            'Content-Type'              => 'application/octet-stream',
            'Content-Disposition'       => 'attachment; filename="' . $filename . '"',
            'Content-Transfer-Encoding' => 'binary',
            'Pragma'                    => 'public',
            'Cache-Control'             => 'no-store, no-cache, must-revalidate',
            'Expires'                   => '0',
        ];

        if ($size) {
            $headers['Content-Length'] = $size;
        }

        return Response::stream(function () {
            $ch = curl_init($this->url);

            $curlHeaders = [];
            foreach ($this->headers as $key => $value) {
                $curlHeaders[] = "$key: $value";
            }

            curl_setopt_array($ch, [
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPHEADER     => $curlHeaders,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_USERAGENT      => self::$userAgent,
                CURLOPT_WRITEFUNCTION  => function ($ch, $data) {
                    echo $data;
                    flush();
                    return strlen($data);
                },
            ]);

            curl_exec($ch);

            if (curl_errno($ch)) {
                echo "Download error: " . curl_error($ch);
            }

            curl_close($ch);
        }, 200, $headers);
    }

    protected function downloadChunk(string $name, string $extension, ?int $size)
    {
        $filename = Str::slug($name) . '.' . strtolower($extension);

        $headers = [
            'Content-Type'              => 'application/octet-stream',
            'Content-Disposition'       => 'attachment; filename="' . $filename . '"',
            'Content-Transfer-Encoding' => 'binary',
            'Expires'                   => '0',
            'Pragma'                    => 'public',
            'Cache-Control'             => 'no-store, no-cache, must-revalidate',
        ];

        if ($size && $size > 100) {
            $headers['Content-Length'] = $size;
        }

        return Response::stream(function () {
            $ch = curl_init($this->url);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_HEADER         => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPHEADER     => $this->formattedHeaders(),
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_USERAGENT      => self::$userAgent,
                CURLOPT_FILE           => fopen('php://output', 'w'),
            ]);

            curl_exec($ch);
            curl_close($ch);
        }, 200, $headers);
    }

    public function streamM3U8Direct(string $m3u8Url, string $filename = 'video.ts')
    {
        $response = Http::get($m3u8Url);

        if (!$response->ok()) {
            abort(404, 'Failed to fetch M3U8 playlist.');
        }

        $playlist = $response->body();

        preg_match_all('/^(?!#)(.+\.ts)$/m', $playlist, $matches);

        $segments = $matches[1] ?? [];

        if (empty($segments)) {
            abort(422, 'No segments found in M3U8 playlist.');
        }

        $baseUrl = rtrim(dirname($m3u8Url), '/') . '/';

        return response()->streamDownload(function () use ($segments, $baseUrl) {
            foreach ($segments as $segment) {
                $segmentUrl = $baseUrl . ltrim($segment, '/');
                try {
                    $segmentResponse = Http::get($segmentUrl);
                    if ($segmentResponse->ok()) {
                        echo $segmentResponse->body();
                        ob_flush();
                        flush();
                    } else {
                        \Log::warning("Failed to stream segment: $segmentUrl");
                    }
                } catch (\Exception $e) {
                    \Log::error("Segment stream error: " . $e->getMessage());
                    // Optionally continue or break
                }
            }
        }, $filename, [
            'Content-Type' => 'video/MP2T',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store',
        ]);
    }
}
