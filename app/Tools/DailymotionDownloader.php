<?php

namespace App\Tools;

use App\Models\Tool;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Contracts\ToolInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Classes\Video\DownloadHelper;
use App\Helpers\Classes\Video\DailymotionService;

class DailymotionDownloader implements ToolInterface
{
    public function render(Request $request, Tool $tool)
    {
        return view('tools.dailymotion-downloader', compact('tool'));
    }

    public function handle(Request $request, Tool $tool)
    {
        $validated = $request->validate([
            'video_url' => [
                'required',
                'url',
                function ($attribute, $value, $fail) {
                    $host = parse_url($value, PHP_URL_HOST);

                    // List of supported dailymotion domains
                    $allowedHosts = [
                        'dai.ly',
                        'www.dai.ly',
                        'dailymotion.com',
                        'www.dailymotion.com',
                    ];

                    if (!in_array($host, $allowedHosts)) {
                        $fail(__('tools.invalidDailymotionUrl', ['attribute' => $attribute]));
                    }
                }
            ],
        ]);

        $process_id = Str::uuid();
        $videoUrl = $request->input('video_url');
        $downloader = new DailymotionService($process_id, $videoUrl);
        $results = $downloader->fetch($videoUrl);

        return view('tools.dailymotion-downloader', compact('results', 'tool'));
    }

    public function postAction(Request $request, $tool)
    {
        $action = $request->action;

        switch ($action) {
            case 'download':
                return $this->download($request, $tool);
                break;
        }

        abort(404);
    }

    protected function download(Request $request, $tool)
    {
        $validator = Validator::make($request->all(), [
            'process_id' => 'required|uuid',
            'token' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => __('tools.invalidRequest')]);
        }

        $process_id = $request->input('process_id');
        $job = Cache::get($process_id);
        if (!$job) {
            return response()->json(['status' => false, 'message' => __('tools.theRequestExpired')]);
        }

        $token = $request->token;
        if (!$job->mediaExists($token)) {
            return response()->json(['status' => false, 'message' => __('tools.theRequestExpired')]);
        }

        $media = $job->getMedia($token);

        $url = $media['url'];
        $filename = $job->title . '.' . $media['format'];
        $referer = $job->getRefererFromUrl();
        $helper = app(DownloadHelper::class, ['url' => $url])->setReferer($referer);

        return $helper->streamM3U8Direct($url, $filename);
    }
}
