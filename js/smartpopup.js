/* vim: set fileencoding=cp932 ai noet ts=4 sw=4 sts=4: */
/* mi: charset=Shift_JIS */

/* rep2expack - �X�}�[�g�|�b�v�A�b�v���j���[  */

var SPM = new Object();
var spmResNum     = new Number(); // �|�b�v�A�b�v�ŎQ�Ƃ��郌�X�ԍ�
var spmBlockID    = new String(); // �t�H���g�ύX�ŎQ�Ƃ���ID
var spmSelected   = new String(); // �I�𕶎�����ꎞ�I�ɕۑ�
var spmFlexTarget = new String(); // �t�B���^�����O���ʂ��J���E�C���h�E
var spmQuoters =new String();

/**
 * �X�}�[�g�|�b�v�A�b�v���j���[�𐶐�����
 */
SPM.init = function(aThread)
{
	var threadId = aThread.objName;
	if (document.getElementById(threadId + '_spm')) {
		return false;
	}
	var opt = aThread.spmOption;

	// �|�b�v�A�b�v���j���[����
	var spm = document.createElement('div');
	spm.id = threadId + '_spm';
	spm.className = 'spm';
	spm.appendItem = function()
	{
		this.appendChild(SPM.createMenuItem.apply(this, arguments));
	}
	SPM.setOnPopUp(spm, spm.id, false);

//	spm.appendItem('���X������', (function(evt){stophide=true; showHtmlPopUp('read.php?bbs=' + aThread.bbs + '&key=' + aThread.key.toString() + '&host=' + aThread.host + '&ls=all&field=msg&word=%3E' + spmResNum + '%5B%5E%5Cd%5D&method=regex&match=on,renzokupop=true',((evt) ? evt : ((window.event) ? event : null)),0);}));
	spm.appendItem('���X������', (function(evt){stophide=true; showHtmlPopUp('read.php?bbs=' + aThread.bbs + '&key=' + aThread.key.toString() + '&host=' + aThread.host + '&ls=all&field=res&word=%5e%28' + spmQuoters + '%29%24&method=regex&match=on,renzokupop=true',((evt) ? evt : ((window.event) ? event : null)),0);}));

	// �R�s�y�p�t�H�[��
	spm.appendItem('���X�R�s�[', (function(){SPM.invite(aThread)}));

	// ����Ƀ��X
	if (opt[1] == 1 || opt[1] == 2) {
		spm.appendItem('����Ƀ��X', [aThread, 'post_form.php', 'inyou=' + (2 & opt[1]).toString()]);
		spm.appendItem('���p���ă��X', [aThread, 'post_form.php', 'inyou=' + ((2 & opt[1]) + 1).toString()]);
	}

	// ���ځ[�񃏁[�h�ENG���[�h
	if (opt[2] == 1 || opt[2] == 2) {
		var abnId = threadId + '_ab';
		var ngId = threadId + '_ng';
		spm.appendItem('���ځ[�񂷂�', [aThread, 'info_sp.php', 'mode=aborn_res']);
		spm.appendItem('���ځ[�񃏁[�h', null, abnId);
		spm.appendItem('NG���[�h', null, ngId);
		// �T�u���j���[����
		var spmAborn = SPM.createNgAbornSubMenu(abnId, aThread, 'aborn');
		var spmNg = SPM.createNgAbornSubMenu(ngId, aThread, 'ng');
	} else {
		var spmAborn = false, spmNg = false;
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
		spm.appendItem('AA�p�t�H���g', (function(){activeMona(SPM.getBlockID())}));
	}

	// AAS
	if (opt[5] == 1) {
		spm.appendItem('AAS', [aThread, 'aas.php']);
	}

	// PRE
	/*spm.appendItem('PRE', (function(){
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
	// �t�B���^�����O�E�T�u���j���[���R���e�i�ɒǉ�
	if (spmFilter) {
		container.appendChild(spmFilter);
	}

	// �\���E��\�����\�b�h��ݒ�
	aThread.show = (function(resnum, resid, quoters, evt){
		SPM.show(aThread, resnum, resid, quoters,evt);
	});
	aThread.hide = (function(evt){
		SPM.hide(aThread, evt);
	});

	return false;
}

/**
 * �X�}�[�g�|�b�v�A�b�v���j���[���|�b�v�A�b�v�\������
 */
SPM.show = function(aThread, resnum, resid, quoters,evt)
{
	var evt = (evt) ? evt : ((window.event) ? event : null);
	if (spmResNum != resnum || spmBlockID != resid) {
		SPM.hideImmediately(aThread, evt);
	}
	spmResNum  = resnum;
	spmBlockID = resid;
	spmQuoters =quoters;
	if (window.getSelection) {
		spmSelected = window.getSelection();
	} else if (document.selection) {
		spmSelected = document.selection.createRange().text;
	}
	if (document.all) { // IE�p
		document.all[aThread.objName + '_spm'].firstChild.firstChild.nodeValue = resnum + '�ւ̃��X������';
	} else if (document.getElementById) { // DOM�Ή��p�iMozilla�j
		document.getElementById(aThread.objName + '_spm').firstChild.firstChild.nodeValue = resnum + '�ւ̃��X������';
	}
	showResPopUp(aThread.objName + '_spm' ,evt);
	return false;
}

/**
 * �X�}�[�g�|�b�v�A�b�v���j���[�����
 */
SPM.hide = function(aThread, evt)
{
	var evt = (evt) ? evt : ((window.event) ? event : null);
	hideResPopUp(aThread.objName + '_spm');
	return false;
}

/**
 * �X�}�[�g�|�b�v�A�b�v���j���[��x���[���ŕ���
 */
SPM.hideImmediately = function(aThread, evt)
{
	var evt = (evt) ? evt : ((window.event) ? event : null);
	document.getElementById(aThread.objName + '_spm').style.visibility = 'hidden';
	return false;
}

/**
 * �N���[�W������O���[�o���ϐ� spmBlockID ���擾���邽�߂̊֐�
 */
SPM.getBlockID = function()
{
	return spmBlockID;
}

/**
 * �N���b�N���Ɏ��s�����֐� (�|�b�v�A�b�v�E�C���h�E���J��) ��ݒ肷��
 */
SPM.setOnClick = function(obj, aThread, inUrl)
{
	var option = (arguments.length > 3) ? arguments[3] : '';
	obj.onclick = function(evt)
	{
		evt = (evt) ? evt : ((window.event) ? window.event : null);
		if (evt) {
			return SPM.openSubWin(aThread, inUrl, option);
		}
		return false;
	}
}

/**
 * �}�E�X�I�[�o�[/�A�E�g���Ɏ��s�����֐� (���j���[�̕\��/��\��) ��ݒ肷��
 */
SPM.setOnPopUp = function(obj, targetId, isSubMenu)
{
	// ���[���I�[�o�[
	obj.onmouseover = function(evt)
	{
		evt = (evt) ? evt : ((window.event) ? window.event : null);
		if (evt) {
			showResPopUp(targetId, evt);
		}
	}
	// ���[���A�E�g
	obj.onmouseout = function(evt)
	{
		evt = (evt) ? evt : ((window.event) ? window.event : null);
		if (evt) {
			hideResPopUp(targetId);
		}
	}
}

/**
 * �A���J�[�𐶐�����
 */
SPM.createMenuItem = function(txt)
{
	var anchor = document.createElement('a');
	anchor.href = 'javascript:void(null)';
	anchor.onclick = function() { return false; }
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
}

/**
 * ���ځ[��/NG�T�u���j���[�𐶐�����
 */
SPM.createNgAbornSubMenu = function(menuId, aThread, mode)
{
	var amenu = document.createElement('div');
	amenu.id = menuId;
	amenu.className = 'spm';
	amenu.appendItem = function()
	{
		this.appendChild(SPM.createMenuItem.apply(this, arguments));
	}
	SPM.setOnPopUp(amenu, amenu.id, true);

	amenu.appendItem('���O', [aThread, 'info_sp.php', 'mode=' + mode + '_name']);
	amenu.appendItem('���[��', [aThread, 'info_sp.php', 'mode=' + mode + '_mail']);
	amenu.appendItem('�{��', [aThread, 'info_sp.php', 'mode=' + mode + '_msg']);
	amenu.appendItem('ID', [aThread, 'info_sp.php', 'mode=' + mode + '_id']);

	return amenu;
}

/**
 * �t�B���^�����O�T�u���j���[�𐶐�����
 */
SPM.createFilterSubMenu = function(menuId, aThread)
{
	this.getOnClick = function(field, match)
	{
		return (function(evt){
			evt = (evt) ? evt : ((window.event) ? window.event : null);
			if (evt) { SPM.openFilter(aThread, field, match); }
		});
	}

	var fmenu = document.createElement('div');
	fmenu.id = menuId;
	fmenu.className = 'spm';
	fmenu.appendItem = function()
	{
		this.appendChild(SPM.createMenuItem.apply(this, arguments));
	}
	SPM.setOnPopUp(fmenu, fmenu.id, true);

	fmenu.appendItem('���̃��X', this.getOnClick('num', 'on'));
	fmenu.appendItem('�������O', this.getOnClick('name', 'on'));
	fmenu.appendItem('�������[��', this.getOnClick('mail', 'on'));
	fmenu.appendItem('�������t', this.getOnClick('date', 'on'));
	fmenu.appendItem('����ID', this.getOnClick('id', 'on'));
	fmenu.appendItem('�قȂ閼�O', this.getOnClick('name', 'off'));
	fmenu.appendItem('�قȂ郁�[��', this.getOnClick('mail', 'off'));
	fmenu.appendItem('�قȂ���t', this.getOnClick('date', 'off'));
	fmenu.appendItem('�قȂ�ID', this.getOnClick('id', 'off'));

	return fmenu;
}

/* ==================== �o������ ====================
 * <a href="javascript:void(0);" onclick="foo()">��
 * <a href="javascript:void(foo());">�Ɠ����ɓ����B
 * JavaScript��URI�𐶐�����Ƃ��A&��&amp;�Ƃ��Ă͂����Ȃ��B
 * ================================================== */

/**
 * URI�̏��������A�|�b�v�A�b�v�E�C���h�E���J��
 */
SPM.openSubWin = function(aThread, inUrl, option)
{
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
}

/**
 * URI�̏��������A�t�B���^�����O���ʂ�\������
 */
SPM.openFilter = function(aThread, field, match)
{
	var inUrl = '?bbs=' + aThread.bbs + '&key=' + aThread.key + '&host=' + aThread.host;
	inUrl += '&rescount=' + aThread.rc + '&ttitle_en=' + aThread.ttitle_en ;
	inUrl += '&offline=1';
	switch (field) {
        case 'num' :
            inUrl= 'read.php' +inUrl;
            inUrl += '&ls=' +spmResNum;
            break;
        default :
            inUrl= 'read_filter.php' +inUrl;
    	    inUrl += '&resnum=' + spmResNum +'&ls=all&field=' + field + '&method=just&match=' + match ;
	}

	switch (spmFlexTarget) {
		case '_self':
			location.href = inUrl;
			break;
		case '_parent':
			parent.location.href = inUrl;
			break;
		case '_top':
			top.location.href = inUrl;
			break;
		case '_blank':
			window.open(inUrl, '', '');
			break;
		default:
			if (parent.spmFlexTarget.location.href) {
				parent.spmFlexTarget.location.href = inUrl;
			} else {
				window.open(inUrl, spmFlexTarget, '')
			}
	}

	return true;
}

/**
 * �R�s�y�p�ɃX�������|�b�v�A�b�v���� (for SPM)
 */
SPM.invite = function(aThread)
{
	Invite(aThread.title, aThread.url, aThread.host, aThread.bbs, aThread.key, spmResNum);
}

// ����݊��̂��߁A�ꉞ
makeSPM = SPM.init;
showSPM = SPM.show;
closeSPM = SPM.hide;
