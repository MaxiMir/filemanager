$(function () {
    root = $('#root').text();
    leftNavSize = 285;
    tbody = $('#table_files tbody');
    relUrl = $('main').attr('data-relurl');


    /**
     * HANDLER: change size left menu
     */
    $('.block-list_paths').resizable({
        handles: 'e',
        minWidth: 285,
        maxWidth: 418,
        start: function ( event, ui ) {
            $('#table_files').resizable({
                disabled: false,
                handles: 'w',
                minWidth: 558,
                maxWidth: 825
            });
        },
        resize: function ( event, ui ) {
            const width = $('.main_content').width() - ui.size.width - 30; // 30 - left, right padding
            const left = ui.size.width - leftNavSize;

            $('#table_files').css({
                'left': left + 'px',
                'width': width + 'px'
            });
        },
        stop: function ( event, ui ) {
            $('#table_files').resizable({ disabled: true });
        }
    });


    /**
     * HANDLER: open folders in left menu
     */
    $('#list_paths').on('click', '.nav_control-link', function () {
        const path = $(this).attr('data-path');
        generateChildList(path);
    });


    /**
     * HANDLER: redactor
     */
    $('#activateRedactor').on('click', function () {
        activateRedactor();
    });

    $('#controlCheckbox').on('click', function () {
        if ($(this).is(':checked')) {
            $('#table_files tbody input:checkbox').prop('checked', true);
        } else {
            $('#table_files tbody input:checkbox').prop('checked', false);
        }
    });

    $('#table_files').on('click', 'input:checkbox, .selectable', function (e) {
        const isHiddenActionPanel = $('#actions-panel').hasClass('d-none');

        if (!isHiddenActionPanel) {
            const operationLinks = $('#actions-panel th a');
            const input = $(this)
                .closest('tr')
                .find('input');
            const checked = input.prop('checked');

            if ($(this).attr('type') !== 'checkbox') {
                e.preventDefault();
                input.prop('checked', !checked);
            }

            const countCheckbox = $('tbody :checkbox').length;
            const countCheckedCheckbox = $('tbody :checkbox:checked').length;
            const valuePropChecked = (countCheckbox === countCheckedCheckbox);

            $('#controlCheckbox').prop('checked', valuePropChecked);

            $.each(operationLinks, function () {
                if (countCheckedCheckbox === 0) {
                    $(this).addClass('disabled');
                } else {
                    $(this).removeClass('disabled');
                }
            });
        }
    });


    /**
     * HANDLER: close operations panel
     */
    $('#table_files').on('click', '#closePanel', function () {
        deactivateRedactor();
    });


    /**
     * HANDLER: modals reset
     */
    $('a[data-toggle="modal"]').on('click', function () {
        const idModal = $(this).attr('data-target');
        const form = $('#' + $(idModal).find('form').attr('id'));
        const error_msg = form.find('.error_msg');

        form[0].reset();
        error_msg.empty();
    });


    /**
     * HANDLER: click outside the dynamic modal window
     */
    $(document).on('click', function (e) {
        const modal = $("#modalQuestion");

        if ($('footer').is(':has(#modalQuestion)')) {
            if (!modal.is(e.target) && modal.has(e.target).length === 0) {
                $('#modalQuestion').detach();
            }
        }
    });


    /**
     * HANDLER: modals create files
     */
    $('.create_files input[type=text]').on('blur', function () {
        const inputVal = $(this).val();
        const errorField = $(this).next();
        const btnSbm = $(this)
            .closest('form')
            .find('button[type="submit"]');

        if (inputVal) {
            if (isValidName(inputVal)) {
                errorField.empty();
                btnSbm.prop('disabled', false);
            } else {
                errorField.html('It is recommended not to use these symbols:<br> "! @ # $ & ~ % * ( ) [ ] { } \' \" \\ / : ; > < ` " and space in the file name');
                btnSbm.attr('disabled', 'disabled');
            }
        }
    });

    $('.form-create_files').on('submit', function (e) {
        const currForm = $('#' + $(this).attr('id'));
        const errorField = currForm.find(".error_msg");
        const input = currForm.find("input[type=text]");
        const inputVal = input.val();
        const btnSbm = currForm.find("button[type='submit']");
        const btnCls = currForm.find("button[data-dismiss='modal']");
        const type = input.attr('data-type'); console.log(type);
        const infoListForUpdate = getInfoListForUpdate();

        e.preventDefault();

        $.post({
            url: relUrl + 'Utils/CreateFiles.php',
            dataType: 'json',
            data: {
                'name': inputVal,
                'type': type
            },
            beforeSend: function (data) {
                if (!$('#actions-panel').hasClass('d-none')) {
                    deactivateRedactor();
                }
                btnSbm.attr('disabled', 'disabled');
            },
            success: function (data) {
                if (data['result'] === 'error') {
                    errorField.html(data['msg']);
                } else {
                    btnCls.trigger('click');
                    $.when(tbody
                        .html(data['content']))
                        .done(function () {
                            if (type === 'folder') {
                                updateChildLists(infoListForUpdate);
                            }
                            showThenHideMsg(`${inputVal} successfully created`);
                        });
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                errorField.html("Status: " + xhr.status + "<br>" + thrownError);
            },
            complete: function (data) {
                btnSbm.prop('disabled', false);
            }
        });
    });


    /**
     * HANDLER: modal uploads files
     */
    $('#formUploadsFiles').on('submit', function (e) {
            const form = $(this);
            const modalBody = form.find('.modal-body');
            const progressBar = form.find('.progress');
            const errorField = form.find(".error_msg");
            const btnCls = form.find("button[data-dismiss='modal']");
            const btnSbm = form.find("button[type='submit']");
            const data = new FormData();
            const files = $('#newFiles').get(0).files;
            const infoListForUpdate = getInfoListForUpdate();

            e.preventDefault();

            if (files.length === 0) {
                errorField.text('Please, select files');
            } else {
                $.each($('#newFiles')[0].files, function (i, file) {
                    data.append('file-' + i, file);
                });

                $.post({
                    url: relUrl + 'Utils/UploadsFiles.php',
                    xhr: function () {
                        const myXhr = $.ajaxSettings.xhr();

                        if (myXhr.upload) {
                            myXhr.upload.addEventListener('progress', changeProgressBar, false);
                        }
                        return myXhr;
                    },
                    data: data,
                    cache: false,
                    contentType: false, // дефолтные установки jQuery равны application/x-www-form-urlencoded, что не предусматривает отправку файлов
                    processData: false, // jQuery будет конвертировать массив files в строку => сервер не сможет получить данные.
                    beforeSend: function () {
                        if (!$('#actions-panel').hasClass('d-none')) {
                            deactivateRedactor();
                        }
                        modalBody.fadeOut('fast', function () {
                            btnSbm.attr('disabled', 'disabled');
                            progressBar.toggleClass('d-none d-block');
                        });
                    },
                    success: function (data) {
                        if (data['result'] === 'error') {
                            errorField.html(data['msg']);
                        } else {
                            btnCls.trigger('click');
                            $.when(tbody
                                .html(data['content']))
                                .done(function () {
                                    showThenHideMsg('Files successfully uploaded');
                                });
                        }
                    },
                    error: errorHandler = function () {
                        errorField.text('Error loading files...');
                    },
                    complete: function (data) {
                        setTimeout(function () {
                            $('#formUploadsFiles button[type=submit]').prop('disabled', false);
                        }, 500);
                        btnSbm.prop('disabled', false);
                        progressBar.toggleClass('d-none d-block');
                        modalBody.fadeIn();
                    }
                });
            }
        }
    );


    /**
     * HANDLER: rename files
     */
    $('#table_files').on('click', '[data-action="rename"]', function (e) {
        const fileName = $(this).attr('data-name');
        const renameLinks = $('[data-action="rename"]');
        const linkFile = $('.link_files[data-name="' + fileName + '"]');
        const parentLinkFile = linkFile.closest('.link-file');
        const renameForm = $('<div>', {
            class: 'input-group',
            append: $('<input>', {
                class: 'form-control',
                type: 'text',
                placeholder: 'Enter name',
                value: '',
                autofocus: 'autofocus',
                'data-name': fileName
            })
                .add($('<div>', {
                    class: 'input-group-append',
                    append: $('<button>', {
                        type: 'submit',
                        class: 'btn btn-outline-secondary btn-ok',
                        text: 'OK'
                    })
                        .add($('<button>', {
                            type: 'button',
                            class: 'btn btn-outline-secondary btn-cancel',
                            text: 'CANCEL'
                        }))
                }))
        });

        e.preventDefault();

        renameLinks.addClass('disabled');
        parentLinkFile.removeClass('selectable');

        linkFile.fadeOut('fast', function () {
            $(this)
                .parent()
                .append(renameForm)
                .find('input')
                .focus()
                .val('')
                .val(fileName)
        });
    });

    $('#table_files').on('click', '.btn-ok, .btn-cancel', function () {
        const renameForm = $(this).closest('.input-group');
        const input = renameForm.find('input[type="text"]');
        const oldName = input.attr('data-name');
        const newName = input.val();
        const linkFile = renameForm.prev('.link_files');
        const parentLinkFile = linkFile.closest('.link-file');
        const type = linkFile.attr('data-type');
        const iconsRename = $('[data-action="rename"]');
        const btnClose = $(this).next('.btn-cancel');
        const infoListForUpdate = getInfoListForUpdate();

        if ($(this).hasClass('btn-cancel')) {
            renameForm.remove();
            linkFile.fadeIn('fast');
            iconsRename.removeClass('disabled');
        } else {
            if (newName === oldName) {
                btnClose.trigger('click');
            } else if ($.trim(newName) === '') {
                showThenHideMsg('Filename is empty', true);
                btnClose.trigger('click');
            } else if (!isValidName(newName)) {
                showThenHideMsg('It is recommended not to use these symbols: "! @ # $ & ~ % * ( ) [ ] { } \' \" \\ / : ; > < ` " and space in the file name', true);
                btnClose.trigger('click');
            } else {
                $.post({
                    url: relUrl + 'Utils/RenameFile.php',
                    dataType: 'json',
                    data: {
                        'oldName': oldName,
                        'newName': newName,
                        'type': type
                    },
                    success: function (data) {
                        if (data['result'] == 'error') {
                            showThenHideMsg(data['msg'], true);
                        } else {
                            $.when(tbody
                                .html(data['content']))
                                .done(function () {
                                    updateChildLists(infoListForUpdate);
                                    activateRedactor();
                                    showThenHideMsg('File successfully renamed');
                                });
                        }
                    },
                    error: errorHandler = function () {
                        showThenHideMsg('Error renaming file', true);
                    }
                });
            }
        }
        parentLinkFile.addClass('selectable');
    });


    /**
     * HANDLER: copy files
     */


    /**
     * HANDLER: delete files
     */
    $('#table_files').on('click', '[data-action="delete"]', function () {
        generateModalWindow('files_delete', 'Are you sure you want to delete?', 'Warning!');
        $('#modalQuestion').modal();
    });

    $('body').on('click', '.files_delete #btnMesCancel, .files_delete #btnMesOk', function (e) {
        const idBtn = $(this).attr('id');
        const checkedFName = getCheckedFNames();
        const infoListForUpdate = getInfoListForUpdate();

        e.preventDefault();
        $('#spanClose').trigger('click');

        if (idBtn === 'btnMesOk' && checkedFName.length > 0) {
            $.post({
                url: relUrl + 'Utils/DeleteFiles.php',
                dataType: 'json',
                data: {
                    'checkedFName': checkedFName
                },
                success: function (data) {
                    if (data['result'] === 'error') {
                        showThenHideMsg(data['msg'], true);
                    } else {
                        updateChildLists();
                        $.when(tbody
                            .html(data['content']))
                            .done(function () {
                                updateChildLists(infoListForUpdate);
                                refreshRedactor();
                                showThenHideMsg('Files successfully deleted');
                            });
                    }
                },
                error: errorHandler = function () {
                    showThenHideMsg('Error deleting files', true);
                }
            });
        }

        $('#modalQuestion').detach();
    });


    /**
     * HANDLER: scroll
     */
    $(window).scroll(function () {
        const isScrollUpExists = $('footer').is(':has(#scroll-up)');

        if ($(this).scrollTop() < 700 && isScrollUpExists) {
            $('#scroll-up').hide();
        } else {
            if (!isScrollUpExists) { generateScrollBlock(); }
            $('#scroll-up').show();
        }
    });


    /**
     * HANDLER: opening a new page
     */
    $('.main_content').on('click', function() {
        if (window.sessionStorage && window.localStorage) {
            let activeListLinkPaths = [];

            $.each($('.nav_control-link.active'), function () {
                activeListLinkPaths.push($(this).attr('data-path'));
            });

            localStorage.activeListLinkPaths = JSON.stringify(activeListLinkPaths);
        }
    });


    loadPrevPageTree();
});


/**
 * FUNCTION: opening redactor
 */
function activateRedactor() {
    setActivityControlCheckbox();
    $('#activateRedactor').hide();
    $('#actions-panel, input:checkbox, a[data-action="rename"]').removeClass('d-none');
}


/**
 * FUNCTION: update data for dynamic files
 */
function refreshRedactor() {
    setActivityControlCheckbox();
    $('#controlCheckbox').prop('checked', false);
    $('#actions-panel th a').addClass('disabled');
    $('input:checkbox, a[data-action="rename"]').removeClass('d-none');
}


/**
 * FUNCTION: close redactor
 */
function deactivateRedactor() {
    $('#actions-panel, #controlCheckbox, #table_files input:checkbox, a[data-action="rename"]').addClass('d-none');
    $('#table_files input:checkbox:checked').prop('checked', false);
    $('#activateRedactor').show();
}


/**
 * FUNCTION: definition of the "disabled" attribute control checkbox
 */
function setActivityControlCheckbox() {
    const valAttrDisabledMainCheckbox = $('#no_files').length === 1;

    $('#controlCheckbox').prop('disabled', valAttrDisabledMainCheckbox);
}

/**
 * FUNCTION: collect the names of the selected files
 */
function getCheckedFNames() {
    let pathFiles = [];

    $.each($('input:checkbox:checked').not("#controlCheckbox"), function () {
        pathFiles.push($(this).val());
    });

    return pathFiles;
}


/**
 * FUNCTION: insert html code selected directories in the left menu
 */
function generateChildList(path) {
    const linkArrow = $('a[data-path="' + path + '"]');
    const imgArrow = linkArrow.find('img[alt="show"]');
    const parentLi = linkArrow.parent();
    const ulNested = linkArrow.siblings('ul.list-group');

    if (linkArrow.hasClass('active')) {
        linkArrow.removeClass('active');
        ulNested.addClass('d-none');
        imgArrow.css({
            'animation': 'backRotate 0.01s',
            'animation-fill-mode': 'forwards'
        });
    } else {
        linkArrow.addClass('active');
        imgArrow.css({
            'animation': 'rotate 0.01s',
            'animation-fill-mode': 'forwards'
        });

        if (ulNested.length !== 0) {
            ulNested.removeClass('d-none');
        } else {
            $.post({
                url: relUrl + 'Utils/GetListDirs.php',
                dataType: 'json',
                data: {
                    'paths': [path]
                },
                success: function (data) {
                    parentLi.append(data['content'][path]);
                },
                error: errorHandler = function () {
                    showThenHideMsg('An error occurred while generating the directory tree', true);
                }
            });
        }
    }
}


/**
 * FUNCTION: returns an object with paths and information (empty dirs and open in the left menu)
 */
function getInfoListForUpdate(paths = []) {
    let listForUpdate = {};
    const urn = $(location).attr('pathname');

    paths.push(root + urn.replace(relUrl, ''));
    $.each(paths, function (i, val) {
        const linkArrow = $('#list_paths [data-path="' + val + '"]');
        const isActive = linkArrow.hasClass('active');
        const isEmpty = linkArrow.hasClass('hidden-arrow');
        listForUpdate[val] = {'isActive': isActive, 'isEmpty': isEmpty};
    });
    return listForUpdate;
}


/**
 * FUNCTION: update left menu after operations
 */
function updateChildLists(data) {
    $.each(data, function (path, pInfo) {
        $.post({
            url: relUrl + 'Utils/GetListDirs.php',
            dataType: 'json',
            data: {
                'paths': [path]
            },
            success: function (data) {
                if (data['result'] === 'success') {
                    if (path !== root) {
                        const linkArrow = $('a[data-path="' + path + '"]');
                        const linkPath = linkArrow.next('a.list_path-link');
                        const parentLi = linkArrow.closest('li.list-group-item');
                        const content = data['content'][path];
                        const isActiveLinkArrowBeforeUpd = pInfo['isActive'];
                        const isEmptyListBeforeUpd = pInfo['isEmpty'];
                        const isEmptyListAfterUpd = (content === '');

                        if (isEmptyListBeforeUpd) {
                            linkArrow.removeClass('hidden-arrow');
                            linkPath.attr('class', 'list_path-link navigation-link');
                        }

                        if (isEmptyListAfterUpd) {
                            linkArrow.removeClass('active').addClass('hidden-arrow');
                            linkPath.attr('class', 'list_path-link navigation-link empty-dir');
                        }

                        if (isActiveLinkArrowBeforeUpd) {
                            const childsUl = linkArrow.siblings('ul.list-group');

                            childsUl.detach();
                            if (!isEmptyListAfterUpd) {
                                parentLi.append(content);
                            }
                        }
                    } else {
                        let oldPaths = [];
                        let newPaths = [];
                        const oldLi = $('#list_paths > .list-group > li > a[data-path]');
                        const content = data['content'][path];
                        const newHtmlLeftNav = $('<output>').append($.parseHTML(content));
                        const newLi = newHtmlLeftNav.find('.list-group-item');

                        $.each(oldLi, function () {
                            oldPaths.push($(this).attr('data-path'));
                        });

                        $.each(newLi, function () {
                            const dataPath = $(this).find('a[data-path]').attr('data-path');

                            newPaths.push(dataPath);
                        });

                        $.each(oldPaths, function (ind, path) {
                            if ($.inArray(path, newPaths) === -1) {
                                $('[data-path="' + path + '"]').parent('li').detach();
                            }
                        });

                        $.each(newPaths, function (ind, path) {
                            if ($.inArray(path, oldPaths) === -1) {
                                const isLastElem = ind === newPaths.length - 1;
                                const nextLi = $('[data-path="' + newPaths[ind + 1] + '"]').parent('li');
                                const parentList = $('#list_paths > .list-group');
                                const newLi = newHtmlLeftNav.find('[data-path="' + path + '"]').parent('li');

                                if (isLastElem) {
                                    parentList.append(newLi);
                                } else {
                                    newLi.insertBefore(nextLi);
                                }
                            }
                        });
                    }
                }
            }
        });
    });
}

/**
 * FUNCTION: loading open directories on the previous page in the left menu
 */
function loadPrevPageTree() {
    if (window.sessionStorage && window.localStorage) {
        const activeListLinkPaths = localStorage.activeListLinkPaths ? JSON.parse(localStorage.activeListLinkPaths) : [];

        if (activeListLinkPaths.length > 0) {
            $.post({
                url: relUrl + 'Utils/GetListDirs.php',
                dataType: 'json',
                data: {
                    'paths': activeListLinkPaths
                },
                success: function (data) {
                    if (data['result'] === 'error') {
                        showThenHideMsg(data['msg'], true);
                    } else {
                        $.each(data['content'], function (path, html) {
                            const li = $('a[data-path="' + path + '"]').closest('li.list-group-item');
                            const link = li.find('a.nav_control-link');
                            const img = li.find('img[alt="show"]');

                            link.addClass('active');
                            img.css({
                                'animation': 'rotate 0.01s',
                                'animation-fill-mode': 'forwards'
                            });
                            li.append(html);
                        });
                    }
                }
            });
        }
    }
}


/**
 * FUNCTION: checks for validity of file name.
 */
function isValidName(name) {
    if ($.trim(name) === '') {
        return false;
    }
    return /^[\wa-яёА-ЯЁ\^\-\+\.\,\_]+$/.test(name);
}


/**
 * FUNCTION: output in '%' of the loaded information
 */
function changeProgressBar(e) {
    if (e.lengthComputable) {
        const progress = Math.ceil(e.loaded / e.total * 100);

        $('.progress-bar')
            .text(progress + '%')
            .css('width', progress + '%')
            .attr('aria-valuenow', progress);
    }
}


/**
 * FUNCTION: showing and hiding the modal window
 */
function showThenHideMsg(msg, error = null) {
    const showTime = error ? 2000 : 1000;

    $('.main-message')
        .html(msg)
        .attr('class', function () {
            return error ? 'main-message text-danger' : 'main-message text-success';
        })
        .show()
        .delay(showTime)
        .fadeOut(100);
}


/**
 * FUNCTION: generate in footer scroll
 */
function generateScrollBlock() {
    $('<div>', {
        id: 'scroll-up',
        css: {
            width: 35,
            height: 35,
            position: 'fixed',
            right: '1%',
            bottom: '2%',
            opacity: 0.6,
            cursor: 'pointer'
        },
        on: {
            click: function () {
                $(window).scrollTop(0);
            },
            mouseenter: function () {
                $(this).css({ 'opacity': '1',
                    '-webkit-transform': 'scale(1.3)',
                    '-ms-transform': 'scale(1.3)',
                    'transform': 'scale(1.3)'
                });
            },
            mouseleave: function () {
                $(this).css({ 'opacity': '0.6',
                    '-webkit-transform': 'scale(1.0)',
                    '-ms-transform': 'scale(1.0)',
                    'transform': 'scale(1.0)'
                });
            }
        },
        append: $('<img>', {
            src: relUrl + 'css/img/control/up-arrow.png',
            alt: 'scroll up',
            title: 'scroll up',
            width: '35px'
        })
    }).appendTo('footer');
}


/**
 * FUNCTION: generate modal window
 */
function generateModalWindow(aClass, msg, header, data = null) {
    $('<div>', {
        class: 'modal fade' + ' ' + aClass,
        id: 'modalQuestion',
        tabindex: '-1',
        role: 'dialog',
        'aria-labelledby': 'modalQuestionTitle',
        'aria-hidden': 'true',
        append: $('<div>', {
            class: 'modal-dialog modal-dialog-centered',
            role: 'document',
            append: $('<div>', {
                class: 'modal-content'
            })
        })
    }).appendTo('footer'),

        $('<div>', {
            class: 'modal-header bg-warning',
            append: $('<h5>', {
                class: 'modal-title',
                text: header
            })
                .add($('<button>', {
                    class: 'close',
                    type: 'button',
                    'data-dismiss': 'modal',
                    'aria-label': 'Close',
                    append: $('<span>', {
                        id: 'spanClose',
                        'aria-hidden': 'true',
                        html: '&times;'
                    })
                }))
        }).appendTo('#modalQuestion .modal-content'),

        $('<div>', {
            class: 'modal-body',
            append: $('<p>', {
                class: 'question-text',
                text: msg
            })
        }).appendTo('#modalQuestion .modal-content'),

        $('<div>', {
            class: 'modal-footer',
            append: $('<button>', {
                type: 'button',
                id: 'btnMesCancel',
                class: 'btn btn-secondary',
                'data-dismiss': 'modal',
                text: 'CANCEL'
            })
                .add($('<button>', {
                    type: 'button',
                    id: 'btnMesOk',
                    class: 'btn btn-primary',
                    text: 'OK'
                }))
        }).appendTo('#modalQuestion .modal-content');
}
