/* Embroidery App — super animation interactions */
(function ($) {
    $(function () {

        // Sidebar toggle (mobile)
        $('#sidebarToggle').on('click', function (e) {
            e.preventDefault();
            $('body').toggleClass('sidebar-open');
            $('.main-sidebar').toggleClass('sidebar-open');
        });
        if ($(window).width() <= 576) {
            $('.nav-link').on('click', function () {
                if (!$(this).attr('data-widget')) {
                    $('body').removeClass('sidebar-open');
                    $('.main-sidebar').removeClass('sidebar-open');
                }
            });
        }

        // Add a focus animation class to flash any input the user just touched
        $('input, select, textarea').on('focus', function () {
            $(this).closest('.form-card, .users.form, .jobs.form')
                .removeClass('anim-flash')
                .addClass('anim-flash');
        });

        // Submit button loading spinner
        $(document).on('submit', 'form', function () {
            var $btn = $(this).find('button[type="submit"], input[type="submit"]').first();
            if ($btn.length && !$btn.prop('disabled')) {
                var label = $btn.text();
                $btn.data('label', label);
                $btn.prop('disabled', true);
                $btn.html('<span class="spinner-ring"></span>' + label);
                // Safety: re-enable after 8s in case of network error
                setTimeout(function () {
                    if ($btn.prop('disabled')) {
                        $btn.prop('disabled', false);
                        $btn.html(label);
                    }
                }, 8000);
            }
        });

        // Reveal alerts then auto-fade
        setTimeout(function () {
            $('.alert.alert-success, .alert.alert-info').each(function (i, el) {
                $(el).css({ transition: 'opacity .6s ease, transform .6s ease', opacity: 0, transform: 'translateY(-6px)' });
                setTimeout(function () { $(el).remove(); }, 4000 + i * 200);
            });
        }, 1500);

        // Pop effect on primary buttons when page loads
        $('.btn-primary, .form-card .btn-primary').each(function (i, el) {
            $(el).css('animation', 'pop .6s ease ' + (i * 0.1) + 's both');
        });

        // ========== Automatic testing modal ==========
        var autoTestLoaded = false;
        function loadAutoTestPanel() {
            if (autoTestLoaded) return $.Deferred().resolve();
            var d = $.Deferred();
            $.get(embroideryBaseUrl + '/system-tests/panel')
                .done(function (html) {
                    $('#autoTestMount').html(html);
                    autoTestLoaded = true;
                    bindAutoTest();
                    d.resolve();
                })
                .fail(function () { d.reject(); });
            return d.promise();
        }
        function bindAutoTest() {
            var csrfToken = $('#autoTestCsrfToken').text();
            $('#autoTestRun').on('click', function () {
                var $btn = $(this);
                $btn.prop('disabled', true).html('<span class="spinner-ring"></span>Running...');
                $('#autoTestProgress').show();
                $('#autoTestResults').html('');
                $('#autoTestBar').css('width', '5%');
                $('#autoTestStatus').text('Starting tests...');
                var progressTimer = setInterval(function () {
                    var w = parseInt($('#autoTestBar').css('width')) / $('#autoTestBar').parent().width() * 100;
                    if (w < 90) $('#autoTestBar').css('width', (w + 1) + '%');
                }, 400);
                $.ajax({
                    url: embroideryBaseUrl + '/system-tests/run',
                    method: 'POST',
                    dataType: 'json',
                    data: { _csrfToken: csrfToken }
                }).done(function (data) {
                    clearInterval(progressTimer);
                    $('#autoTestBar').css('width', '100%');
                    renderAutoTestResults(data);
                }).fail(function (xhr) {
                    clearInterval(progressTimer);
                    $('#autoTestBar').removeClass('bg-primary').addClass('bg-danger');
                    $('#autoTestStatus').text('Request failed');
                    $('#autoTestResults').html(
                        '<div class="alert alert-danger mb-0"><strong>Request failed</strong><br>' +
                        $('<div>').text(xhr.responseText || xhr.statusText).html() + '</div>'
                    );
                }).always(function () {
                    $btn.prop('disabled', false).html('<i class="fas fa-play me-1"></i>Run again');
                });
            });
        }
        function renderAutoTestResults(data) {
            $('#autoTestStatus').text('Completed in ' + data.total_ms + ' ms');
            var summary = $('<div class="mb-3"></div>');
            var cls = data.failed === 0 ? 'success' : 'warning';
            summary.append(
                '<div class="alert alert-' + cls + ' d-flex justify-content-between align-items-center mb-3">' +
                '  <div><strong>' + data.passed + '</strong> passed, <strong>' + data.failed + '</strong> failed (of ' + (data.passed + data.failed) + ')</div>' +
                '  <small class="text-muted">' + data.php + ' · ' + data.db_driver + '</small>' +
                '</div>'
            );
            var list = $('<div class="list-group"></div>');
            data.results.forEach(function (r) {
                var icon = r.status === 'pass' ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';
                list.append(
                    '<div class="list-group-item d-flex justify-content-between align-items-start">' +
                    '  <div class="ms-2 me-auto">' +
                    '    <div class="fw-semibold"><i class="fas ' + icon + ' me-2"></i>' + $('<div>').text(r.name).html() + '</div>' +
                    (r.detail ? '<small class="text-muted d-block ms-4">' + $('<div>').text(r.detail).html() + '</small>' : '') +
                    '  </div>' +
                    '  <span class="badge bg-light text-dark">' + r.ms + ' ms</span>' +
                    '</div>'
                );
            });
            $('#autoTestResults').empty().append(summary).append(list);
        }
        // Determine base URL once
        var embroideryBaseUrl = (function () {
            var b = $('base[href]').attr('href') || '';
            if (b) return b.replace(/\/$/, '');
            // Fallback: derive from current path (/embroidery_app/...)
            var m = window.location.pathname.match(/^(\/[^\/]+)/);
            return m ? m[1] : '';
        })();
        $('#openAutoTestBtn').on('click', function () {
            loadAutoTestPanel().done(function () {
                var m = bootstrap.Modal.getOrCreateInstance(document.getElementById('autoTestModal'));
                m.show();
            });
        });

    });
})(jQuery);