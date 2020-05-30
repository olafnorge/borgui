'use strict';

import $ from 'jquery'
import Popper from 'popper.js';
import Pace from 'pace-progress';
import PerfectScrollbar from 'perfect-scrollbar';
import '@coreui/coreui';

require('bootstrap');

// make imports global
window.Popper = Popper;
window.$ = window.jQuery = $;
window.Cookies = require('js-cookie');
window.Pace = Pace;
window.PerfectScrollbar = PerfectScrollbar;

// always send CSRF token with each ajax request
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// run pace
Pace.start();
Pace.options = {
    ajax: false
};

// page wide functions
$('[data-toggle="popover"]').popover();
$('.popover-dismiss').popover({
    trigger: 'focus'
});

// tooltips
$('[data-toggle="tooltip"]').tooltip();

// transform links to post
$('.do-post').on('click', function (event) {
    event.preventDefault();

    if ($(this).data('confirm')) {
        if ($(this).data('formid') && confirm($(this).data('confirm'))) {
            $('#' + $(this).data('formid')).submit();
        }
    } else if ($(this).data('formid')) {
        $('#' + $(this).data('formid')).submit();
    }
});

// loading modal if page load is expected to take longer
$('.wait-for-reload').on('click', function (event) {
    if ($(this).data('prevent-default')) {
        event.preventDefault();
    }

    $('#loadingModal').modal({
        backdrop: false,
        keyboard: false,
        show: true
    });
});
