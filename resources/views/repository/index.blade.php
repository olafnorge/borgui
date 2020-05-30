@extends('layouts.app')

@section('content')
    @if($repositories->total())
        @foreach($repositories as $repository)
            <div class="card">
                <div class="card-header bg-primary"><h2>{{ $repository->name }}</h2></div>
                <div class="card-body pt-2 pr-2 pb-2 pl-2">
                    <div class="card-group">
                        <div class="card border-0 pr-lg-2">
                            <div class="card-header bg-gray-200"><h5 class="mb-0">Repository</h5></div>
                            <div class="card-body p-1 pt-2">
                                <dl class="row mb-0">
                                    <dt class="col-12 col-md-4">Name</dt>
                                    <dd class="col-12 col-md-8 text-lg-right">{{ $repository->name }}</dd>
                                    <dt class="col-12 col-md-4">Backup Count</dt>
                                    <dd class="col-12 col-md-8 text-lg-right">{{ $repository->backup_count }}</dd>
                                    <dt class="col-12 col-md-4">Chunks</dt>
                                    <dd class="col-12 col-md-8 text-lg-right">{{ $repository->total_chunks }}</dd>
                                    <dt class="col-12 col-md-4">Unique chunks</dt>
                                    <dd class="col-12 col-md-8 text-lg-right">{{ $repository->unique_chunks }}</dd>
                                    <dt class="col-12 col-md-4">Original size</dt>
                                    <dd class="col-12 col-md-8 text-lg-right">@byteToHumanReadable($repository->size)</dd>
                                    <dt class="col-12 col-md-4">Compressed size</dt>
                                    <dd class="col-12 col-md-8 text-lg-right">@byteToHumanReadable($repository->compressed_size)</dd>
                                    <dt class="col-12 col-md-4">Deduplicated size</dt>
                                    <dd class="col-12 col-md-8 text-lg-right">@byteToHumanReadable($repository->deduplicated_size)</dd>
                                    <dt class="col-12 col-md-4">Repository</dt>
                                    <dd class="col-12 col-md-8 text-lg-right">{{ $repository->repository }}</dd>
                                </dl>
                            </div>
                        </div>
                        <div class="card border-0 pl-lg-2">
                            <div class="card-header bg-gray-200"><h5 class="mb-0">Last Backup</h5></div>
                            <div class="card-body p-1 pt-2">
                                @if($repository->last_backup)
                                    <dl class="row mb-0">
                                        <dt class="col-12 col-md-4">Name</dt>
                                        <dd class="col-12 col-md-8 text-lg-right">{{ $repository->last_backup->name }}</dd>
                                        <dt class="col-12 col-md-4">Start</dt>
                                        <dd class="col-12 col-md-8 text-lg-right">{{ $repository->last_backup->start ? $repository->last_backup->start->calendar() : '' }}</dd>
                                        <dt class="col-12 col-md-4">End</dt>
                                        <dd class="col-12 col-md-8 text-lg-right">{{ $repository->last_backup->end ? $repository->last_backup->end->calendar() : '' }}</dd>
                                        <dt class="col-12 col-md-4">Duration</dt>
                                        <dd class="col-12 col-md-8 text-lg-right">@duration($repository->last_backup->duration)</dd>
                                        <dt class="col-12 col-md-4">Files</dt>
                                        <dd class="col-12 col-md-8 text-lg-right">{{ $repository->last_backup->number_files }}</dd>
                                        <dt class="col-12 col-md-4">Original size</dt>
                                        <dd class="col-12 col-md-8 text-lg-right">@byteToHumanReadable($repository->last_backup->original_size)</dd>
                                        <dt class="col-12 col-md-4">Compressed size</dt>
                                        <dd class="col-12 col-md-8 text-lg-right">@byteToHumanReadable($repository->last_backup->compressed_size)</dd>
                                        <dt class="col-12 col-md-4">Deduplicated size</dt>
                                        <dd class="col-12 col-md-8 text-lg-right">@byteToHumanReadable($repository->last_backup->deduplicated_size)</dd>
                                        <dd class="col-12">
                                            <a href="{{ route('backup.show', ['backup' => $repository->last_backup]) }}" class="btn btn-block btn-outline-info bg-gray-100">
                                                <i class="icon-magnifier"></i>
                                                Browse
                                            </a>
                                        </dd>
                                    </dl>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-12 col-lg-4 pl-1 pr-1 pr-lg-2">
                            <a href="{{ route('repository.edit', ['repository' => $repository]) }}" class="btn btn-outline-primary btn-block">
                                <i class="icon-pencil"></i>
                                Edit
                            </a>
                        </div>
                        <div class="col-12 col-lg-4 pl-1 pl-lg-2 pr-1 pr-lg-2 pt-3 pt-lg-0">
                            <a href="{{ route('backup.index', ['repository_id' => $repository->id]) }}" class="btn btn-outline-info btn-block">
                                <i class="icon-layers"></i>
                                All Backups of this Repository
                            </a>
                        </div>
                        <div class="col-12 col-lg-4 pl-1 pl-lg-2 pr-1 pt-3 pt-lg-0">
                            <button class="btn btn-danger btn-block do-post" data-formid="delete-repository-{{ $repository->id }}" data-confirm="Are you sure?">
                                <i class="icon-trash"></i>
                                Delete
                                {{ Form::open(['url' => route('repository.destroy', ['repository' => $repository]), 'hidden' => true, 'id' => sprintf('delete-repository-%s', $repository->id), 'method' => 'DELETE']) }}{{ Form::close() }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        {{ $repositories->links() }}
    @else
        {{ session()->now('warning', 'You don\'t have any repositories. Please create one first.') }}
    @endif
@endsection
