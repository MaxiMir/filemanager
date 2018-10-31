$(function () {
	var $msg = $('.main-message');

	$('[data-toggle="tooltip"]').tooltip();

    // handler: create files
	$('.create_files input[type=text]').on('blur', function () {
		var
			$inputVal = $(this).val(),
			$errorField = $(this).next();

		if ($inputVal) {
			if (isValidName($inputVal)) {
				$errorField.text('');
			} else {
				$errorField.text('It is recommended not to use these symbols: "! @ # $ & ~ % * ( ) [ ] { } \' \" \\ : ; > < ` " and space in the file name');
			}
		}
	});

	$('.create_files').on('submit', function (e) {
		e.preventDefault();

		var
			$currForm = $("#" + $(this).attr('id')),
			$errorField = $currForm.find(".error_msg"),
			$input = $currForm.find("input[type=text]"),
			$inputVal = $input.val(),
			$btnSubmit = $currForm.find("button[type='submit']"),
			$btnClose = $currForm.find("button[data-dismiss='modal']"),
			$type = $input.attr('name') == 'fileName' ? 'file' : 'folder',
			$isValidForm = false;

		$errorField.empty();

		if ($.trim($inputVal) === '') {
			$errorField.text('Not valid filename!');
		} else {
			$errorField.text('');
			$isValidForm = true;
		}

		if ($isValidForm) {
			$.post({
				url: 'Utils/CreateFiles.php',
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
						$msg
							.text('The file "' + $inputVal + '" was created successfully')
							.show()
							.delay(1500)
							.hide(300);
						if (data['html']) {
					        $('#table_files .file_field').get(data['filePos']).after(data['html']);
						}
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
				url: 'Utils/UploadsFiles.php',
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
						$msg
							.html(data['msg'])
							.show()
							.delay(1500)
							.hide(300);
						if (data['htmlFiles']) {
							$('#table_files').prepend(data['htmlFiles']);
						}
					}
				},
				error: errorHandler = function () {
					$errorField.text('Error loading files');
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

	$('#table_files').on('click', '[data-action="rename"]', function (e) {
		e.preventDefault();

		var $filePath = $(this).attr('data-url'),
			$fileName = $(this).attr('data-name'),
			$linkFile = $('.link_files[data-name="' + $fileName + '"]'),
			$renameHtml = $('<div class="input-group"></div>')
			    .append('<input type="text" class="form-control" placeholder="Enter name" value="" autofocus data-name="' + $fileName + '">')
			    .append($('<div class="input-group-append"></div>')
				.append('<button class="btn btn-outline-secondary btn-ok" type="button">ok</button>')
				.append('<button class="btn btn-outline-secondary btn-cancel" type="button">cancel</button>'));

		$(this).addClass('disabled');

		$linkFile.fadeOut('fast')
                    			.parent()
                    			.append($renameHtml)
                    			.find('input')
                    			.focus()
                    			.val('')
                    			.val($fileName);
	});

	$('#table_files').on('click', '.btn-ok, .btn-cancel', function () {
		var $renameHtml = $(this).closest('.input-group'),
			$input = $renameHtml.find('input[type="text"]'),
			$oldName = $input.attr('data-name'),
			$newName = $input.val(),
			$linkFile = $renameHtml.prev('.link_files'),
			$trFile = $renameHtml.parent('tr');
			$type = $linkFile.attr('data-type'),
			$iconRename = $('[data-type="rename"], [data-name="' + $oldName + '"]');
		if ($(this).hasClass('btn-ok')) {
			if ($.trim($newName) === '') {
				$msg
					.text('Not valid filename!')
					.show()
					.delay(1500)
					.hide(300);
			} else if (!isValidName($newName)) {
				$msg
					.text('It is recommended not to use these symbols: "! @ # $ & ~ % * ( ) [ ] { } \' \" \\ : ; > < ` " and space in the file name')
					.show()
					.delay(1500)
					.hide(300);
			} else if ($newName === $oldName) {
				$msg
					.text('File names are not individual')
					.show()
					.delay(1500)
					.hide(300);
			} else {
				$.post({
					url: 'Utils/RenameFile.php',
					dataType: 'json',
					data: {
						'oldName': $oldName,
						'newName': $newName,
						'type': $type
					},
					success: function (data) {
						if (data['result'] == 'error') {
						    $msg.text(data['msg']);
						} else {
						    $trFile.html(data['htmlFile']);
                            $msg.text('File successfully renamed');
						}
					},
					error: errorHandler = function () {
						$msg.text('Error renaming file');
					}
				});
			}
		}

		$renameHtml.remove();
		$linkFile.fadeIn('fast');
		$iconRename.removeClass('disabled');
	});
});


function isValidName(name) {
	return /^[\wa-яёА-ЯЁ\^\-\+\.\,\_]/.test(name);
}

function changeProgressBar(e) {
	if (e.lengthComputable) {
		$progress = Math.ceil(e.loaded / e.total * 100);
		$('.progress-bar')
			.text($progress + '%')
			.css('width', $progress + '%')
			.attr('aria-valuenow', $progress);
	}
}
