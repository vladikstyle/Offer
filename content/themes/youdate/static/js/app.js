$.fn.serializeFiles = function() {
    var $obj = $(this);
    var formData = new FormData();
    $.each($obj.find("input[type='file']"), function(i, tag) {
        $.each($(tag)[0].files, function(i, file) {
            formData.append(tag.name, file);
        });
    });
    var params = $obj.serializeArray();
    $.each(params, function (i, val) {
        formData.append(val.name, val.value);
    });
    return formData;
};

var delay = (function(){
    var timer = 0;
    return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
    };
})();

var isEdge = window.navigator.userAgent.indexOf("Edge") > -1;
var isChrome = !!window.chrome && !isOpera;
var isExplorer= typeof document !== 'undefined' && !!document.documentMode && !isEdge;
var isFirefox = typeof window.InstallTrigger !== 'undefined';
var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
var isOpera = !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;

$(document).ready(function() {
    var $body = $('body'),
        $window = $(window),
        $document = $(document);

    /**
     * Tooltip plugin
     */
    $body.tooltip({
        selector: '[rel=tooltip]',
        container: 'body',
        placement: 'auto'
    });

    /**
     * Bootbox plugin
     */
    bootbox.setDefaults({
        show: true,
        backdrop: true,
        closeButton: false,
        animate: true,
        buttons: {
            cancel: {
                className: 'btn btn-secondary'
            }
        }
    });

    /**
     * Messenger plugin
     */
    Messenger.options = {
        extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-left',
        theme: 'flat'
    };
    $(document).ajaxError(function(event, jqXHR, ajaxSettings, thrownError) {
        if (thrownError == 'abort' || thrownError == 'timeout') {
            return;
        }

        if (jqXHR.responseText) {
            var data = eval("(" + jqXHR.responseText + ")");
            Messenger().post({
                message: data.message || data,
                type: 'error',
                hideAfter: 5,
                hideOnNavigate: true
            });
        }
    });

    $body.on('click', '.settings-photos .photo-make-primary', function(event) {
        var $btn = $(this);
        $.ajax({
            url: $btn.attr('data-url'),
            method: 'post',
            success: function(data) {
                if (data.success) {
                    Messenger().post({
                        message: data.message,
                        type: 'success',
                        hideAfter: 5,
                        hideOnNavigate: true
                    });
                    $('.settings-photos .photo-make-primary').removeClass('btn-primary btn-secondary').addClass('btn-secondary');
                    $btn.removeClass('btn-secondary').addClass('btn-primary');
                } else {
                    Messenger().post({
                        message: data.message,
                        type: 'error',
                        hideAfter: 5,
                        hideOnNavigate: true
                    });
                }
            }
        });
    });

    $body.on('click', '.settings-photos .photo-delete', function(event) {
        var $btn = $(this);
        if (confirm($btn.data('confirmation'))) {
            $.ajax({
                url: $btn.attr('data-url'),
                method: 'post',
                success: function(data) {
                    if (data.success) {
                        Messenger().post({
                            message: data.message,
                            type: 'success',
                            hideAfter: 5,
                            hideOnNavigate: true
                        });
                        $btn.parent('.photo-item').fadeOut();
                    } else {
                        Messenger().post({
                            message: data.message,
                            type: 'error',
                            hideAfter: 5,
                            hideOnNavigate: true
                        });
                    }
                }
            });
        }
    });

    /**
     * Likes
     */
    $body.on('click', '.btn-like-toggle', function(event) {
        var $btn = $(this);
        $btn.attr('disabled', 'disabled');

        $.ajax({
            url: $btn.attr('data-url'),
            method: 'post',
            success: function (data) {
                if (data.success) {
                    if (data.liked == true) {
                        $btn.attr('title', $btn.attr('data-like-delete-title'));
                        $btn.find('i.fa').removeClass('fa-heart-o').addClass('fa-heart');
                        $btn.removeClass('btn-not-liked').addClass('btn-liked');
                    } else {
                        $btn.attr('title', $btn.attr('data-like-create-title'));
                        $btn.find('i.fa').removeClass('fa-heart').addClass('fa-heart-o');
                        $btn.removeClass('btn-liked').addClass('btn-not-liked');
                    }
                    $btn.tooltip('dispose');
                    $btn.tooltip('enable');
                }
            },
            complete: function () {
                $btn.attr('disabled', false);
            }
        })
    });

    /**
     * Custom
     */
    $window.on('scroll', function () {
        if ($document.scrollTop() > 150) {
            $body.addClass('body-scrolled-down');
        } else {
            $body.removeClass('body-scrolled-down');
        }
    });

    $body.on('beforeSubmit', '.modal-form form', function(event) {
        event.preventDefault();
        var $form = $(this),
            $buttons = $form.find('.btn'),
            $modal = $form.closest('.modal');
        $.ajax({
            url: $form.attr('action'),
            data: new FormData(this),
            processData: false,
            contentType: false,
            type: $form.attr('method'),
            beforeSend: function() {
                $buttons.attr('disabled', 'disabled');
            },
            success: function(response) {
                if (response.success) {
                    $('.modal.in').modal('hide');
                    Messenger().post({
                        message: response.message,
                        type: 'success'
                    });
                    $form[0].reset();
                    $form.trigger('afterSubmit', event);
                    $modal.modal('hide');
                    if ($form.data('pjax-container') && $($form.data('pjax-container')).length > 0) {
                        $.pjax.reload({ container: $form.data('pjax-container') });
                    }
                    if (response.balance) {
                        $('.user-balance').text(response.balance);
                    }
                } else if (response.errors) {
                    showErrors($form.find('.error-summary'), response.errors);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Messenger().post({message: jqXHR.data, type: 'error'});
            },
            complete: function () {
                $buttons.attr('disabled', false);
            }
        });

        return false;
    });

    function showErrors($element, messages) {
        var output = '';
        $.each(messages, function(i, error) {
            output += '<li>' + error + '</li>';
        });
        $element.html('<ul>' + output + '</ul>');
        $element.show();
    }

    // AJAX buttons
    $body.on('click', '.btn-ajax', function(event) {
        event.preventDefault();
        var $btn = $(this),
            url = $btn.attr('href') || $btn.attr('data-action'),
            confirmText = $btn.data('confirm-title');

        var callback = function() {
            $.ajax({
                url: url,
                method: $btn.data('type'),
                data: $btn.data('data'),
                beforeSend: function() {
                    $btn.attr('disabled', 'disabled');
                },
                success: function(response) {
                    $btn.attr('disabled', false);
                    if (response.success) {
                        Messenger().post({message: response.message, type: 'success'});
                        if ($btn.data('pjax-container')) {
                            $.pjax.reload({ container: $btn.data('pjax-container') });
                        }
                    } else if (response.message) {
                        Messenger().post({ message: response.message, type: 'error' });
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $btn.attr('disabled', false);
                },
                complete: function() {
                    $btn.attr('disabled', false);
                }
            });
        };

        if (!confirmText) {
            callback();
        } else {
            bootbox.confirm({
                message: $btn.attr('data-confirm-title'),
                title: $btn.attr('data-title'),
                buttons: {
                    confirm: {
                        label: $btn.attr('data-confirm-button'),
                        className: 'btn-danger'
                    },
                    cancel: {
                        label: $btn.attr('data-cancel-button'),
                        className: 'btn-secondary'
                    }
                },
                callback: function (result) {
                    if (!result) {
                        return;
                    }
                    callback();
                }
            });
        }

        return false;
    });

    /**
     * New message form
     */
    $('.btn-new-message').on('click', function (e) {
        var $target = $(e.target);
        var modalSelector = $target.data('target');
        $(modalSelector + ' .placeholder-contact-id').val($target.data('contact-id'));
        $(modalSelector + ' .placeholder-title').text($target.data('title'));
        $(modalSelector + ' form').trigger('reset');
        $(modalSelector + ' form textarea').focus();
    });

    /**
     * Visibility toggle
     */
    $body.on('click', '[data-toggle-visibility-target]', function(event) {
        var $target = $($(this).attr('data-toggle-visibility-target')),
            cookieKey = $(this).attr('data-toggle-visibility-cookie');
        $target.toggleClass("hidden");
        Cookies.set(cookieKey, $target.hasClass("hidden") ? 0 : 1, { expires: 3650 });
        event.preventDefault();
    });

    $.each($('[data-toggle-visibility-target]'), function(idx, element) {
        var $element = $(element),
            $target = $($element.attr('data-toggle-visibility-target')),
            cookieKey = $element.attr('data-toggle-visibility-cookie');
        if (Cookies.get(cookieKey) == 1) {
            $target.removeClass("hidden");
        } else if (Cookies.get(cookieKey) === 0) {
            $target.addClass("hidden");
        }
    });

    $body.on('click', '.btn-toggle-sidebar', function(event) {
        var $btn = $(this),
            $sidebar = $('.sidebar-menu'),
            $sidebarLine = $('.sidebar-menu-line');

        $sidebar.toggleClass('d-none');
        $sidebarLine.toggleClass('hidden');
        if ($sidebar.hasClass('d-none')) {
            $btn.find('.fe').removeClass($btn.attr('data-icon-hide')).addClass($btn.attr('data-icon-show'));
        } else {
            $btn.find('.fe').removeClass($btn.attr('data-icon-show')).addClass($btn.attr('data-icon-hide'));
        }
    });

    $body.on('click', '.sidebar-menu .list-group-item', function(event) {
        let $sidebar = $(this).parent('.sidebar-menu'),
            $current = $(this),
            $active = $sidebar.find('.list-group-item.active');

        $active.removeClass('active');
        $active.find('.badge').removeClass('badge-primary').addClass('badge-secondary');
        $current.addClass('active').find('.badge').removeClass('badge-secondary').addClass('badge-primary');
    });

    $body.on('click', '.nav .nav-link', function(event) {
        let $nav = $(this).closest('.nav'),
            $currentNavItem = $(this).closest('.nav-item'),
            $currentNavLink = $currentNavItem.find('.nav-link'),
            $activeNavItem = $nav.find('.nav-item.active'),
            $activeNavLink = $activeNavItem.find('.nav-link');

        $activeNavItem.removeClass('active');
        $activeNavLink.removeClass('active');
        $currentNavItem.addClass('active');
        $currentNavLink.addClass('active');
    });

    /**
     * Auth/Register modals
     */
    $('.modal-auth, .modal-register').on('shown.bs.modal', function() {
        $('input:text:visible:first', this).focus();
    });

    /**
     * Posts
     */
    $body.on('click', '.post .btn-comments', function(event) {
        let $post = $(this).closest('.post');
        $post.find('.post-comments').toggleClass('hidden');
    });
    $body.on('focus', '.post-new .post-content-field', function(event) {
        let $contentField = $(this),
            $postNew = $contentField.closest('.post-new');

        $postNew.addClass('activated');
        $contentField.attr('rows', 3);
    });

    /**
     * Profile
     */
    $body.on('click', '.modal-upload-photo .btn', function(event) {
        Cookies.set($(this).data('cookie-name'), 1);
    });

    /**
     * Pjax events
     */
    $(document).on('pjax:send', function(event) {
        NProgress.start();
    });
    $(document).on('pjax:complete', function(event) {
        NProgress.done();
        var $target = $(event.target),
            scrollTo = $target.attr('data-pjax-scroll-to');
        if (scrollTo) {
            $("html, body").animate({ scrollTop: $(scrollTo).offset().top }, 300);
        }
    });

    if (isExplorer) {
        $('body').addClass('ie');
    }
});

function appBaseUrl() {
    return $('meta[name=baseUrl]').attr("content");
}

function first(p) {
    for (var i in p) {
        return p[i];
    }
}
