@if ($breadcrumbs)
    <ol class="breadcrumb">
        @foreach ($breadcrumbs as $breadcrumb)
            @if ($breadcrumb->url && !$loop->last)
                <li class="breadcrumb-item">
                    <a href="{{ $breadcrumb->url }}">{{ $breadcrumb->title }}</a>
                </li>
            @else
                <li class="breadcrumb-item text-truncate active">{{ $breadcrumb->title }}</li>
            @endif
        @endforeach

        @if(isset($breadcrumb->menu))
            <li class="breadcrumb-menu">
                <div class="btn-group" role="group">
                    @if(is_array($breadcrumb->menu))
                        @foreach($breadcrumb->menu as $item)
                            {{ $item }}
                        @endforeach
                    @else
                        {{ $breadcrumb->menu }}
                    @endif
                </div>
            </li>
        @endif
    </ol>
@endif
