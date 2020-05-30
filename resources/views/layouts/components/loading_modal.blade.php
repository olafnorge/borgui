<div class="modal fade loading" id="loadingModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-info h-100 d-flex flex-column justify-content-center my-0" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fa fa-circle-o-notch fa-lg fa-spin"></i>
                    Loading Contents
                </h4>
            </div>
            <div class="modal-body">
                <p>Please wait while the content gets loaded. This may take a while because this is a remote action.</p>
                <p class="additional-message"></p>
            </div>
            @hasSection('loadingModalFooter')
                @yield('loadingModalFooter')
            @endif
        </div>
    </div>
</div>
