/**
 * rep2expack - ���[�U�ݒ�Ǘ���UI���^�u������
 */

// {{{ _edit_conf_user_js_onload()

var _edit_conf_user_js_onload = function() {
	// �E�C���h�E�̃^�C�g����ݒ�
	setWinTitle();

	// �^�u�p�v�f�����֐�
	var getTab = function() {
		var aTab = document.createElement('span');
		aTab.style.marginLeft = '5px';
		aTab.style.paddingBottom = '1px';
		aTab.style.verticalAlign = 'bottom';
		return aTab;
	}

	// �{�^���v�f�����֐�
	var getBtn = function(btn_type, btn_name, btn_value) {
		var aBtn = document.createElement('input');
		aBtn.type = btn_type;
		aBtn.name = btn_name;
		aBtn.value = btn_value;
		aBtn.style.fontSize = '80%';
		return aBtn;
	}

	// �P�ڂ� 'tabbernav' �ɑ��M�E���Z�b�g�p�̃^�u��ǉ�����
	var tabs = document.getElementsByTagName('ul');
	for (var i = 0; i < tabs.length; i++) {
		if (tabs[i].className != 'tabbernav') {
			continue;
		}
		var targetForm = document.getElementById('edit_conf_user_form');

		// �u�ύX��ۑ�����v�^�u
		var saveTab = getTab();
		var saveBtn = getBtn('submit', 'submit_save', '�ύX��ۑ�����');
		/*saveBtn.onclick = function() {
			var msg = '�ύX��ۑ����Ă���낵���ł����H';
			return window.confirm(msg);
		}*/
		saveTab.appendChild(saveBtn);

		// �u�ύX���������v�^�u
		var resetTab = getTab();
		var resetBtn = getBtn('reset', 'reset_change', '�ύX��������');
		resetBtn.onclick = function() {
			var msg = '�ύX���������Ă���낵���ł����H' + '\n';
				msg += '�i�S�Ẵ^�u�̕ύX�����Z�b�g����܂��j';
			return window.confirm(msg);
		}
		resetTab.appendChild(resetBtn);

		// �u�f�t�H���g�ɖ߂��v�^�u
		var defaultTab = getTab();
		var defaultBtn = getBtn('submit', 'submit_default', '�f�t�H���g�ɖ߂�');
		defaultBtn.onclick = function() {
			var msg = '���[�U�ݒ���f�t�H���g�ɖ߂��Ă���낵���ł����H' + '\n';
				msg += '�i��蒼���͂ł��܂���j';
			return window.confirm(msg);
		}
		defaultTab.appendChild(defaultBtn);

		// �^�u��ǉ�
		tabs[i].appendChild(document.createElement('li')).appendChild(saveTab);
		tabs[i].appendChild(document.createElement('li')).appendChild(resetTab);
		tabs[i].appendChild(document.createElement('li')).appendChild(defaultTab);
		return;
	}
};

// }}}

(function(){
	if (typeof window.onload == 'function') {
		var oldonload = window.onload;
		window.onload = function(event) {
			oldonload(event);
			_edit_conf_user_js_onload();
		};
	} else {
		window.onload = _edit_conf_user_js_onload;
	}
})();

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

