$(function() {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /*
     |-----------------------------------------------------------
     |   left navigation
     |-----------------------------------------------------------
     */
    var sidebar_li = $('.sidebar-category li > a'),

    pathname = window.location.pathname.replace('menu-items', 'menus').split('/');
    pathname = pathname[1] + '/' + pathname[2];

    sidebar_li.each(function () {
        if ($(this).attr('href').indexOf(pathname) != -1) {
            return $(this).parent('li').addClass('active');
        }
    });

    /*
    |-----------------------------------------------------------
    |   Удаление элемента из списка
    |-----------------------------------------------------------
    */
    var table = $('.table-responsive table td');
    table.on('click', '.last__btn', function (e) {
        e.preventDefault();
        var _this = $(this),
            alias = _this.data('data-alias');
        return sendDestroyRequest(_this, alias);
    });

    var checkBox = $(".control-primary"),
        checkBoxStyled = $(".styled");

    checkBoxStyled.uniform({
        radioClass: 'choice'
    });

    checkBox.uniform({
        radioClass: 'choice',
        wrapperClass: 'border-primary-600 text-primary-800'
    });

    /*
    |-----------------------------------------------------------
    |   image actions
    |-----------------------------------------------------------
    */
    var editImageBox = jQuery('#edit-image'),
        imagesBox = jQuery('#_images_box');
    imagesBox.on('click', '.icon-pencil', function () {
        $.get(jQuery(this).closest('a').attr('href'), function(data){
            return editImageBox.html(data) && $(".control-primary").uniform({
                radioClass: 'choice',
                wrapperClass: 'border-primary-600 text-primary-800'
            });
        });
    });

    editImageBox.on('submit', 'form', function (e) {
        e.preventDefault();
        var _this = jQuery(this);
        return jQuery.ajax({
            url: _this.attr('action'),
            type: "POST",
            dataType: "json",
            data: _this.serialize(),
            error: function (error, message) {
                new PNotify({
                    title: 'Уведомление от сервера',
                    text: 'Произошла ошибка.<br />Повторите, пожалуйста, позже',
                    icon: 'icon-blocked',
                    type: 'error'
                });
            },
            success: function(data) {
                editImageBox.modal('hide');
                new PNotify({
                    title: 'Уведомление от сервера',
                    text: data.message,
                    icon: 'icon-checkmark3',
                    type: 'success'
                });
                return imagesBox.html(data.images);
            }
        });
    });

    imagesBox.on('click', '.icon-trash', function (e) {
        e.preventDefault();
        if(confirm('Вы уверены, что хотите удалить изображение?')) {
            var _this = jQuery(this);
            return $.ajax({
                url: _this.parent('a').attr('href'),
                type: "POST",
                data: {'_method': 'DELETE'},
                success: function (data) {
                    new PNotify({
                        title: 'Уведомление от сервера',
                        text: data.message,
                        icon: 'icon-checkmark3',
                        type: 'success'
                    });
                    return _this.closest('div.single_image').fadeOut().remove();
                }
            });
        }
    });

    $('.nav.navbar-nav').on('click', '#logout-btn', function (e) {
        e.preventDefault();
        return $(this).closest('li').find('form').trigger('submit');
    });

    $('.select-search').select2();
    $(".select-multiple-tags").select2({
        tags: true
    });

    if($('#editor-full').length) {
        CKEDITOR.replace('editor-full');
    }

    if($('#editor-props').length) {
        CKEDITOR.replace('editor-props');
    }

    if($('#full-props').length) {
        CKEDITOR.replace('full-props');
    }

    var tabsEditors = $("textarea.tabs__editor");
    if(tabsEditors) {
        tabsEditors.each(function(){
            var txt = $(this).attr('name');
            CKEDITOR.replace(txt , {
                height :'120'
            });
        });
    }

    if($('#editor-full2').length) {
        CKEDITOR.replace( 'editor-full2', {
            height: '120px',
            toolbarGroups: [
                { name: 'links' },
                { name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
                { name: 'others' },
                { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
                { name: 'paragraph',   groups: [ 'blocks', 'align', 'bidi' ] },
                { name: 'colors' }
            ]
        });
    }

    $(".file-styled-primary").uniform({
        fileButtonClass: 'action btn bg-blue',
        fileDefaultHtml: 'Изображение не выбрано',
        fileButtonHtml: 'Выбрать'
    });

    var modalInfo = $('#modal_info'),
        imageBox = $('#image__box');
    modalInfo.on('submit', 'form', function (e) {
        e.preventDefault();
        var _this = jQuery(this);
        return jQuery.ajax({
            url: _this.attr('action'),
            type: "POST",
            dataType: "json",
            data: _this.serialize(),
            success: function(data) {
                modalInfo.modal('hide');
                return new PNotify({
                    title: 'Уведомление от сервера',
                    text: data.message,
                    icon: 'icon-checkmark3',
                    type: 'success'
                });
            }
        });
    });

    imageBox.on('click', '.delete__img', function () {
        if(confirm('Вы уверены, что хотите удалить изображение?')) {
            var _this = jQuery(this);
            return $.ajax({
                url: _this.attr('data-href'),
                type: "POST",
                data: {'_method': 'DELETE'},
                success: function (data) {
                    new PNotify({
                        title: 'Уведомление от сервера',
                        text: data.message,
                        icon: 'icon-checkmark3',
                        type: 'success'
                    });
                    return imageBox.fadeOut();
                }
            });
        }
    });

    // Initialize with options
    $(".select-icons").select2({
        templateResult: iconFormat,
        minimumResultsForSearch: Infinity,
        templateSelection: iconFormat,
        escapeMarkup: function(m) { return m; }
    });

});

// Format icon
function iconFormat(icon) {
    var originalOption = icon.element;
    if (!icon.id) { return icon.text; }
    //var $icon = "<i class='icon-" + $(icon.element).data('icon') + "'></i>" + icon.text;

    var $icon = "<svg class=\"icon\">\n" +
            "<use xlink:href=\"/img/symbols.svg#" + $(icon.element).data('icon') + "\"></use>\n" +
        "</svg>" + icon.text;

    return $icon;
}

function sendDestroyRequest(_this, alias = '') {
    var notice = new PNotify({
        title: 'Предупреждение',
        text: '<p>Вы действительно хотите удалить?</p>',
        hide: false,
        type: 'info',
        icon: 'icon-info22',
        confirm: {
            confirm: true,
            buttons: [
                {
                    text: 'Да',
                    addClass: 'btn btn-sm btn-primary'
                },
                {
                    text: 'Нет',
                    addClass: 'btn btn-sm btn-link'
                }
            ]
        },
        buttons: {
            closer: false,
            sticker: false
        },
        history: {
            history: false
        }
    });

    // On confirm
    notice.get().on('pnotify.confirm', function() {
        if(alias == 'index'){
            return new PNotify({
                title: 'Осторожно',
                text: 'Нельзя удалить главную страницу',
                icon: 'icon-blocked',
                type: 'error'
            });
        }
        return _this.closest('form').trigger('submit');
    });
}