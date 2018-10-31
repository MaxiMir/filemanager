$(function () {
	$('[data-toggle="tooltip"]').tooltip();

	var editor = ace.edit('editor'),
		mode = setEditorMode();
	source = editor.getValue();

	editor.session.setMode('ace/mode/' + mode);
	editor.setOptions({
		minLines: 15,
		autoScrollEditorIntoView: true,
		copyWithEmptySelection: true,
		wrap: true
	});

	$('#editor').on('change input paste cut keyup keydown', function (e) {
		if (source === editor.getValue()) {
			$('#btnSave, #btnRemove').attr('disabled', 'disabled');
		} else {
			if (e.which == 83 && e.ctrlKey) {
				e.preventDefault();
				$('#btnSave').trigger('click');
			}
			$('#btnSave, #btnRemove').prop('disabled', false);
		}
	});

	$('#btnRemove').on('click', function () {
		editor.setValue(source);
	});

	$('#btnSave').on('click', function () {
		var code = editor.getValue(),
			$msg = $('.main-message');

		$.post({
			url: 'Utils/ChangeContent.php',
			data: {
				'code': code
			},
			beforeSend: function () {
				$('#btnSave, #btnRemove').attr('disabled', 'disabled');
			},
			success: function (data) {
				if (data['result'] == 'error') {
					$text = data['msg'];
				} else {
					source = code;
					$text = 'File has been successfully modified';
				}

				$msg
					.html($text)
					.show()
					.delay(1500)
					.hide(300);
			},
			error: errorHandler = function () {
				$msg.text('Error loading files');
			}
		});
	});

	$('.lamp').on('click', function () {
		if ($(this).is(':checked')) {
			editor.setTheme('ace/theme/dracula');
		} else {
			editor.setTheme('ace/theme/eclipse');
		}
	});
});

function setEditorMode() {

	var $ext = $('#editor').attr('data-ext'),
		$modes = {
			'php': 'php',
			'js': 'javascript',
			'py': 'python',
			'rb': 'ruby',
			'json': 'json',
			'sql': 'sql',
			'html': 'html',
			'twig': 'twig',
			'xml': 'xml',
			'yaml': 'yaml',
			'css': 'css',
			'sass': 'sass',
			'scss': 'scss',
			'less': 'less',
			'svg': 'svg',
			'slim': 'slim'
		};

	return $ext in $modes ? $modes[$ext] : 'text';
}
