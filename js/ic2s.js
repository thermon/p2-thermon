/*
 * ImageCache2::Uploader
 */

// {{{ GLOBALS

var fileUploadFormControllers = new Array;

// }}}
// {{{ fileUploadFormController()

/*
 * �t�@�C���A�b�v���[�h�p�t�H�[���v�f�̐��𑝌�����
 * �J�X�^���I�u�W�F�N�g�̃R���X�g���N�^
 */
function fileUploadFormController(containerId, submitId, fileElemName, pathElemName, formLabel)
{
	var controllerId = fileUploadFormControllers.length;

	var elementContainer = document.getElementById(containerId);
	if (!elementContainer) {
		window.alert('�R���e�i�v�f (id="' + containerId + '")������܂���B');
		return;
	}

	var submitButton = document.getElementById(submitId);
	if (!submitButton) {
		window.alert('���M�{�^�� (id="' + submitId + '")������܂���B');
		return;
	}

	var fileElemName = fileElemName + '[]';
	var pathElemName = pathElemName + '[]';
	var formLabel = formLabel;

	var itemContainer = new Array;
	var formContainer = new Array;

	// {{{ addFile()

	this.addFile = function()
	{
		var i, dv, el, fe, pe, se;

		i = itemContainer.length + 1;
		dv = document.createElement('div');

		//se = document.createElement('span');
		//se.innerHTML = formLabel + i + ':&nbsp;';
		//dv.appendChild(se);

		fe = document.createElement('input');
		fe.setAttribute('type', 'file');
		fe.setAttribute('name', fileElemName);
		fe.setAttribute('size', 45);
		dv.appendChild(fe);

		pe = document.createElement('input');
		pe.setAttribute('type', 'hidden');
		pe.setAttribute('name', pathElemName);
		dv.appendChild(pe);

		elementContainer.appendChild(dv);
		itemContainer.push(dv);

		el = new fileUploadFormElement(fe, pe);
		formContainer.push(el);
	}

	// }}}
	// {{{ removeFile()

	this.removeFile = function()
	{
		var dv, el;
		if (itemContainer.length > 1) {
			el = formContainer.pop();
			dv = itemContainer.pop();
			elementContainer.removeChild(dv);
		}
	}

	// }}}
	// {{{ resetFile()

	this.resetFile = function()
	{
		var dv, el;
		while (itemContainer.length) {
			el = formContainer.pop();
			dv = itemContainer.pop();
			elementContainer.removeChild(dv);
		}
		this.addFile();
		this.enableSubmit();
	}

	// }}}
	// {{{ enableSubmit()

	this.enableSubmit = function()
	{
		submitButton.disabled = false;
	}

	// }}}
	// {{{ disableSubmit()

	this.disableSubmit = function()
	{
		submitButton.disabled = true;
	}

	// }}}
	// {{{ preSubmit()

	this.preSubmit = function()
	{
		this.disableSubmit();

		var i, err, localURI, tmpPath;
		var dosPath = /^[A-Z]:\\/i;

		try {
			for (i = 0; i < formContainer.length; i++) {
				tmpPath = formContainer[i].fileElem.value;
				if (dosPath.test(tmpPath)) {
					localURI = 'file:///' + tmpPath.replace(/\\/g, '/');
				} else if (tmpPath.charAt(0) == '/') {
					localURI = 'file://' + tmpPath;
				} else {
					localURI = 'file:///' + tmpPath;
				}
				formContainer[i].pathElem.value = localURI;
			}
		} catch (err) {
			window.alert('preSubmit() Error: ' + err.toString());
			this.enableSubmit();
			return false;
		}

		var funcName = 'fileUploadFormControllers['  + controllerId + '].enableSubmit()';
		setTimeout(funcName, 5000);
		return true;
	}

	// }}}

	// �C���X�^���X�쐬���Ƀt�H�[������ǉ�����
	this.addFile();

	// setTimeout�ŎQ�Ƃ��邽�߂̃O���[�o���ϐ��Ɏ��g��o�^����
	fileUploadFormControllers.push(this);
}

// }}}
// {{{ fileUploadFormElement()

/*
 * �t�@�C���A�b�v���[�h�p�t�H�[���̗v�f�ɃA�N�Z�X���₷���悤�ɂ���
 * �J�X�^���I�u�W�F�N�g�̃R���X�g���N�^
 */
function fileUploadFormElement(fileElem, pathElem)
{
	this.fileElem = fileElem;
	this.pathElem = pathElem;
}

// }}}

/*
 * Local Variables:
 * mode: javascript
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: t
 * End:
 */
/* vim: set syn=javascript fenc=cp932 ai noet ts=4 sw=4 sts=4 fdm=marker: */
