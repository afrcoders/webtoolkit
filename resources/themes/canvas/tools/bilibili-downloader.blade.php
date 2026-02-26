<x-application-tools-wrapper>
    <x-ad-slot :advertisement="get_advert_model('above-tool')" />
    <x-tool-wrapper :tool="$tool">
        <x-ad-slot :advertisement="get_advert_model('above-form')" />
        <x-form method="post" :route="route('tool.handle', $tool->slug)">
            <div class="row">
                <div class="col-md-12">
                    <x-input-label>@lang('tools.videoUrl')</x-input-label>
                    <div class="input-group">
                        <x-text-input class="form-control" name="video_url" id="video_url" type="url" required
                            value="{{ $results->video_url ?? old('video_url') }}" :placeholder="__('tools.enterVideoUrl')" />
                        <x-button type="submit" class="btn btn-primary">
                            @lang('common.download')
                        </x-button>
                    </div>
                    <x-input-error :messages="$errors->get('video_url')" />
                </div>
            </div>
        </x-form>
    </x-tool-wrapper>
    @if (isset($results))
        <x-page-wrapper :title="__('common.result')">
            <div class="wp-detail result mt-4">
                <x-ad-slot :advertisement="get_advert_model('above-result')" />
                <div class="row">
                    <div class="col-md-6">
                        <div class="box-shadow mb-3">
                            @if ($results->thumbnail)
                                <img src="{{ $results->thumbnail }}" alt="{{ $results->title }}" class="img-fliud mb-3">
                            @endif
                            <p>{{ $results->title }}</p>
                            <p><strong>{{ __('tools.duration') }}</strong>: {{ $results->formatted_duration }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-style mb-0">
                            <thead>
                                <tr>
                                    <th>@lang('tools.type')</th>
                                    <th>@lang('tools.quality')</th>
                                    <th>@lang('tools.size')</th>
                                    <th>@lang('tools.download')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($results->medias as $media)
                                    <tr>
                                        <th>{{ $media['format'] }}</th>
                                        <th>{{ $media['quality'] }}</th>
                                        <th>{{ $media['size_formatted'] }}</th>
                                        <td>
                                            <x-form class="download-all-btn no-app-loader d-inline-block" metho="post" :route="route('tool.postAction', ['tool' => $tool->slug, 'action' => 'download'])">
                                                <input type="hidden" name="process_id" value="{{ $results->process_id }}">
                                                <input type="hidden" name="token" value="{{ $media['token'] }}">
                                                <x-download-form-button class="btn-sm" :text="__('tools.download')" />
                                            </x-form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </x-page-wrapper>
        <x-ad-slot :advertisement="get_advert_model('below-result')" />
    @endif
    <x-tool-content :tool="$tool" />
</x-application-tools-wrapper>
