@extends('layouts.app')

@section('content')
    @if($backups->total())
        @foreach($backups as $backup)
            <div class="card">
                <div class="card-header bg-primary"><h2>{{ $backup->name }} <span class="h5 text-secondary">{{ $backup->repository->name }}</span> </h2></div>
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
                <div class="card-footer">
                    <div class="row">
                        <div class="col-12 {{-- col-lg-6 --}} pl-1 pr-1 pr-lg-2">
                            <a href="{{ route('backup.show', ['backup' => $backup]) }}" class="btn btn-outline-primary btn-block">
                                <i class="icon-magnifier"></i>
                                Browse
                            </a>
                        </div>
{{--                        <div class="col-12 col-lg-6 pl-1 pl-lg-2 pr-1 pt-3 pt-lg-0">--}}
{{--                            <button class="btn btn-danger btn-block do-post" data-formid="delete-backup-{{ $backup->id }}" data-confirm="Are you sure?">--}}
{{--                                <i class="icon-trash"></i>--}}
{{--                                Delete--}}
{{--                                {{ Form::open(['url' => route('backup.destroy', ['backup' => $backup]), 'hidden' => true, 'id' => sprintf('delete-backup-%s', $backup->id), 'method' => 'DELETE']) }}{{ Form::close() }}--}}
{{--                            </button>--}}
{{--                        </div>--}}
                    </div>
                </div>
            </div>
        @endforeach

        {{ $backups->links() }}
    @else
        {{ session()->now('warning', 'You don\'t have any backups. Please create one first.') }}
    @endif
@endsection
