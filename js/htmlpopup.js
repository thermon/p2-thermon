/*
	p2 - HTML���|�b�v�A�b�v���邽�߂�JavaScript
*/

//showHtmlDelaySec = 0.2 * 1000; // HTML�\���f�B���C�^�C���B�}�C�N���b�B

showHtmlTimerID = 0;
node_div = false;
node_close = false;
tUrl = ""; // URL�e���|�����ϐ�
gUrl = ""; // URL�O���[�o���ϐ�
gX = 0;
gY = 0;

/**
 * �N���[�Y�{�^���������̓|�b�v�A�b�v�O�����N���b�N���ĕ��邽�߂̊֐�
 */
function hideHtmlPopUpCallback(evt)
{
	evt = evt || window.event;

	hideHtmlPopUp();

	// �C�x���g���X�i���폜
	if (window.removeEventListener) {
		// W3C  DOM
		document.body.removeEventListener('click', hideHtmlPopUpCallback, false);
		evt.preventDefault();
		evt.stopPropagation();
	} else if (window.detachEvent) {
		// IE
		document.body.detachEvent('onclick', hideHtmlPopUpCallback);
		evt.returnValue = false;
		evt.cancelBubble = true;
	}
}

/**
 * HTML�|�b�v�A�b�v��\������
 *
 * �����̈��p���X�Ԃ�(p)�� onMouseover �ŌĂяo�����
 */
function showHtmlPopUp(url,ev,showHtmlDelaySec)
{
	if (!document.createElement) { return; } // DOM��Ή�

	// �܂� onLoad ����Ă��Ȃ��A�R���e�i���Ȃ���΁A������
	if (!gIsPageLoaded && !document.getElementById('popUpContainer')) {
		return;
	}

	showHtmlDelaySec = showHtmlDelaySec * 1000;

	if (!node_div || url != gUrl) {
		tUrl = url;
		gX = getPageX(ev);
		gY = getPageY(ev);
		showHtmlTimerID = setTimeout("showHtmlPopUpDo()", showHtmlDelaySec); // HTML�\���f�B���C�^�C�}�[
	}
}

/**
 * HTML�|�b�v�A�b�v�̎��s
 */
function showHtmlPopUpDo()
{
	// ���炩���ߊ�����HTML�|�b�v�A�b�v����Ă���
	hideHtmlPopUp();

	gUrl = tUrl;
	var x_adjust = 7;	// x���ʒu����
	var y_adjust = -46;	// y���ʒu����
	var closebox_width = 18;

	if (!node_div) {
		node_div = document.createElement('div');
		node_div.setAttribute('id', "iframespace");

		if (!window.addEventListener && !window.attachEvent) {
			node_close = document.createElement('div');
			node_close.setAttribute('id', "closebox");
		}

		node_div.style.left = gX + x_adjust + "px"; //�|�b�v�A�b�v�ʒu
		node_div.style.top = getScrollY() + "px"; //gY + y_adjust + "px";
		if (node_close) {
			node_close.style.left = (gX + x_adjust - closebox_width) + "px"; // �|�b�v�A�b�v�ʒu
			node_close.style.top = node_div.style.top;
		}
		var b_adjust = 4; // iframe��(frameborder+border)*2
		var yokohaba = getWindowWidth() - b_adjust - gX - x_adjust;
		var tatehaba = getWindowHeight() - b_adjust;

		pageMargin = "";
		// �摜�̏ꍇ�̓}�[�W�����[����
		if (gUrl.search(/\.(jpe?g|gif|png)$/) !== -1) {
			pageMargin = ' marginheight="0" marginwidth="0" hspace="0" vspace="0"';
		}
		node_div.innerHTML = '<iframe src="' + gUrl + '" frameborder="1" border="1" style="background-color:#fff;" width="' + yokohaba + '" height="' + tatehaba + '"' + pageMargin + '>&nbsp;</iframe>';

		if (node_close) {
			node_close.innerHTML = '<b onclick="hideHtmlPopUpCallback(event)" style="cursor:pointer;">�~</b>';
		}

		var popUpContainer = document.getElementById("popUpContainer");
		if (!popUpContainer) {
			popUpContainer = document.body;
		}
		popUpContainer.appendChild(node_div);
		if (node_close) {
			popUpContainer.appendChild(node_close);
		}
	}

	// HTML�|�b�v�A�b�v�O�����N���b�N���Ă�������悤�ɂ���
	if (window.addEventListener) {
		// W3C  DOM
		document.body.addEventListener('click', hideHtmlPopUpCallback, false);
	} else if (window.attachEvent) {
		// IE
		document.body.attachEvent('onclick', hideHtmlPopUpCallback);
	}
}

/**
 * HTML�|�b�v�A�b�v���\���ɂ���
 */
function hideHtmlPopUp()
{
	if (!document.createElement) { return; } // DOM��Ή�
	if (showHtmlTimerID) { clearTimeout(showHtmlTimerID); } // HTML�\���f�B���C�^�C�}�[������
	if (node_div) {
		node_div.style.visibility = "hidden";
		node_div.parentNode.removeChild(node_div);
		node_div = false;
	}
	if (node_close) {
		node_close.style.visibility = "hidden";
		node_close.parentNode.removeChild(node_close);
		node_close = false;
	}
}

/**
 * HTML�\���^�C�}�[����������
 *
 * (p)�� onMouseout �ŌĂяo�����
 */
function offHtmlPopUp()
{
	// HTML�\���f�B���C�^�C�}�[������Ή������Ă���
	if (showHtmlTimerID) {
		clearTimeout(showHtmlTimerID);
	}
}

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
