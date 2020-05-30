<div class="sidebar">
    <nav class="sidebar-nav">
        <ul class="nav">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('repository.index') }}">
                    <i class="nav-icon icon-folder"></i> Repositories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('backup.index') }}">
                    <i class="nav-icon icon-layers"></i> Backups
                </a>
            </li>
            @if(config('horizon.dashboard_enabled', false) && Auth::user()->horizon_allowed)
                <li class="nav-title">External Tools</li>
                <li class="nav-item">
                    <a class="nav-link" rel="noopener" target="_blank" href="{{ route('horizon.index') }}">
                        <i class="nav-icon icon-speedometer"></i>
                        Horizon
                    </a>
                </li>
            @endif
        </ul>
    </nav>
    <span class="nav-title">Version {{ config('logging.channels.rollbar.code_version') }}</span>
    <button class="sidebar-minimizer brand-minimizer" type="button"></button>
</div>
