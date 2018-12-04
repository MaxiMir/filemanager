$(function () {
	var
		$msg = $('.main-message'),
		$tbody = $('#table_files tbody');
		$relUrl = $('main').attr('data-relurl');

	$('[data-toggle="tooltip"]').tooltip();

	/**
	 * handler: create files
	 */
	$('.create_files input[type=text]').on('blur', function () {
		var
			$inputVal = $(this).val(),
			$errorField = $(this).next();

		if ($inputVal) {
			if (isValidName($inputVal)) {
				$errorField.text('');
			} else {
				$errorField.text('It is recommended not to use these symbols: "! @ # $ & ~ % * ( ) [ ] { } \' \" \\ / : ; > < ` " and space in the file name');
			}
		}
	});

	$('.create_files').on('submit', function (e) {
		var
			$currForm = $("#" + $(this).attr('id')),
			$errorField = $currForm.find(".error_msg"),
			$input = $currForm.find("input[type=text]"),
			$inputVal = $input.val(),
			$btnSubmit = $currForm.find("button[type='submit']"),
			$btnClose = $currForm.find("button[data-dismiss='modal']"),
			$type = $input.attr('data-type'),
			$isValidForm = false;

        e.preventDefault();
		$errorField.empty();

		if ($.trim($inputVal) === '') {
			$errorField.text('Filename is empty');
		} else {
			$errorField.text('');
			$isValidForm = true;
		}

		if ($isValidForm) {
			$.post({
				url: $relUrl + 'Utils/CreateFiles.php',
				dataType: 'json',
				data: {
					'name': $inputVal,
					'type': $type
				},
				beforeSend: function (data) {
					$btnSubmit.attr('disabled', 'disabled');
				},
				success: function (data) {
					if (data['result'] == 'error') {
						$errorField.html(data['msg']);
					} else {
						$btnClose.trigger('click');
						$.when($tbody
								.html(data['content']))
							.done(function () {
								showThenHideMsg('The file "' + $inputVal + '" was created successfully');
							});
					}
				},
				error: function (xhr, ajaxOptions, thrownError) {
					$errorField.html("Status: " + xhr.status + "<br>" + thrownError);
				},
				complete: function (data) {
					$btnSubmit.prop('disabled', false);
				}
			});
		}
	});

	/**
	 * handler: uploads files
	 */
	$('#formUploadsFiles').on('submit', function (e) {
		var
			$form = $(this),
			$modalBody = $form.find('.modal-body'),
			$progressBar = $form.find('.progress'),
			$errorField = $form.find(".error_msg"),
			$btnClose = $form.find("button[data-dismiss='modal']"),
			$btnSubmit = $form.find("button[type='submit']"),
			$data = new FormData(),
			$files = $('#newFiles').get(0).files,
			$error = '';

        e.preventDefault();

		if ($files.length === 0) {
			$error = 'Please, select files';
		}

		$.each($('#newFiles')[0].files, function (i, file) {
			if (file.name.length < 1) {
				$error = 'The file has an incorrect size!';
			} else {
				$data.append('file-' + i, file);
			}
		});

		if ($error !== '') {
			$errorField.text($error);
		} else {
			$errorField.empty();
			$.post({
				url: $relUrl + 'Utils/UploadsFiles.php',
				xhr: function () {
					var $myXhr = $.ajaxSettings.xhr();
					if ($myXhr.upload) {
						$myXhr.upload.addEventListener('progress', changeProgressBar, false);
					}
					return $myXhr;
				},
				data: $data,
				cache: false,
				contentType: false, // дефолтные установки jQuery равны application/x-www-form-urlencoded, что не предусматривает отправку файлов
				processData: false, // jQuery будет конвертировать массив files в строку => сервер не сможет получить данные.
				beforeSend: function () {
					$modalBody.fadeOut('fast', function () {
						$btnSubmit.attr('disabled', 'disabled');
						$progressBar.toggleClass('d-none d-block');
					});
				},
				success: function (data) {
					if (data['result'] == 'error') {
						$errorField.html(data['msg']);
					} else {
						$btnClose.trigger('click');
						$.when($tbody
								.html(data['content']))
							.done(function () {
								showThenHideMsg('Files successfully uploaded');
							});
					}
				},
				error: errorHandler = function () {
					$errorField.text('Error loading files...');
				},
				complete: function (data) {
					setTimeout(function () {
						$('#formUploadsFiles button[type=submit]').prop('disabled', false);
					}, 500);
					$btnSubmit.prop('disabled', false);
					$progressBar.toggleClass('d-none d-block');
					$modalBody.fadeIn();
					$form.trigger('reset');
				}
			});
		}
	});

	/**
	 * handler: rename files
	 */
	$('#table_files').on('click', '[data-action="rename"]', function (e) {
		e.preventDefault();

		var
			$filePath = $(this).attr('data-path'),
			$fileName = $(this).attr('data-name'),
			$linkFile = $('.link_files[data-name="' + $fileName + '"]'),

			$renameForm = $('<div>', {
				class: 'input-group',
				append: $('<input>', {
						class: 'form-control',
						type: 'text',
						placeholder: 'Enter name',
						value: '',
						autofocus: 'autofocus',
						'data-name': $fileName
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

		$('[data-action="rename"]').addClass('disabled');

		$linkFile.fadeOut('fast')
			.parent()
			.append($renameForm)
			.find('input')
			.focus()
			.val('')
			.val($fileName);
	});

	$('#table_files').on('click', '.btn-ok, .btn-cancel', function () {
		var
			$renameForm = $(this).closest('.input-group'),
			$input = $renameForm.find('input[type="text"]'),
			$oldName = $input.attr('data-name'),
			$newName = $input.val(),
			$linkFile = $renameForm.prev('.link_files'),
			$type = $linkFile.attr('data-type'),
			$iconsRename = $('[data-action="rename"]'),
			$btnClose = $(this).next('.btn-cancel');

		if ($(this).hasClass('btn-cancel')) {
			$renameForm.remove();
			$linkFile.fadeIn('fast');
			$iconsRename.removeClass('disabled');
		} else {
			if ($newName === $oldName) {
				$btnClose.trigger('click');
			} else if ($.trim($newName) === '') {
				showThenHideMsg('Filename is empty', true);
				$btnClose.trigger('click');
			} else if (!isValidName($newName)) {
				showThenHideMsg('It is recommended not to use these symbols: "! @ # $ & ~ % * ( ) [ ] { } \' \" \\ / : ; > < ` " and space in the file name', true);
				$btnClose.trigger('click');
			} else {
				$.post({
					url: $relUrl + 'Utils/RenameFile.php',
					dataType: 'json',
					data: {
						'oldName': $oldName,
						'newName': $newName,
						'type': $type
					},
					success: function (data) {
						if (data['result'] == 'error') {
							showThenHideMsg(data['msg'], true);
						} else {
							$.when($tbody
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
	 * handler: copy files
	 */






	/**
	 * handler: delete files
	 */
	$('#table_files').on('click', '[data-action="delete"]', function () {
		var
			$pathFile = $(this).attr('data-path');

		generateModalWindow('Are you sure you want to delete the file?', 'Warning!', $pathFile);
        $('#modalQuestion').modal();
	});

	$(document).on('click', function (e) {
		var modal = $("#modalQuestion");
		if ($('footer').is(':has(#modalQuestion)')) {
			if (!modal.is(e.target) && modal.has(e.target).length === 0) {
				$('#modalQuestion').detach();
			}
		}
	});

	$('body').on('click', '#btnMesCancel, #btnMesOk', function (e) {
        var
            $pathFile = $('#formMessageVal').attr('value'),
            $idBtn = $(this).attr('id');

        e.preventDefault();
        $('#spanClose').trigger('click');

        if ($idBtn == 'btnMesOk') {
            $.post({
                url: $relUrl + 'Utils/DeleteFiles.php',
                dataType: 'json',
                data: {
                    'pathFile': $pathFile
                },
                success: function (data) {
                    if (data['result'] == 'error') {
                        showThenHideMsg(data['msg'], true);
                    } else {
                        $.when($tbody
                            .html(data['content']))
                            .done(function () {
                                $.fx.off = true;
                                showThenHideMsg('File successfully deleted');
                            });
                    }
                },
                error: errorHandler = function () {
                    showThenHideMsg('Error deleting file', true);
                }
            });
        }

        $('#modalQuestion').detach();
	});

	/**
	 * handler: scroll
	 */
    $(window).scroll(function() {
    	var
			$isScrollUpExists = $('footer').is(':has(#scroll-up)');

        if ($(this).scrollTop() < 700 && $isScrollUpExists) {
        	$('#scroll-up').hide();
		} else {
        	if (!$isScrollUpExists) { generateScrollBlock(); }
            $('#scroll-up').show();
        }
    });
});

function isValidName(name) {
	return /^[\wa-яёА-ЯЁ\^\-\+\.\,\_]+$/.test(name);
}

function changeProgressBar(e) {
	if (e.lengthComputable) {
		var progress = Math.ceil(e.loaded / e.total * 100);
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
	var
		$scroll = $('<div>', {
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
                click: function() {
                    $(window).scrollTop(0);
                },
                mouseenter: function() {
                    $(this).css({ 'opacity': '1',
								  '-webkit-transform': 'scale(1.3)',
								  '-ms-transform': 'scale(1.3)',
								  'transform': 'scale(1.3)'
					});
				},
                mouseleave: function() {
                    $(this).css({ 'opacity': '0.6',
								  '-webkit-transform': 'scale(1.0)',
								  '-ms-transform': 'scale(1.0)',
								  'transform': 'scale(1.0)'
                    });
				}
			},
			append: $('<img>', {
				src: $relUrl + 'css/img/control/up-arrow.png',
				alt: 'scroll up',
				title: 'scroll up',
				width: '35px'
            })
		}).appendTo('footer');
}

function generateModalWindow(msg, header, data = null) {
	var
		$modalQuestion = $('<div>', {
			class: 'modal fade',
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

		$modalQuestionHeader = $('<div>', {
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

		$modalQuestionBody = $('<div>', {
			class: 'modal-body',
			append: $('<p>', {
				class: 'question-text',
				text: msg
			})
			.add($('<input>', {
				type: 'hidden',
				id: 'formMessageVal',
				value: data
			}))
		}).appendTo('#modalQuestion .modal-content'),

		$modalQuestionFooter = $('<div>', {
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


	// var $blockAnim = $('<div>', {
	// 	class: 'bouncing-loader',
	// 	append: $('<div>')
	// 				.add($('<div>'))
	// 				.add($('<div>'))
	// 			index.php	.add($('<div>'))
	// });