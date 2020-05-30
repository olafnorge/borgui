@extends('layouts.app')

@push('js')
    <script type="text/javascript">
        $('.wait-for-reload').on('click', function ($event) {
            {{-- ensure the page gets not reloaded unless we want it --}}
            $event.preventDefault();
            $poll(
                $(this).attr('href').split('?').shift() + '/poll',
                new URL($(this).attr('href')).searchParams.get('folder')
            );
        });
        $('.do-download').on('click', function($event) {
            let $interval = null;
            let $token = $(this).find('input[name="download_token"]').val();

            $('#loadingModal').modal({
                backdrop: false,
                keyboard: false,
                show: true
            }).find('.additional-message').text("We are preparing the download for you.");

            $interval = setInterval(function () {
                if (Cookies.get('download_token') === $token) {
                    clearInterval($interval);
                    $('#loadingModal').hide();
                    @if(request()->isSecure())
                        Cookies.remove('download_token', { secure: true });
                    @else
                        Cookies.remove('download_token');
                    @endif
                }
            }, 500);
        });

        let $request = null;
        const $poll = function ($route, $folder) {
            if ($request) {
                $request.abort();
            }

            $request = $.ajax({
                type: 'PUT',
                url: $route,
                contentType: 'application/json; charset=UTF-8',
                data: JSON.stringify({ "folder": $folder }),
                dataType: 'json',
                statusCode: {
                    {{-- server claims to have a proper folder listing --}}
                    200: function ($response) {
                        window.location.assign($response.location);
                    },
                    {{-- server says we have to wait --}}
                    202: function ($response) {
                        $('#loadingModal').find('.additional-message').text($response.message);

                        setTimeout(function () {
                            $poll($route, $folder);
                        }, $request.getResponseHeader('Retry-After') * 1000);
                    }
                },
                {{-- some error occured --}}
                error: function ($response) {
                    window.location.reload();
                }
            });
        };

        @if(isset($retryAfter) || isset($message))
            $('#loadingModal').modal({
                backdrop: false,
                keyboard: false,
                show: true
            }).find('.additional-message').text("{{ isset($message) ? $message : '' }}");
            setTimeout(function () {
                $poll("{{ route('backup.poll', ['backup' => $backup]) }}", "{{ $folder }}");
            }, parseInt("{{ isset($retryAfter) ? $retryAfter : 5 }}") * 1000);
        @endif
    </script>
@endpush

@section('content')
    <div class="card">
        <div class="card-header bg-primary">
            <h2>{{ $backup->name }} <span class="h5 text-secondary">{{ $backup->repository->name }}</span> </h2>
        </div>
        <div class="card-body pt-2 pr-2 pb-2 pl-2">
            <div class="no-more-tables">
                <table class="table mb-0">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col" class="border-0 w-33">Start</th>
                        <th scope="col" class="border-0 w-33">Duration</th>
                        <th scope="col" class="border-0 w-33">Archive</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td scope="row" data-title="Start">
                            <span class="no-more-tables-cell">{{ $backup->start->calendar() }}</span>
                        </td>
                        <td data-title="Duration">
                            <span class="no-more-tables-cell">@duration($backup->duration)</span>
                        </td>
                        <td data-title="Archive">
                            <span class="no-more-tables-cell">
                                Files:&nbsp;{{ $backup->number_files }}<br>
                                Size:&nbsp;@byteToHumanReadable($backup->original_size)<br>
                                Compressed:&nbsp;@byteToHumanReadable($backup->compressed_size)<br>
                                Deduplicated:&nbsp;@byteToHumanReadable($backup->deduplicated_size)
                            </span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <div class="row">
                <div class="col-10 h2">Backup Data</div>
                <div class="col-2 pl-0 text-right">
                    <button class="btn btn-warning do-post" title="Flush Browse Cache" data-confirm="This will flush the cache! Are you sure?" data-formid="{{ sprintf('flush-browse-cache-%s', $backup->id) }}">
                        <i class="fa fa-recycle"></i>
                        {{ Form::open(['url' => route('backup.flush', ['backup' => $backup]), 'hidden' => true, 'id' => sprintf('flush-browse-cache-%s', $backup->id), 'method' => 'DELETE']) }}{{ Form::close() }}
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body pt-2 pr-2 pb-2 pl-2">
            {!! $breadcrumb !!}
            <div class="no-more-tables">
                <table class="table table-striped mb-0">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col" class="border-0">Type</th>
                        <th scope="col" class="border-0">Name</th>
                        <th scope="col" class="border-0">Owner</th>
                        <th scope="col" class="border-0">Group</th>
                        <th scope="col" class="border-0">Permissions</th>
                        <th scope="col" class="border-0">Date Modified</th>
                        <th scope="col" class="border-0">Size</th>
                        <th scope="col" class="border-0">&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($listings)
                        @if(count($listings) === 1)
                            <tr>
                                <td scope="row" data-title="Type"><i class="icon-action-undo no-more-tables-cell"></i></td>
                                <td data-title="Name">
                                    <a class="wait-for-reload btn-link btn-block no-more-tables-cell" data-prevent-default="true" href="{{ route('backup.show', ['backup' => $backup, 'folder' => $parent]) }}">
                                        ..
                                    </a>
                                </td>
                                <td data-title="Owner"></td>
                                <td data-title="Group"></td>
                                <td data-title="Permissions"></td>
                                <td data-title="Date Modified"></td>
                                <td data-title="Size"></td>
                                <td data-title="Actions"></td>
                            </tr>
                            @if(array_get($listings[0], 'type') !== 'd')
                                <tr>
                                    <td scope="row" data-title="Type"><i class="icon-{{ value(function () use ($listings) {
                                            return array_get($listings[0], 'type') === 'l' ? 'link' : 'doc';
                                        }) }} no-more-tables-cell"></i></td>
                                    <td data-title="Name">
                                        <span class="no-more-tables-cell">
                                            {{ ltrim(basename(sprintf('/%s', ltrim(array_get($listings[0], 'path'), '/'))), '/') }}
                                            @if(array_get($listings[0], 'type') === 'l')&nbsp;->&nbsp;{{ array_get($listings[0], 'linktarget') }}@endif
                                        </span>
                                    </td>
                                    <td data-title="Owner"><span class="no-more-tables-cell">{{ array_get($listings[0], 'user') }}&nbsp;({{ array_get($listings[0], 'uid') }})</span></td>
                                    <td data-title="Group"><span class="no-more-tables-cell">{{ array_get($listings[0], 'group') }}&nbsp;({{ array_get($listings[0], 'gid') }})</span></td>
                                    <td data-title="Permissions"><span class="no-more-tables-cell">{{ array_get($listings[0], 'mode') }}</span></td>
                                    <td data-title="Date Modified"><span class="no-more-tables-cell">{{ \Carbon\Carbon::parse(array_get($listings[0], 'mtime')) }}</span></td>
                                    <td data-title="Size"><span class="no-more-tables-cell">@byteToHumanReadable(array_get($listings[0], 'size', 0))</span></td>
                                    <td data-title="Actions">
                                        <span class="no-more-tables-cell">
                                            <div class="dropdown">
                                                <button
                                                        class="btn btn-secondary dropdown-toggle"
                                                        type="button"
                                                        id="{{ sprintf('button-download-%s', sha1(sprintf('/%s', ltrim(array_get($listings[0], 'path'), '/')))) }}"
                                                        data-toggle="dropdown"
                                                        aria-haspopup="true"
                                                        aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="{{ sprintf('button-download-%s', sha1(sprintf('/%s', ltrim(array_get($listings[0], 'path'), '/')))) }}">
                                                    <button class="dropdown-item do-download do-post" data-formid="{{ sprintf('download-%s', sha1(sprintf('/%s', ltrim(array_get($listings[0], 'path'), '/')))) }}">
                                                        Download
                                                        {{ Form::open([
                                                                'url' => route('backup.download', ['backup' => $backup]),
                                                                'hidden' => true,
                                                                'id' => sprintf('download-%s', sha1(sprintf('/%s', ltrim(array_get($listings[0], 'path'), '/')))),
                                                        ]) }}
                                                            {{ Form::hidden('download_token', Str::uuid()) }}
                                                            {{ Form::hidden('folder', sprintf('/%s', ltrim(array_get($listings[0], 'path'), '/'))) }}
                                                        {{ Form::close() }}
                                                    </button>
                                                </div>
                                            </div>
                                        </span>
                                    </td>
                                </tr>
                            @endif
                        @else
                            @foreach($listings as $path)
                                <tr>
                                    @if($loop->first)
                                        <td scope="row" data-title="Type"><i class="icon-action-undo no-more-tables-cell"></i></td>
                                        <td data-title="Name">
                                            <a class="wait-for-reload btn-link btn-block no-more-tables-cell" data-prevent-default="true" href="{{ route('backup.show', ['backup' => $backup, 'folder' => $parent]) }}">
                                                ..
                                            </a>
                                        </td>
                                        <td data-title="Owner"></td>
                                        <td data-title="Group"></td>
                                        <td data-title="Permissions"></td>
                                        <td data-title="Date Modified"></td>
                                        <td data-title="Size"></td>
                                        <td data-title="Actions"></td>
                                    @else
                                        <td scope="row" data-title="Type"><i class="icon-{{ value(function () use ($path) {
                                            $type = array_get($path, 'type');

                                            if ($type === 'd') {
                                                return 'folder';
                                            }

                                            return $type === 'l' ? 'link' : 'doc';
                                        }) }} no-more-tables-cell"></i></td>
                                        <td data-title="Name">
                                            <a class="wait-for-reload btn-link btn-block no-more-tables-cell" data-prevent-default="true" @if(array_get($path, 'type') === 'd')href="{{ route('backup.show', ['backup' => $backup, 'folder' => sprintf('/%s', ltrim(array_get($path, 'path'), '/'))]) }}"@endif>
                                                {{ ltrim(str_replace_first($folder, '', sprintf('/%s', ltrim(array_get($path, 'path'), '/'))), '/') }}
                                                @if(array_get($path, 'type') === 'l')&nbsp;->&nbsp;{{ array_get($path, 'linktarget') }}@endif
                                            </a>
                                        </td>
                                        <td data-title="Owner"><span class="no-more-tables-cell">{{ array_get($path, 'user') }}&nbsp;({{ array_get($path, 'uid') }})</span></td>
                                        <td data-title="Group"><span class="no-more-tables-cell">{{ array_get($path, 'group') }}&nbsp;({{ array_get($path, 'gid') }})</span></td>
                                        <td data-title="Permissions"><span class="no-more-tables-cell">{{ array_get($path, 'mode') }}</span></td>
                                        <td data-title="Date Modified"><span class="no-more-tables-cell">{{ \Carbon\Carbon::parse(array_get($path, 'mtime')) }}</span></td>
                                        <td data-title="Size"><span class="no-more-tables-cell">@byteToHumanReadable(array_get($path, 'size', 0))</span></td>
                                        <td data-title="Actions">
                                            <span class="no-more-tables-cell">
                                                <div class="dropdown">
                                                    <button
                                                            class="btn btn-secondary dropdown-toggle"
                                                            type="button"
                                                            id="{{ sprintf('button-download-%s', sha1(sprintf('/%s', ltrim(array_get($path, 'path'), '/')))) }}"
                                                            data-toggle="dropdown"
                                                            aria-haspopup="true"
                                                            aria-expanded="false">
                                                        <i class="fa fa-bars"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="{{ sprintf('button-download-%s', sha1(sprintf('/%s', ltrim(array_get($path, 'path'), '/')))) }}">
                                                        <button class="dropdown-item do-download do-post" data-formid="{{ sprintf('download-%s', sha1(sprintf('/%s', ltrim(array_get($path, 'path'), '/')))) }}">
                                                            Download
                                                            {{ Form::open([
                                                                    'url' => route('backup.download', ['backup' => $backup]),
                                                                    'hidden' => true,
                                                                    'id' => sprintf('download-%s', sha1(sprintf('/%s', ltrim(array_get($path, 'path'), '/')))),
                                                            ]) }}
                                                                {{ Form::hidden('download_token', Str::uuid()) }}
                                                                {{ Form::hidden('folder', sprintf('/%s', ltrim(array_get($path, 'path'), '/'))) }}
                                                            {{ Form::close() }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </span>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @endif
                    @else
                        @foreach($backup->paths as $path)
                            <tr>
                                <td scope="row" data-title="Type"><i class="icon-layers no-more-tables-cell"></i></td>
                                <td data-title="Name">
                                    <a class="wait-for-reload btn-link btn-block no-more-tables-cell" data-prevent-default="true" href="{{ route('backup.show', ['backup' => $backup, 'folder' => $path]) }}">{{ $path }}</a>
                                </td>
                                <td data-title="Owner"></td>
                                <td data-title="Group"></td>
                                <td data-title="Permissions"></td>
                                <td data-title="Date Modified"></td>
                                <td data-title="Size"></td>
                                <td data-title="Actions"></td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
