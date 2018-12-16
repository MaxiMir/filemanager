$(function () {
		msg = $('.main-message'),
    	tbody = $('#table_files tbody'),
		relUrl = $('main').attr('data-relurl');


    /**
     * HANDLER: change size block
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
        	var
				size = $('.main_content').width() - ui.size.width - 30,
                indent = Math.abs(ui.size.width - ui.originalSize.width);

            $('#table_files').css({
				'left': indent + 'px',
				'width': size + 'px'
			});
		},
        stop: function ( event, ui ) {
            $('#table_files').resizable({disabled: true});
    	}
    });


    /**
     * HANDLER: scroll
     */
    $(window).scroll(function () {
        var
            isScrollUpExists = $('footer').is(':has(#scroll-up)');

        if ($(this).scrollTop() < 700 && isScrollUpExists) {
            $('#scroll-up').hide();
        } else {
            if (!isScrollUpExists) { generateScrollBlock(); }
            $('#scroll-up').show();
        }
    });


	/**
     * HANDLER: checkbox in table
     */
    $('#checkboxControl').on('click', 'a[data-action="activate-checkbox"]', function () {
    	$(this).hide();
        $('#table_files input:checkbox, #allCheckboxes').removeClass('d-none');
        $('#actions-panel').removeClass('d-none');
	});

    $('#table_files').on('click', '#allCheckboxes', function () {
        if ($(this).is(':checked')) {
            $('#table_files input:checkbox').prop('checked', true);
        } else {
            $('#table_files input:checkbox').prop('checked', false);
        }
    });

    $('#table_files').on('click', 'input:checkbox', function () {
        var
            count = $(':checkbox:checked').length,
            actionsLinks = $('#actions-panel td a');

        actionsLinks.each(function (i, elem) {
            if (count === 0) {
                $(this).addClass('disabled');
            } else {
                $(this).removeClass('disabled');
            }
        });
    });

    $('#table_files').on('click', '.file_field td:not(:first-of-type)', function (e) {
    	if (!$('#actions-panel').hasClass('d-none')) {
    		var
				input = $(this)
					.closest('tr')
					.find('input'),
    			checked = input.prop('checked');

    		e.preventDefault();
            input.prop('checked', !checked);
		}
    });


    /**
     * HANDLER: close operations panel
     */
	$('#table_files').on('click', '#closePanel', function () {
        hideActionsPanel();
    });

	
	/**
	 * HANDLER: modals reset
	 */
	$('a[data-toggle="modal"]').on('click', function () {
		var
			idModal = $(this).attr('data-target'),
			form = $('#' + $(idModal).find('form').attr('id')),
            error_msg = form.find('.error_msg');

        form[0].reset();
        error_msg.empty();
	});


    /**
     * HANDLER: click outside the dynamic modal window
     */
    $(document).on('click', function (e) {
        var
            modal = $("#modalQuestion");

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
		var
			inputVal = $(this).val(),
			errorField = $(this).next(),
			btnSbm = $(this)
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
		var
			currForm = $('#' + $(this).attr('id')),
			errorField = currForm.find(".error_msg"),
			input = currForm.find("input[type=text]"),
			inputVal = input.val(),
            btnSbm = currForm.find("button[type='submit']"),
			btnCls = currForm.find("button[data-dismiss='modal']"),
			type = input.attr('data-type');

		e.preventDefault();

		$.post({
			url: relUrl + 'Utils/CreateFiles.php',
			dataType: 'json',
			data: {
				'name': inputVal,
				'type': type
			},
			beforeSend: function (data) {
                btnSbm.attr('disabled', 'disabled');
                hideActionsPanel();
			},
			success: function (data) {
				if (data['result'] === 'error') {
					errorField.html(data['msg']);
				} else {
					btnCls.trigger('click');
					$.when(tbody
						.html(data['content']))
						.done(function () {
							showThenHideMsg('The file "' + inputVal + '" was created successfully');
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
		var
			form = $(this),
			modalBody = form.find('.modal-body'),
			progressBar = form.find('.progress'),
			errorField = form.find(".error_msg"),
            btnCls = form.find("button[data-dismiss='modal']"),
			btnSbm = form.find("button[type='submit']"),
			data = new FormData(),
			files = $('#newFiles').get(0).files;

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
                    let
                        myXhr = $.ajaxSettings.xhr();

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
                    modalBody.fadeOut('fast', function () {
                        btnSbm.attr('disabled', 'disabled');
                        progressBar.toggleClass('d-none d-block');
                    });
					hideActionsPanel();
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
	});


	/**
	 * HANDLER: rename files
	 */
	$('#table_files').on('click', '[data-action="rename"]', function (e) {
		var
			filePath = $(this).attr('data-path'),
			fileName = $(this).attr('data-name'),
			linkFile = $('.link_files[data-name="' + fileName + '"]'),
			renameForm = $('<div>', {
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
		$('[data-action="rename"]').addClass('disabled');

		linkFile.fadeOut('fast', function () {
			$(this).parent()
			.append(renameForm)
			.find('input')
			.focus()
			.val('')
			.val(fileName)			
		});
	});

	$('#table_files').on('click', '.btn-ok, .btn-cancel', function () {
		var
			renameForm = $(this).closest('.input-group'),
			input = renameForm.find('input[type="text"]'),
			oldName = input.attr('data-name'),
			newName = input.val(),
			linkFile = renameForm.prev('.link_files'),
			type = linkFile.attr('data-type'),
			iconsRename = $('[data-action="rename"]'),
			btnClose = $(this).next('.btn-cancel');

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
                    beforeSend: function (data) {
                        hideActionsPanel();
                    },
					success: function (data) {
						if (data['result'] == 'error') {
							showThenHideMsg(data['msg'], true);
						} else {
							$.when(tbody
								.html(data['content']))
								.done(function () {
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
        var
            idBtn = $(this).attr('id'),
            checkedFName = getCheckedFNames();

        e.preventDefault();
        $('#spanClose').trigger('click');

        if (idBtn === 'btnMesOk' && checkedFName.length > 0) {
            $.post({
                url: relUrl + 'Utils/DeleteFiles.php',
                dataType: 'json',
                data: {
                    'checkedFName': checkedFName
                },
				beforeSend: function (data) {
                    hideActionsPanel();
				},
                success: function (data) {
                    if (data['result'] === 'error') {
                        showThenHideMsg(data['msg'], true);
                    } else {
                        $.when(tbody
                            .html(data['content']))
                            .done(function () {
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
});


function isValidName(name) {
	if ($.trim(name) === '') {
		return false;
	}
	return /^[\wa-яёА-ЯЁ\^\-\+\.\,\_]+$/.test(name);
}

function changeProgressBar(e) {
	if (e.lengthComputable) {
		var
			progress = Math.ceil(e.loaded / e.total * 100);

		$('.progress-bar')
			.text(progress + '%')
			.css('width', progress + '%')
			.attr('aria-valuenow', progress);
	}
}

function showThenHideMsg(msg, error = null) {
	$('.main-message').attr('class', function () {
		return error ? 'main-message text-danger' : 'main-message text-success';
	});

	$('.main-message')
		.html(msg)
		.show()
		.delay(1500)
		.hide(500);
}

function generateScrollBlock() {
	$('<div>', {
			id: 'scroll-up',
            css: {
                width: 35,
                height: 35,
				position: 'fixed',
                right: '3%',
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
	var
		modalQuestion = $('<div>', {
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

		modalQuestionHeader = $('<div>', {
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

		modalQuestionBody = $('<div>', {
			class: 'modal-body',
			append: $('<p>', {
				class: 'question-text',
				text: msg
			})
		}).appendTo('#modalQuestion .modal-content'),

		modalQuestionFooter = $('<div>', {
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
    var
        pathFiles = [];

    $('input:checkbox:checked').not("#allCheckboxes").each(function () {
        pathFiles.push($(this).val());
    });

    return pathFiles;
}

function hideActionsPanel() {
	if (!$('#actions-panel').hasClass('d-none')) {
        $('#actions-panel, #allCheckboxes, #table_files input:checkbox').addClass('d-none');
        $('a[data-action="activate-checkbox"]').show();
	}
}