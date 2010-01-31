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

blockOpenedRes=new Array();

// ���X�|�b�v�A�b�v���Q�ƃ��X�̃u���b�N�\�����́A������Ăяo�������X�ɑ΂���A���J�[���C������
resCallerDecorate=true;
anchorSwitch=new Object();

function getElement(id) {
//	// alert(id);
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
/*
		// ���X���X�g�̃N���b�N���ǂ���
		var isdescend = function (check) {
			if (!check) return false;
			var test = target;
			do {
				if (test == check) return true;
				test = test.parentNode;
			} while (test && test != res);
		};
		if ( !isdescend(_findChildByClassName(res, 'reslist')) &&
			!isdescend(_findChildByClassName(res, 'v_reslist')))
*/
			return;
	}

	var anchors = _findAnchorComment(res);
	if (anchors == null) return;

	if (_findChildByClassName(resblock, 'resblock_inner') !== null &&
			evt.type != 'dblclick') {
		if (mark) resetReaded(res, anchors);
		removeRes(res, button);
		changeFontOfLink(appendAnchorClassCascade(evt, res.className, anchors,false),false);
//		var children=anchors.split("/");
	} else {
		changeFontOfLink(appendAnchorClassCascade(evt, res.className, anchors,true),true);
		insertRes(evt, res, anchors, mark);
		blockOpenedRes=new Array();
	}
}
function changeFontOfLink(anchors,sw) {
	anchors=anchors.unique();
	if(anchors.length) {
		var append='anchorToCaller';
		// �N���X���ŗv�f��T��
		var el=document.getElementsByTagName('a');
		var re=new RegExp('\\b('+anchors.join('|')+')\\b');
			
		// ���������Ƀ}�b�`������A�N���X�ǉ��^�폜
		for (i=0;i<el.length;i++){
			if(el[i].className.match(re)){
				if (sw) {
					el[i].className+=' '+append;
				} else {
					el[i].className=el[i].className.split(' ').remove(append).join(' ');
				}
			}
		}
	}
}
function appendAnchorClassCascade(evt,res,anchors,sw) {
	// �J�������X���w���A���J�[�����ǂɂ���
	var toAnchor=new Array();
	if (resCallerDecorate) {
		if (anchors) {
			var children=anchors.split("/");

			for (var i=0;i<children.length;i++) {
				toAnchor.push('T'+children[i]);
				anchorSwitch[children[i]] = sw

				var importElement=getElementForCopy(""+children[i]);
				var anchor = _findAnchorComment(importElement);
			
				if (sw == false || evt.type == 'dblclick') {
					toAnchor=toAnchor.concat(appendAnchorClassCascade(evt,children[i],anchor,sw));
				}
			}
		}
		var resnum=res.match(/(?:t\d+)?r\d+/);
		resnum=resnum[0].replace(/r/,"qr");
		if (anchorSwitch[resnum] != sw) {
			toAnchor.push('T'+resnum);
			anchorSwitch[resnum] = sw;
		}
	}
	return toAnchor;
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
	var reslistP = _findChildByClassName(res, 'reslist');
//	if (reslistP) reslistP.style.display = 'none';
	
	var children=anchors.split("/");
	var resblock_inner = document.createElement('div');
	var count=0;

	for (var i=children.length-1;i>=0;i--) {
		var importId=children[i];

		if (blockOpenedRes[importId]) {continue;}
		var importElement=getElementForCopy(""+importId);

		//�Q�Ɛ惌�X�����R�s�[
		var container=document.createElement('blockquote');
		container.innerHTML=importElement.innerHTML.replace(/id=\"[^\"]+\"/g,"");
//		container.innerHTML=container.innerHTML.replace(/(class="[^"]*\sreadmessage)"/,"$1 more\"");
		container.className='folding_container '+importId.replace(/qr/,"r");

		// �I���W�i���̃��X������Ό����ڕύX
		if (mark) (function(origId) {
				for (var oidx=0;oidx<origId.length;oidx++) {
					var orig = (document.all) ?  document.all[origId[oidx]]
					: ((document.getElementById) ? document.getElementById(origId[oidx])
					   : null);
					if (orig) {
						orig.className+=' readmessage';
					}
				}
			   })(new Array(
			   				importId.replace(/qr/,'r'),
			   				// importId.replace(/qr/,'m'),
			   				importId.replace(/qr/,'qm'),
			   				res.id.replace(/r/,'qm')
			   			));	
		
		var anchor = _findAnchorComment(importElement);
		if (anchor) {
			container.onclick = function (evt) {
				toggleResBlk(evt, this, mark);
			};
			var c_resblock=document.createElement('div');
			c_resblock.className = 'resblock';
			if (button)
				c_resblock.appendChild(button.cloneNode(false));

			var reslistC = _findChildByClassName(container, 'reslist');
//			if (reslistC) {
				container.insertBefore(c_resblock, reslistC);
//			} else {
//				container.appendChild(c_resblock);
//			}

			// �_�u���N���b�N�Ȃ�J�X�P�[�h
			if (evt.type == 'dblclick') {
				insertRes(evt, container, anchor, mark);
			}
		}
		appendChildBackword(resblock_inner,container);

		if (reslistP) {
			var linkstr=_findChildByClassName(reslistP, importId);
			if (linkstr) {
			linkstr.innerHTML=linkstr.innerHTML.replace(/(�y.+�z)/,"<!--$1-->");
			linkstr.style.display = 'none';
			}
		}
		
		blockOpenedRes[importId]=true;
		count++;
	}
	if (count) {
		resblock_inner.className='resblock_inner';
		resblock.appendChild(resblock_inner);
	}

	if (button) button.src=button.src.replace(/plus/,'minus');
}

function appendChildBackword(parent,child) {
		if (parent.childNodes.length) {
			parent.insertBefore(child,parent.firstChild);
		} else {
			parent.appendChild(child);
		}
}

function removeRes(res, button) {

	button.src=button.src.replace(/minus/,'plus');
	var resblock_inner = _findChildByClassName(
			button.parentNode, 'resblock_inner');
	if (resblock_inner) button.parentNode.removeChild(resblock_inner);

	// reslist������Ε\��
	var reslistP = _findChildByClassName(res, 'reslist');
//	if (reslistP) reslistP.style.display = 'block';
	if (reslistP) {
		for (var i=0;i<reslistP.childNodes.length;i++) {
			reslistP.childNodes[i].innerHTML=reslistP.childNodes[i].innerHTML.replace(/<!\-\-(.+)\-\->/,"$1");
			reslistP.childNodes[i].style.display = 'block';
		}
	}
}

function resetReaded(res, anchors,flag) {
	var resblock = _findChildByClassName(res, 'resblock');
	if (resblock == null) return;
	var resblock_inner = _findChildByClassName(resblock, 'resblock_inner');
	if (resblock_inner == null) return;

	var children=anchors.split("/");
	for (var i=0;i<resblock_inner.childNodes.length;i++) {
		children=children.concat(
					resetReaded(
								resblock_inner.childNodes[i],
								_findAnchorComment(resblock_inner.childNodes[i]),
								true
								)
					 );
	}
	if (flag) return children;
	

	var origId=new Array();
	for (var i=0;i<children.length;i++) {
		// �I���W�i���̃��X������Ό����ڕύX

		if (children[i]) {
			origId.push(
						children[i].replace(/qr/,'r'),
//						children[i].replace(/qr/,'m'),
						children[i].replace(/qr/,'qm')
					);
		}
	}
	if(res.id) {
		origId.push(res.id.replace(/r/,'qm') );
	}

	
	// �N���X���ŗv�f��T��
	var el=document.getElementsByTagName('div');
	var re=new RegExp('\\b('+origId.join('|')+')\\b');
	for (i=0;i<el.length;i++){
		if(el[i].className.match(re)){
			var orig=el[i];
//			console.log(orig);
				if (orig) {
					orig.className=orig.className.split(' ').remove('readmessage').join(' ');
				}
		}
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
		if (!p.childNodes[i].className) {continue;}
		if (p.childNodes[i].className.split(' ').find(kls)) {
			return p.childNodes[i];
		}
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
function showResPopUp(divID, ev,res) {
	if (divID.indexOf("-") != -1) { return; } // �A�� (>>1-100) �͔�Ή��Ȃ̂Ŕ�����

	var aResPopUp = gResPopCtl.getResPopUp(divID);
	if (aResPopUp) {
		if (aResPopUp.hideTimerID) { clearTimeout(aResPopUp.hideTimerID); } // ��\���^�C�}�[������
	} else {
		// doShowResPopUp(divID, ev);

		x = getPageX(ev);
		y = getPageY(ev);

		aShowTimer = new Object();
		var anchorClass= res.className ? res.className : '';

		aShowTimer.timerID = setTimeout("doShowResPopUp('" + divID +"','" + anchorClass +  "')", delayShowSec); // ��莞�Ԃ�����\������

		aShowTimer.x = x;
		aShowTimer.y = y;

		gShowTimerIds[divID] = aShowTimer;
		//alert(gShowTimerIds[divID].timerID);
	}

}

/**
 * ���X�|�b�v�A�b�v��\������
 */
function doShowResPopUp(divID,resClass) {
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

	(function (divID) {	// �|�b�v�A�b�v�̌�������΃n�C���C�g
		if (document.all) { // IE�p
			var orig = document.all[divID.replace(/qr/,'r')];
		} else if (document.getElementById) { // DOM�Ή��p�iMozilla�j
			var orig = document.getElementById(divID.replace(/qr/,'r'));
		}

		 if (orig) {
		 	orig.className+=' highlight';
		}
	})(divID);
}

/**
 * ���X�|�b�v�A�b�v���\���^�C�}�[����
 *
 * ���p���X�Ԃ��� onMouseout �ŌĂяo�����
 */
function hideResPopUp(divID,res) {
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

	(function (divID) {	// �|�b�v�A�b�v���̃n�C���C�g��߂�
		if (document.all) { // IE�p
			 var orig = document.all[divID.replace(/qr/,'r')];
		} else if (document.getElementById) { // DOM�Ή��p�iMozilla�j
			 var orig = document.getElementById(divID.replace(/qr/,'r'));
		}
		 if (orig) {
		 	orig.className=orig.className.split(' ').remove('highlight').join(' ');
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
	this.linkclass=new Array();

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
		} else if (this.divID.indexOf('matome_cache_meta') == 0) {
			x_adjust += 10;
			y_adjust += 10;
		}
		if (this.popOBJ.style.visibility != "visible") {
			this.popOBJ.style.zIndex = this.zNum;
			//x = getPageX(ev); // ���݂̃}�E�X�ʒu��X���W
			//y = getPageX(ev); // ���݂̃}�E�X�ʒu��Y���W
			this.popOBJ.style.left = x + x_adjust + "px"; //�|�b�v�A�b�v�ʒu
			this.popOBJ.style.top = y + y_adjust + "px";
			//alert(window.pageYOffset);
			//alert(this.popOBJ.offsetTop);

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

// Array�N���X�Ƀ��\�b�h�ǉ�
Array.prototype.remove= function(el){
	for (var j=this.length-1;j>=0;j--) {
		if (this[j] == el) {
			this.splice(j, 1);
			break;
		}
	}
	return this;
};

Array.prototype.find= function(el){
	for (var j=0;j<this.length;j++) {
		if (this[j] == el) {
			return true;
		}
	}
	return false;
};

Array.prototype.unique = function(){
   var i = this.length;
	var hashary=new Object();
	var newarray=new Array();
   while(i){
//   		console.log(i);
   		if (!hashary[this[--i]]) {
   			hashary[this[i]]=0;
//			console.log("unshift "+this[i]);
			newarray.unshift(this[i]);
		}
		hashary[this[i]]++;
   }
   return newarray;
};