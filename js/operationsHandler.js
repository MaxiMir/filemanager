$(function () {
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
        const type = input.attr('data-type');

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
                            updateChildLists();
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
                                updateChildLists();
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
							updateChildLists();
							$.when(tbody
								.html(data['content']))
								.done(function () {
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


    /**
     * FUNCTION: opening tree folders from previous page
     */
    loadPrevPageTree();
});

function activateRedactor() {
    setActivitycontrolCheckbox();
    $('#activateRedactor').hide();
    $('#actions-panel, input:checkbox, a[data-action="rename"]').removeClass('d-none');
}

function refreshRedactor() {
    setActivitycontrolCheckbox();
    $('#controlCheckbox').prop('checked', false);
    $('#actions-panel th a').addClass('disabled');
    $('input:checkbox, a[data-action="rename"]').removeClass('d-none');
}

function deactivateRedactor() {
	$('#actions-panel, #controlCheckbox, #table_files input:checkbox, a[data-action="rename"]').addClass('d-none');
    $('#table_files input:checkbox:checked').prop('checked', false);
	$('#activateRedactor').show();
}

function setActivitycontrolCheckbox() {
    const valAttrDisabledMainCheckbox = $('#no_files').length === 1;

    $('#controlCheckbox').prop('disabled', valAttrDisabledMainCheckbox);
}

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
                },
                error: errorHandler = function () {
                    showThenHideMsg('An error occurred while generating the directory tree', true);
                }
            });
        }
    }
}

function generateChildList(path) {
	const li = $('a[data-path="' + path + '"]').closest('li.list-group-item');
    const link = li.find('a.nav_control-link');
    const img = li.find('img[alt="show"]');
    const ulNested = li.find('ul.list-group');

    if (link.hasClass('active')) {
        link.removeClass('active');
        ulNested.hide('fast', function () {
            img.css({
                'animation': 'backRotate 0.01s',
                'animation-fill-mode': 'forwards'
            });
        });
    } else {
        link.addClass('active');
        img.css({
            'animation': 'rotate 0.01s',
            'animation-fill-mode': 'forwards'
        });

        if (ulNested.length !== 0) {
            ulNested.show('fast');
        } else {
            $.post({
                url: relUrl + 'Utils/GetListDirs.php',
                dataType: 'json',
                data: {
                    'paths': [path]
                },
                success: function (data) {
                    if (data['result'] === 'error') {
                        showThenHideMsg(data['msg'], true);
                    } else {
                        if (ulNested.length !== 0) {
                            ulNested.closest('.list-group').remove();
                        }
                        li.append(data['content'][path]);
                    }
                },
                error: errorHandler = function () {
                    showThenHideMsg('An error occurred while generating the directory tree', true);
                }
            });
        }
    }
}

function isValidName(name) {
	if ($.trim(name) === '') {
		return false;
	}
	return /^[\wa-яёА-ЯЁ\^\-\+\.\,\_]+$/.test(name);
}

function changeProgressBar(e) {
	if (e.lengthComputable) {
		const progress = Math.ceil(e.loaded / e.total * 100);

		$('.progress-bar')
			.text(progress + '%')
			.css('width', progress + '%')
			.attr('aria-valuenow', progress);
	}
}

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

function getCheckedFNames() {
    let pathFiles = [];

    $.each($('input:checkbox:checked').not("#controlCheckbox"), function () {
        pathFiles.push($(this).val());
    });

    return pathFiles;
}


function getListsForUpdate(paths = []) {
    let listForUpdate = [];
    const root = $('#root').text();
    const urn = $(location).attr('pathname');
    paths.push = root + urn.replace(relUrl, '');
    $.each(paths, function (i, val) {
        listForUpdate[val] = $('#list_paths [data-path="' + val + '"]').hasClass('active');
    });
    return listForUpdate;
}

function updateChildLists(data) {
    // $.each(data, function () {
    // if (!hasChildDirs && isIssetControlArrow) {
    //     controlArrow.detach();
    //     if (isActiveControlArrow) {
    //         parentUl.find('ul.list-group').detach();
    //     }
    // } else {
    //     if (!isIssetControlArrow) {
    //         $('<a>', {
    //             href: 'javascript:void(0);',
    //             class: 'nav_control-link',
    //             'data-path': currPath,
    //             append: $('<img>', {
    //                 src: '/fm/css/img/control/down-arrow.png',
    //                 width: '10px',
    //                 alt: 'show'
    //             })
    //         }).prependTo(parentUl);
    //     } else if (isActiveControlArrow) {
    //         parentUl.find('ul.list-group').detach();
    //
    //     }
    // }
}
