$(function () {
	$('[data-toggle="tooltip"]').tooltip();

	const editor = ace.edit('editor');
    const mode = setEditorMode();
    const relUrl = $('main').attr('data-relurl');
    let source = editor.getValue();
    
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
        const code = editor.getValue();
        const msg = $('.main-message');

		$.post({
			url: relUrl + 'Utils/ChangeContent.php',
			data: {
				'code': code
			},
			beforeSend: function () {
				$('#btnSave, #btnRemove').attr('disabled', 'disabled');
			},
			success: function (data) {
                const text = data['result'] === 'success' ? 'File has been successfully modified' : data['msg'];
				if (data['result'] === 'success') {
					source = code;
				}

                showThenHideMsg(text);
			},
			error: errorHandler = function () {
                showThenHideMsg('Error loading files', true);
			}
		});
	});

	$('.lamp').on('click', function () {
		if ($(this).is(':checked')) {
			editor.setTheme('ace/theme/ambiance');
		} else {
			editor.setTheme('ace/theme/eclipse');
		}
	});
});

function setEditorMode() {

	const ext = $('#editor').attr('data-ext');
    const modes = {
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

	return ext in modes ? modes[ext] : 'text';
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