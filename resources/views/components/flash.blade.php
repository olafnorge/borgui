@if($message)
    @if(!Auth::check() || session('resent'))
        <div class="row justify-content-center">
            <div class="col-md-4">
    @endif

    <div class="card-accent-{{ $type }} alert alert-{{ $type }} alert-dismissible fade show" role="alert">
        {{ $message }}

        <button class="close" type="button" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
    </div>

    @if(!Auth::check() || session('resent'))
            </div>
        </div>
    @endif
@endif
