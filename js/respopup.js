/* p2 - ���p���X�Ԃ��|�b�v�A�b�v���邽�߂�JavaScript */

/*
document.open;
document.writeln('<style type="text/css" media="all">');
document.writeln('<!--');
document.writeln('.respopup{visibility: hidden;}');
document.writeln('-->');
document.writeln('</style>');
document.close;
*/

delayShowSec = 0.1 * 1000;	// ���X�|�b�v�A�b�v��\������x�����ԁB
delaySec = 0.3 * 1000;	// ���X�|�b�v�A�b�v���\���ɂ���x�����ԁB
zNum = 0;

//==============================================================
// gPOPS -- ResPopUp �I�u�W�F�N�g���i�[����z��B
// �z�� gPOPS �̗v�f�����A���ݐ����Ă��� ResPopUp �I�u�W�F�N�g�̐��ƂȂ�B
//==============================================================
gPOPS = new Array();

gResPopCtl = new ResPopCtl();

gShowTimerIds = new Object();

isIE = /*@cc_on!@*/false;

function getElement(id) {
	// alert(id);

	if (typeof(id) == "string") {
		if (isIE) { // IE�p
			return document.all[id];
		} else if (document.getElementById) { // DOM�Ή��p�iMozilla�j
			return document.getElementById(id);
		}
	} else {
		return id;
	}
}

function toggleResBlk(evt, res, mark) {
	var evt = (evt) ? evt : ((window.event) ? window.event : null);
	var target = evt.target ? evt.target :
		(evt.srcElement ? evt.srcElement : null);
	if(typeof(res.ondblclick) !== 'function')
		res.ondblclick = res.onclick;

	// �C�x���g�����`�F�b�N
	if (target.className == 'respopup') return;
	var resblock = _findChildByClassName(res, 'resblock');
	if (evt == null || res == null || target == null || resblock == null)
		return;
	var button = resblock.firstChild;
	if (!button) return;
	if (target != res && target != resblock && target != button) {
		// ���X���X�g�̃N���b�N���ǂ���
		var isdescend = function (check) {
			if (!check) return false;
			var test = target;
			do {
				if (test == check) return true;
				test = test.parentNode;
			} while (test && test != res);
		};
		if (!isdescend(_findChildByClassName(res, 'reslist')) &&
			!isdescend(_findChildByClassName(res, 'v_reslist')))
			return;
	}

	var anchors = _findAnchorComment(res);
	if (anchors == null) return;

	if (_findChildByClassName(resblock, 'resblock_inner') !== null &&
			evt.type != 'dblclick') {
		if (mark) resetReaded(res, anchors);
		removeRes(res, button);
	} else {
		insertRes(evt, res, anchors, mark);
	}
}

function insertRes(evt, res, anchors, mark) {

	var resblock = _findChildByClassName(res, 'resblock');
	if (!resblock) return;
	var button = resblock.firstChild;
	var resblock_inner = _findChildByClassName(resblock, 'resblock_inner');
	// ���ɊJ���Ă����ꍇ
	if (resblock_inner) {
		if (evt.type != 'dblclick') return;
		// �_�u���N���b�N�Ȃ�J�X�P�[�h
		(function (nodes) {
			for  (var i=0;i<nodes.length;i++) {
				if (nodes[i].className != 'folding_container') continue;
				var anchor = _findAnchorComment(nodes[i]);
				if (anchor != null)
					insertRes(evt, nodes[i],
						_findAnchorComment(nodes[i]), mark);
			}
		 })(resblock_inner.childNodes);
		 return;
	 }

	// reslist������Δ�\����
	var reslist = _findChildByClassName(res, 'reslist');
	if (reslist) reslist.style.display = 'none';

	var resblock_inner = document.createElement('div');
	var children=anchors.split("/");
	for (var i=0;i<children.length;i++) {
		var importId=children[i];
		var importElement=getElementForCopy(""+importId);

		// �I���W�i���̃��X������Ό����ڕύX
		if (mark) (function(origId) {
			var orig = (document.all) ?  document.all[origId]
				: ((document.getElementById) ? document.getElementById(origId)
						: null);
			if (orig) {
				var kls = orig.className.split(' ');
				kls.push('readmessage');
				orig.className = kls.join(' ');
			}
		})('m' + importId.substr(2));

		//�Q�Ɛ惌�X�����R�s�[
		var container=document.createElement('blockquote');
		container.innerHTML=importElement.innerHTML.replace(/id=\".+?\"/g,"");

		var anchor = _findAnchorComment(importElement);
		if (anchor) {
			container.onclick = function (evt) {
				toggleResBlk(evt, this, mark);
			};
			var c_resblock=document.createElement('div');
			c_resblock.className = 'resblock';
			if (button)
				c_resblock.appendChild(button.cloneNode(false));

			var reslist = _findChildByClassName(container, 'reslist');
			if (reslist) {
				container.insertBefore(c_resblock, reslist);
			} else {
				container.appendChild(c_resblock);
			}
			// �_�u���N���b�N�Ȃ�J�X�P�[�h
			if (evt.type == 'dblclick') {
				insertRes(evt, container, anchor, mark);
			}
		}
		container.className='folding_container';
		resblock_inner.appendChild(container);
	}
	resblock_inner.className='resblock_inner';
	resblock.appendChild(resblock_inner);

	if (button) button.src=button.src.replace(/plus/,'minus');
}

function removeRes(res, button) {

	button.src=button.src.replace(/minus/,'plus');
	var resblock_inner = _findChildByClassName(
			button.parentNode, 'resblock_inner');
	if (resblock_inner) button.parentNode.removeChild(resblock_inner);

	// reslist������Ε\��
	var reslist = _findChildByClassName(res, 'reslist');
	if (reslist) reslist.style.display = 'block';
}

function resetReaded(res, anchors) {
	var resblock = _findChildByClassName(res, 'resblock');
	if (resblock == null) return;
	var resblock_inner = _findChildByClassName(resblock, 'resblock_inner');
	if (resblock_inner == null) return;

	var children=anchors.split("/");
	for (var i=0;i<children.length;i++) {
		// �I���W�i���̃��X������Ό����ڕύX

		var origId = 'm' + children[i].substr(2);
		var orig = (document.all) ?  document.all[origId]
			: ((document.getElementById) ? document.getElementById(origId)
					: null);
		if (orig) {
			var kls = orig.className.split(' ');
			for (var j=0;j<kls.length;j++) {
				if (kls[j] == 'readmessage') {
					kls.splice(j, 1);
					orig.className = kls.join(' ');
					break;
				}
			}
		}
	}

	for (var i=0;i<resblock_inner.childNodes.length;i++) {
		resetReaded(resblock_inner.childNodes[i],
			_findAnchorComment(resblock_inner.childNodes[i]));
	}
}

function getElementForCopy(qresID) {
	if (qresID.indexOf("-") != -1) { return null; } // �A�� (>>1-100) �͔�Ή��Ȃ̂Ŕ�����
	
	if (document.all) { // IE�p
		aResPopUp = document.all[qresID];
	} else if (document.getElementById) { // DOM�Ή��p�iMozilla�j
		aResPopUp = document.getElementById(qresID);
	}

	if (aResPopUp) {
		return aResPopUp;
	} else {
		return null;
	}
}

function _findChildByClassName(p, kls) {
	for (var i=0;i<p.childNodes.length;i++) {
		if (p.childNodes[i].className == kls)
			return p.childNodes[i];
	}
	return null;
}

function _findAnchorComment(res) {
	for (var i=0;i<res.childNodes.length;i++) {
		if (res.childNodes[i].nodeName.toLowerCase().indexOf('comment') != -1) {
			var nv = res.childNodes[i].nodeValue.replace(/^\s+|\s+$/g, '');
			if (nv.indexOf('backlinks:') == 0) {
				return nv.substr('backlinks:'.length);
			}
		}
	}
	return null;
}

/**
 * ���X�|�b�v�A�b�v��\���^�C�}�[����
 *
 * ���p���X�Ԃ� onMouseover �ŌĂяo�����
 */
function showResPopUp(divID, ev) {
//	alert("show");
	if (divID.indexOf("-") != -1) { return; } // �A�� (>>1-100) �͔�Ή��Ȃ̂Ŕ�����

	var aResPopUp = gResPopCtl.getResPopUp(divID);
	if (aResPopUp) {
		if (aResPopUp.hideTimerID) { clearTimeout(aResPopUp.hideTimerID); } // ��\���^�C�}�[������
	} else {
		// doShowResPopUp(divID, ev);

		x = getPageX(ev);
		y = getPageY(ev);

		aShowTimer = new Object();
		aShowTimer.timerID = setTimeout("doShowResPopUp('" + divID + "')", delayShowSec); // ��莞�Ԃ�����\������

		aShowTimer.x = x;
		aShowTimer.y = y;

		gShowTimerIds[divID] = aShowTimer;
		//// alert(gShowTimerIds[divID].timerID);
	}
}

/**
 * ���X�|�b�v�A�b�v��\������
 */
function doShowResPopUp(divID) {

	x = gShowTimerIds[divID].x;
	y = gShowTimerIds[divID].y;

	var aResPopUp = gResPopCtl.getResPopUp(divID);
	if (aResPopUp) {
		if (aResPopUp.hideTimerID) { clearTimeout(aResPopUp.hideTimerID); } // ��\���^�C�}�[������

		/*
		// �ĕ\������ zIndex ���� ------------------------
		// �������Ȃ������Ғʂ�̓�������Ă���Ȃ��B
		// IE��Mozilla�ŋ������Ⴄ�B����Ĕ�A�N�e�B�u�B
		aResPopUp.zNum = zNum;
		aResPopUp.popOBJ.style.zIndex = aResPopUp.zNum;
		//----------------------------------------
		*/

	} else {
		zNum++;
		aResPopUp = gResPopCtl.addResPopUp(divID); // �V�����|�b�v�A�b�v��ǉ�
	}

	aResPopUp.showResPopUp(x, y);

	(function (divid) {	// �|�b�v�A�b�v�̌�������΃n�C���C�g
		if (document.all) { // IE�p
			var orig = document.all['r' + divID.substr(2)];
		} else if (document.getElementById) { // DOM�Ή��p�iMozilla�j
			var orig = document.getElementById('r' + divID.substr(2));
		}
		 if (orig) {
			var kls = orig.className.split(' ');
			kls.push('highlight');
			orig.className = kls.join(' ');
		}
	})(divID);
}

/**
 * ���X�|�b�v�A�b�v���\���^�C�}�[����
 *
 * ���p���X�Ԃ��� onMouseout �ŌĂяo�����
 */
function hideResPopUp(divID) {
	if (divID.indexOf("-") != -1) { return; } // �A�� (>>1-100) �͔�Ή��Ȃ̂Ŕ�����

	// �\���^�C�}�[������
	if (gShowTimerIds[divID] && gShowTimerIds[divID].timerID) {
		clearTimeout(gShowTimerIds[divID].timerID);
	}

	var aResPopUp = gResPopCtl.getResPopUp(divID);
	if (aResPopUp) {
		aResPopUp.hideResPopUp();
	}
}

/**
 * ���X�|�b�v�A�b�v���\���ɂ���
 */
function doHideResPopUp(divID) {
	var aResPopUp = gResPopCtl.getResPopUp(divID);
	if (aResPopUp) {
		aResPopUp.doHideResPopUp();
	}

	(function (divid) {	// �|�b�v�A�b�v���̃n�C���C�g��߂�
		if (document.all) { // IE�p
			 var orig = document.all['r' + divID.substr(2)];
		} else if (document.getElementById) { // DOM�Ή��p�iMozilla�j
			 var orig = document.getElementById('r' + divID.substr(2));
		}
		 if (orig) {
			var kls = orig.className.split(' ');
			for (var j=0;j<kls.length;j++) {
				if (kls[j] == 'highlight') {
					kls.splice(j, 1);
					orig.className = kls.join(' ');
					break;
				}
			}
		}
	})(divID);
}


/**
 * �I�u�W�F�N�g�f�[�^���R���g���[������N���X
 */
function ResPopCtl() {

	/**
		* �z�� gPOPS �ɐV�K ResPopUp �I�u�W�F�N�g ��ǉ�����
		*/
	ResPopCtl.prototype.addResPopUp = function (divID) {
		var aResPopUp = new ResPopUp(divID);
		// gPOPS.push(aResPopUp); Array.push ��IE5.5�������Ή��Ȃ̂ő�֏���
		return gPOPS[gPOPS.length] = aResPopUp;
	}

	/**
		* �z�� gPOPS ���� �w��� ResPopUp �I�u�W�F�N�g ���폜����
		*/
	ResPopCtl.prototype.rmResPopUp = function (divID) {
		for (i = 0; i < gPOPS.length; i++) {
			if (gPOPS[i].divID == divID) {

				gPOPS = arraySplice(gPOPS, i);

				return true;
			}
		}
		return false;
	}

	/**
		* �z�� gPOPS �Ŏw�� divID �� ResPopUp �I�u�W�F�N�g��Ԃ�
		*/
	ResPopCtl.prototype.getResPopUp = function (divID) {
		for (i = 0; i < gPOPS.length; i++) {
			if (gPOPS[i].divID == divID) {
				return gPOPS[i];
			}
		}
		return false;
	}

	return this;
}

/**
 * arraySplice
 *
 * anArray.splice(i, 1); Array.splice ��IE5.5�������Ή��Ȃ̂ő�֏���
 * @return array
 */
function arraySplice(anArray, i) {
	var newArray = new Array();
	for (j = 0; j < anArray.length; j++) {
		if (j != i) {
			newArray[newArray.length] = anArray[j];
		}
	}
	return newArray;
}

/**
 * ���X�|�b�v�A�b�v�N���X
 */
function ResPopUp(divID) {

	this.divID = divID;
	this.zNum = zNum;
	this.hideTimerID = 0;

	if (document.all) { // IE�p
		this.popOBJ = document.all[this.divID];
	} else if (document.getElementById) { // DOM�Ή��p�iMozilla�j
		this.popOBJ = document.getElementById(this.divID);
	}

	/**
		* ���X�|�b�v�A�b�v��\������
		*/
	ResPopUp.prototype.showResPopUp = function (x, y) {
		var x_adjust = 10;	// x���ʒu����
		var y_adjust = -10;	// y���ʒu����
		if (this.divID.indexOf('spm_') == 0) {
			y_adjust = -10;
		}
		if (this.popOBJ.style.visibility != "visible") {
			this.popOBJ.style.zIndex = this.zNum;
			//x = getPageX(ev); // ���݂̃}�E�X�ʒu��X���W
			//y = getPageX(ev); // ���݂̃}�E�X�ʒu��Y���W
			this.popOBJ.style.left = x + x_adjust + "px"; //�|�b�v�A�b�v�ʒu
			this.popOBJ.style.top = y + y_adjust + "px";
			//// alert(window.pageYOffset);
			//// alert(this.popOBJ.offsetTop);

			var scrollY = getScrollY();
			var windowHeight = getWindowHeight();
			if ((this.popOBJ.offsetTop + this.popOBJ.offsetHeight) > (scrollY + windowHeight)) {
				this.popOBJ.style.top = (scrollY + windowHeight - this.popOBJ.offsetHeight - 20) + "px";
			}
			if (this.popOBJ.offsetTop < scrollY) {
				this.popOBJ.style.top = (scrollY - 2) + "px";
			}

			this.popOBJ.style.visibility = "visible"; // ���X�|�b�v�A�b�v�\��
		}
	}

	/**
		* ���X�|�b�v�A�b�v���\���^�C�}�[����
		*/
	ResPopUp.prototype.hideResPopUp = function () {
		this.hideTimerID = setTimeout("doHideResPopUp('" + this.divID + "')", delaySec); // ��莞�ԕ\�����������
	}

	/**
		* ���X�|�b�v�A�b�v���\���ɂ���
		*/
	ResPopUp.prototype.doHideResPopUp = function () {

		for (i=0; i < gPOPS.length; i++) {

			if (this.zNum < gPOPS[i].zNum) {
				//clearTimeout(this.hideTimerID); // �^�C�}�[������
				this.hideTimerID = setTimeout("hideResPopUp('" + this.divID + "')", delaySec); // ��莞�ԕ\�����������
				return;
			}
		}

		this.popOBJ.style.visibility = "hidden"; // ���X�|�b�v�A�b�v��\��
		// clearTimeout(this.hideTimerID); // �^�C�}�[������
		gResPopCtl.rmResPopUp(this.divID);
	}

	return this;
}
