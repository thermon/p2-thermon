/*
 * rep2expack - ���X�ԍ��|�b�v�A�b�v���j���[
 */

var SPM = {};
var spmResNum     = -1; // �|�b�v�A�b�v�ŎQ�Ƃ��郌�X�ԍ�
var spmBlockID    = ''; // �t�H���g�ύX�ŎQ�Ƃ���ID
var spmSelected   = ''; // �I�𕶎�����ꎞ�I�ɕۑ�
var spmFlexTarget = ''; // �t�B���^�����O���ʂ��J���E�C���h�E

/**
 * �R�[���o�b�N�֐��R���e�i
 */
SPM.callbacks = {};

/**
 * �X�}�[�g�|�b�v�A�b�v���j���[�𐶐�����
 */
SPM.init = function (aThread) {
	var threadId = aThread.objName;
	if (document.getElementById(threadId + '_spm')) {
		return false;
	}
	var opt = aThread.spmOption;

	// �|�b�v�A�b�v���j���[����
	var spm = document.createElement('div');
	spm.id = threadId + '_spm';
	spm.className = 'spm';
	spm.appendItem = function() {
		this.appendChild(SPM.createMenuItem.apply(this, arguments));
	};
	SPM.setOnPopUp(spm, spm.id, false);

	// �R�s�y�p�t�H�[��
	spm.appendItem('���X�R�s�[', (function () {
		SPM.invite(aThread);
	}));

	// ����Ƀ��X
	if (opt[1] == 1 || opt[1] == 2) {
		spm.appendItem('����Ƀ��X', [aThread, 'post_form.php', 'inyou=' + (2 & opt[1]).toString()]);
		spm.appendItem('���p���ă��X', [aThread, 'post_form.php', 'inyou=' + ((2 & opt[1]) + 1).toString()]);
	}

	// �t�Q��
	spm.appendItem('�t�Q��', (function (event) {
		SPM.openFilter(aThread, 'rres', 'on', event);
	}));

	// �����܂œǂ�
	spm.appendItem('�����܂œǂ�', (function () {
		SPM.httpcmd('setreadnum', aThread, SPM.callbacks.setreadnum);
	}));

	// �u�b�N�}�[�N (������)

	// ���ځ[��/NG/�n�C���C�g���[�h
	if (opt[2] == 1 || opt[2] == 2) {
		var abnId = threadId + '_ab';
		var ngId = threadId + '_ng';
		var highlightId = threadId + '_highlight';
		spm.appendItem('���ځ[�񂷂�', [aThread, 'info_sp.php', 'mode=aborn_res']);
		spm.appendItem('���ځ[�񃏁[�h', null, abnId);
		spm.appendItem('NG���[�h', null, ngId);
		spm.appendItem('�n�C���C�g���[�h', null, highlightId);
		// �T�u���j���[����
		var spmAborn = SPM.createNgAbornSubMenu(abnId, aThread, 'aborn');
		var spmNg = SPM.createNgAbornSubMenu(ngId, aThread, 'ng');
		var spmHighlight = SPM.createNgAbornSubMenu(highlightId, aThread, 'highlight');
	} else {
		var spmAborn = false, spmNg = false, spmHighlight = false;
	}

	// �t�B���^�����O
	if (opt[3] == 1) {
		var filterId = threadId + '_fl';
		spm.appendItem('�t�B���^�����O', null, filterId);
		// �T�u���j���[����
		var spmFilter = SPM.createFilterSubMenu(filterId, aThread);
	} else {
		var SpmFilter = false;
	}

	// �A�N�e�B�u���i�[
	if (opt[4] == 1) {
		spm.appendItem('AA�p�t�H���g', (function () {
			activeMona(SPM.getBlockID());
		}));
	}

	// AAS
	if (opt[5] == 1) {
		spm.appendItem('AAS', [aThread, 'aas.php']);
	}

	// PRE
	/*spm.appendItem('PRE', (function () {
		var msg = document.getElementById(SPM.getBlockID());
		if (msg.style.whiteSpace == 'pre') {
			msg.style.whiteSpace = 'normal';
		} else {
			msg.style.whiteSpace = 'pre';
		}
	}));*/

	// �|�b�v�A�b�v�E�R���e�i���擾 or �쐬
	var container = document.getElementById('popUpContainer');
	if (!container) {
		container = document.createElement('div');
		container.id = 'popUpContainer';
		container.style.position = 'absolute';
		document.body.insertBefore(container, document.body.firstChild);
	}

	// �|�b�v�A�b�v���j���[���R���e�i�ɒǉ�
	container.appendChild(spm);

	// ���ځ[�񃏁[�h�E�T�u���j���[���R���e�i�ɒǉ�
	if (spmAborn) {
		container.appendChild(spmAborn);
	}
	// NG���[�h�E�T�u���j���[���R���e�i�ɒǉ�
	if (spmNg) {
		container.appendChild(spmNg);
	}
	// �n�C���C�g���[�h�E�T�u���j���[���R���e�i�ɒǉ�
	if (spmHighlight) {
		container.appendChild(spmHighlight);
	}
	// �t�B���^�����O�E�T�u���j���[���R���e�i�ɒǉ�
	if (spmFilter) {
		container.appendChild(spmFilter);
	}
	// �\���E��\�����\�b�h��ݒ�
	aThread.show = (function(resnum, resid, event){
		SPM.show(aThread, resnum, resid, event);
	});
	aThread.hide = (function(event){
		SPM.hide(aThread, event);
	});

	return false;
};

/**
 * �X�}�[�g�|�b�v�A�b�v���j���[���|�b�v�A�b�v�\������
 */
SPM.show = function (aThread, resnum, resid, event) {
	event = event || window.event;
	if (spmResNum != resnum || spmBlockID != resid) {
		SPM.hideImmediately(aThread, event);
	}
	spmResNum  = resnum;
	spmBlockID = resid;
	if (window.getSelection) {
		spmSelected = window.getSelection();
	} else if (document.selection) {
		spmSelected = document.selection.createRange().text;
	}
	showResPopUp(aThread.objName + '_spm' ,event);
	return false;
};

/**
 * �X�}�[�g�|�b�v�A�b�v���j���[�����
 */
SPM.hide = function (aThread, event) {
	event = event || window.event;
	hideResPopUp(aThread.objName + '_spm');
	return false;
};

/**
 * �X�}�[�g�|�b�v�A�b�v���j���[��x���[���ŕ���
 */
SPM.hideImmediately = function (aThread, event) {
	event = event || window.event;
	document.getElementById(aThread.objName + '_spm').style.visibility = 'hidden';
	return false;
};

/**
 * �N���[�W������O���[�o���ϐ� spmBlockID ���擾���邽�߂̊֐�
 */
SPM.getBlockID = function() {
	return spmBlockID;
};

/**
 * �N���b�N���Ɏ��s�����֐� (�|�b�v�A�b�v�E�C���h�E���J��) ��ݒ肷��
 */
SPM.setOnClick = function (obj, aThread, inUrl) {
	var option = (arguments.length > 3) ? arguments[3] : '';
	obj.onclick = function (event) {
		event = event || window.event;
		if (event) {
			return SPM.openSubWin(aThread, inUrl, option);
		}
		return false;
	};
};

/**
 * �}�E�X�I�[�o�[/�A�E�g���Ɏ��s�����֐� (���j���[�̕\��/��\��) ��ݒ肷��
 */
SPM.setOnPopUp = function (obj, targetId, isSubMenu) {
	// ���[���I�[�o�[
	obj.onmouseover = function (event) {
		event = event || window.event;
		if (event) {
			showResPopUp(targetId, event);
		}
	};
	// ���[���A�E�g
	obj.onmouseout = function (event) {
		event = event || window.event;
		if (event) {
			hideResPopUp(targetId);
		}
	}
};

/**
 * �A���J�[�𐶐�����
 */
SPM.createMenuItem = function (txt) {
	var anchor = document.createElement('a');
	anchor.href = 'javascript:void(null)';
	anchor.onclick = function() {
		return false;
	};
	anchor.appendChild(document.createTextNode(txt));

	// �N���b�N���ꂽ�Ƃ��̃C�x���g�n���h����ݒ�
	if (arguments.length > 1 && arguments[1] != null) {
		if (typeof arguments[1] === 'function') {
			anchor.onclick = arguments[1];
		} else {
			var aThread = arguments[1][0];
			var inUrl = arguments[1][1];
			var option = (arguments[1].length > 2) ? arguments[1][2] : '';
			SPM.setOnClick(anchor, aThread, inUrl, option);
		}
	}

	// �T�u���j���[���|�b�v�A�b�v����C�x���g�n���h����ݒ�
	if (arguments.length > 2 && arguments[2] != null) {
		SPM.setOnPopUp(anchor, arguments[2], true);
	}

	return anchor;
};

/**
 * ���ځ[��/NG�T�u���j���[�𐶐�����
 */
SPM.createNgAbornSubMenu = function (menuId, aThread, mode) {
	var amenu = document.createElement('div');
	amenu.id = menuId;
	amenu.className = 'spm';
	amenu.appendItem = function () {
		this.appendChild(SPM.createMenuItem.apply(this, arguments));
	};
	SPM.setOnPopUp(amenu, amenu.id, true);

	amenu.appendItem('���O', [aThread, 'info_sp.php', 'mode=' + mode + '_name']);
	amenu.appendItem('���[��', [aThread, 'info_sp.php', 'mode=' + mode + '_mail']);
	amenu.appendItem('�{��', [aThread, 'info_sp.php', 'mode=' + mode + '_msg']);
	amenu.appendItem('ID', [aThread, 'info_sp.php', 'mode=' + mode + '_id']);

	return amenu;
};

/**
 * �t�B���^�����O�T�u���j���[�𐶐�����
 */
SPM.createFilterSubMenu = function (menuId, aThread) {
	this.getOnClick = function (field, match) {
		return (function (event) {
			SPM.openFilter(aThread, field, match, event);
		});
	}

	var fmenu = document.createElement('div');
	fmenu.id = menuId;
	fmenu.className = 'spm';
	fmenu.appendItem = function() {
		this.appendChild(SPM.createMenuItem.apply(this, arguments));
	};
	SPM.setOnPopUp(fmenu, fmenu.id, true);

	fmenu.appendItem('�������O', this.getOnClick('name', 'on'));
	fmenu.appendItem('�������[��', this.getOnClick('mail', 'on'));
	fmenu.appendItem('�������t', this.getOnClick('date', 'on'));
	fmenu.appendItem('����ID', this.getOnClick('id', 'on'));
	fmenu.appendItem('�قȂ閼�O', this.getOnClick('name', 'off'));
	fmenu.appendItem('�قȂ郁�[��', this.getOnClick('mail', 'off'));
	fmenu.appendItem('�قȂ���t', this.getOnClick('date', 'off'));
	fmenu.appendItem('�قȂ�ID', this.getOnClick('id', 'off'));

	return fmenu;
};


/* ==================== �o������ ====================
 * <a href="javascript:void(0);" onclick="foo()">��
 * <a href="javascript:void(foo());">�Ɠ����ɓ����B
 * JavaScript��URI�𐶐�����Ƃ��A&��&amp;�Ƃ��Ă͂����Ȃ��B
 * ================================================== */

/**
 * URI�̏��������A�|�b�v�A�b�v�E�C���h�E���J��
 */
SPM.openSubWin = function (aThread, inUrl, option) {
	var inWidth  = 650; // �|�b�v�A�b�v�E�C���h�E�̕�
	var inHeight = 350; // �|�b�v�A�b�v�E�C���h�E�̍���
	var boolS = 1; // �X�N���[���o�[��\���ioff:0, on:1�j
	var boolR = 0; // �������T�C�Y�ioff:0, on:1�j
	var popup = 1; // �|�b�v�A�b�v�E�C���h�E���ۂ��ino:0, yes:1, yes&�^�C�}�[�ŕ���:2�j
	if (inUrl == 'info_sp.php') {
		inWidth  = 480;
		inHeight = 240;
		boolS = 0;
		if (aThread.spmOption[2] == 1) {
			popup = 2; // ���ځ[��/NG���[�h�o�^�̊m�F�����Ȃ��Ƃ�
		}
		if (option.indexOf('_msg') != -1 && spmSelected != '') {
			option += '&selected_string=' + encodeURIComponent(spmSelected);
		}
	} else if (inUrl == 'post_form.php') {
		if (aThread.spmOption[1] == 2) {
			// inHeight = 450;
		}
		if (location.href.indexOf('/read_new.php?') != -1) {
			if (option == '') {
				option = 'from_read_new=1';
			} else {
				option += '&from_read_new=1';
			}
		}
	} else if (inUrl == 'tentori.php') {
		inWidth  = 450;
		inHeight = 150;
		popup = 2;
	} else if (inUrl == 'aas.php') {
		inWidth  = (aas_popup_width) ? aas_popup_width : 250;
		inHeight = (aas_popup_height) ? aas_popup_height : 330;
	}
	inUrl += '?host=' + aThread.host + '&bbs=' + aThread.bbs + '&key=' + aThread.key.toString();
	inUrl += '&rescount=' + aThread.rc.toString() + '&ttitle_en=' + aThread.ttitle_en;
	inUrl += '&resnum=' + spmResNum.toString() + '&popup=' + popup.toString();
	if (option != '') {
		inUrl += '&' + option;
	}
	OpenSubWin(inUrl, inWidth, inHeight, boolS, boolR);
	return true;
};

/**
 * URI�̏��������A�t�B���^�����O���ʂ�\������
 */
SPM.openFilter = function (aThread, field, match, event) {
	var target;
	var inUrl = 'read_filter.php?bbs=' + aThread.bbs + '&key=' + aThread.key + '&host=' + aThread.host;
	inUrl += '&rescount=' + aThread.rc + '&ttitle_en=' + aThread.ttitle_en + '&resnum=' + spmResNum;
	inUrl += '&ls=all&field=' + field + '&method=just&match=' + match + '&offline=1';

	event = event || window.event;
	if (event.shiftKey) {
		target = '_blank';
	} else {
		target = spmFlexTarget;
	}

	switch (target) {
		case '_popup':
			showHtmlPopUp(inUrl, event, 0);
			SPM.hideImmediately(aThread, event);
			break;
		case '_blank':
			window.open(inUrl, '', '');
			break;
		case '_self':
			window.self.location.href = inUrl;
			break;
		case '_parent':
			window.parent.location.href = inUrl;
			break;
		case '_top':
			window.top.location.href = inUrl;
			break;
		default:
			if (window.parent != window.self &&
				typeof window.parent[target] !== 'undefined' &&
				typeof window.parent[target].location !== 'undefined' &&
				typeof window.parent[target].location.href !== 'undefined')
			{
				window.parent[target].location.href = inUrl;
			} else {
				window.open(inUrl, target, '')
			}
	}

	return true;
};

/**
 * �R�s�y�p�ɃX�������|�b�v�A�b�v���� (for SPM)
 */
SPM.invite = function (aThread) {
	Invite(aThread.title, aThread.url, aThread.host, aThread.bbs, aThread.key, spmResNum);
};

/**
 * httpcmd.php�̃��b�p�[
 */
SPM.httpcmd = function (cmd, aThread, callback) {
	var num = spmResNum;
	var url = 'httpcmd.php?host=' + aThread.host + '&bbs=' + aThread.bbs + '&key=' + aThread.key
	        + '&cmd=' + cmd + '&' + cmd + '=' + num;
	var result = getResponseTextHttp(getXmlHttp(), url, true);
	if (typeof callback === 'function') {
		callback(result, cmd, aThread, num, url);
	}
};

/**
 * �u�����܂œǂ񂾁v���N�G�X�g��Ɏ��s����R�[���o�b�N�֐�
 */
SPM.callbacks.setreadnum = function (result, cmd, aThread, num, url) {
	var msg = '�X���b�h�g' + aThread.title + '�h�̊��ǐ���';
	if (result == '1') {
		msg += ' ' + num + ' �ɃZ�b�g���܂����B';
	} else {
		msg += '�Z�b�g�ł��܂���ł����B';
	}
	window.alert(msg);
};

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
