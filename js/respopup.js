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

importedRes=new Array();	// ���X���W�J���ꂽ�t���O�̔z��
resPosition=new Array();	// �W�J���ꂽ���X�̐e���X�Ƃ�������̑���y���W�̔z��

timerId=new Array();;

// ���X�|�b�v�A�b�v���Q�ƃ��X�̃u���b�N�\�����́A������Ăяo�������X�ɑ΂���A���J�[���C������
resCallerDecorate=true;
importedClass="imported";

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

function toggleResBlk(evt, res) {
	if (!getElement('footer')) return null;	// html�\�[�X�����ׂēǂݍ��܂�Ă��Ȃ��ꍇ�͍쓮�����Ȃ�
//	HTMLAnchorList=document.getElementsByName('linkfrom');
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
	var button = _findChildByClassName(res, 'buttonblock');
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
		if (result_pair=removeRes(res, button)) {
//			console.log(result_pair);
			var div_class=result_pair[0].unique();
			if (div_class) {
				// �N���X���ŗv�f��T��
				var el=document.getElementsByTagName('div');
				var re=new RegExp('\\b('+div_class.join('|')+')\\b');
				
				i=el.length;
				while(i--) {
					if(el[i].className.match(re)){
						el[i].className=el[i].className.remove(importedClass,' ');
					}
				}
			}
		
			result1=result_pair[1].unique();
//			console.log("close result1="+result1);

			var a_class=new Array();
			var idx=result1.length;
			while(idx--) {
				if (importedRes[result1[idx]]==0) {a_class.push("T"+result1[idx]);}
			}
//			console.log("close result2="+a_class);
			if (a_class.length) {
				// �N���X���ŗv�f��T��
				var el=document.getElementsByTagName('a');
				var re=new RegExp('\\b('+a_class.join('|')+')\\b');
			
				// ���������Ƀ}�b�`������A�N���X�ǉ��^�폜
				i=el.length;
				while(i--) {
					if(el[i].className.match(re)){
						el[i].className=el[i].className.remove(importedClass,' ');
					}
				}
			}
		}
	} else {
		if (result_pair=insertRes(evt, res, anchors)) {
			(function(origId) 
			{
				var oidx=origId.length;
				while(oidx--) {

					var orig = (document.all) ?  document.all[origId[oidx]]
					: ((document.getElementById) ? document.getElementById(origId[oidx])
					   : null);
					if (orig) {
						orig.className+=' '+importedClass;
					}
				}
			}
		 	)(result_pair[0].unique());
		
			result1=result_pair[1].unique();
//			console.log("open result1="+result1);

			var a_class=new Array();
			var idx=result1.length;
			while(idx--) {
				if (importedRes[result1[idx]]==1) {a_class.push("T"+result1[idx]);}
			}
//			console.log("open result2="+a_class);
			if (a_class.length) {
				// �N���X���ŗv�f��T��
				var el=document.getElementsByTagName('a');
				var re=new RegExp('\\b('+a_class.join('|')+')\\b');
			
				// ���������Ƀ}�b�`������A�N���X�ǉ��^�폜
				i=el.length;
				while(i--) {
					if(el[i].className.match(re)){
						el[i].className+=' '+importedClass;
					}
				}
			}
		}
	}
}

function resIdCompare(a,b){
	var ax=a.match(/\d+$/);
	var bx=b.match(/\d+$/);
	return ax-bx;
}

function insertRes(evt, res, anchors) {
	var cascade_flag=false;	// �J�X�P�[�h���A���ʓW�J��
//	console.log('insertRes');
//	console.log(res);
	//		console.log(anchors);
	var markRead=new Array();		// �C���|�[�g���ꂽ���X�̔ԍ����i�[
	var openedAnchors=new Array();	// �J�����Ƃ��ł������X�̃A���J�[���i�[
	var resblock = _findChildByClassName(res, 'resblock');
	if (!resblock) return new Array();
	var button = _findChildByClassName(res, 'buttonblock');
	var resblock_inner = _findChildByClassName(resblock, 'resblock_inner');
	/*
	// ���ɊJ���Ă����ꍇ
	if (resblock_inner) {
		if (evt.type != 'dblclick') return new Array();
		// �_�u���N���b�N�Ȃ�J�X�P�[�h
		var r=(function (nodes) {
			var result=new Array();
			for  (var i=0;i<nodes.length;i++) {
				if (nodes[i].className != 'folding_container') continue;
				var anchor = _findAnchorComment(nodes[i]);
				if (anchor != null) {
					result_pair=insertRes(evt, nodes[i], _findAnchorComment(nodes[i]));
					if (result_pair.length) {
						markRead=markRead.concat(result_pair[0]);
						openedAnchors=openedAnchors.concat(result_pair[1]);
					}
				}
				
			}
			console.log("result="+result);
			return result;
		   })(resblock_inner.childNodes);
		console.log("r="+r);
		return;
	}
	*/

	// reslist������Δ�\����
	var reslistP = _findChildByClassName(res, 'reslist');
	//	if (reslistP) reslistP.style.display = 'none';
	
	var anchorsArray=anchors.split("/");
	var resblock_inner = document.createElement('div');
	var count=0;

	// �_�u���N���b�N�Ȃ畽�ʓW�J
	var allAnchors=new Array();
	if (evt.type == 'dblclick' && !cascade_flag) {
		while (anchorsArray.length) {
			var resid=anchorsArray.shift();
//		console.log(resid);
			var resElement=getElementForCopy(""+resid);
			var resAnchor = _findAnchorComment(resElement);
			if (resAnchor) {
				anchorsArray=anchorsArray.concat(resAnchor.split("/"));
			}
			allAnchors.push(resid);
		}
		allAnchors=allAnchors.unique().sort(resIdCompare);
	} else {
		allAnchors=anchorsArray;
	}
//	console.log(allAnchors);
	
	var i=allAnchors.length;
	var children=new Array();

	while(i--) {	// �d���W�J���֎~
		if (!importedRes[allAnchors[i]] || !cascade_flag) {
			children.unshift(allAnchors[i]);
			importedRes[allAnchors[i]]=1;
		}
	}

	for (i=0;i<children.length;i++) {
		var importId=children[i];

//		var importElement=getElementForCopy(""+importId);

		//�Q�Ɛ惌�X�����R�s�[
		var container=document.createElement('blockquote');
		var id_rx=importId.replace(/qr/,'rx');
		var HTMLAnchorTo=document.getElementsByName(id_rx);

		if (!HTMLAnchorTo.length){
			var importedAnchor=createNamedElement('a',id_rx);
			container.appendChild(importedAnchor);
		}

		var importElement2=getElement(importId);
		for (var c=0;c<importElement2.childNodes.length;c++) {
			container.appendChild(importElement2.childNodes[c].cloneNode(true));
		}

		var msgBlock = _findChildByClassName(container, 'message');
		msgBlock.removeAttribute('id');
//		container.innerHTML=importElement.innerHTML.replace(/id=\"[^\"]+\"/g,"");
		container.className='folding_container ';
		if (evt.type == 'dblclick' && !cascade_flag) {
			container.className+='single_layer ';
		}
		container.className+=importId.replace(/qr/,"r");

		var anchor = _findAnchorComment(importElement2);
		if (anchor) {
			container.onclick = function (evt) {
				toggleResBlk(evt, this);
			};
/*			var c_buttonblock=document.createElement('div');
			c_buttonblock.className = 'buttonblock';
			if (button)
				c_buttonblock.appendChild(button.cloneNode(false));*/

			var reslistC = _findChildByClassName(container, 'reslist');
			container.insertBefore(button.cloneNode(false), reslistC);
			
			var c_resblock=document.createElement('div');
			c_resblock.className = 'resblock';
			container.appendChild(c_resblock);
			
			// �_�u���N���b�N�Ȃ�J�X�P�[�h
			if (evt.type == 'dblclick' && cascade_flag) {
				result_pair=insertRes(evt, container, anchor);
				if (result_pair.length) {
					markRead=markRead.concat(result_pair[0]);
					openedAnchors=openedAnchors.concat(result_pair[1]);
				}
			}
		}
		resblock_inner.appendChild(container);	// �W�J����
	
		if (reslistP) {
			var linkstr=_findChildByClassName(reslistP, importId);
			if (linkstr) {
//				linkstr.innerHTML=linkstr.innerHTML.replace(/(�y.+�z)/,"<!--$1-->");
				linkstr.style.display = 'none';
			}
		}

		openedAnchors.push(importId);
		markRead.push(importId.replace(/qr/,'qm'));	// �q���X�̃|�b�v�A�b�v�����Ǐ���
		var anchor = _findAnchorComment(importElement2);
		//		if (!anchor) {
		markRead.push(importId.replace(/qr/,'r'));	// �������Ȃ��Ƃ��͎q���X�̖{�̂����Ǐ���
		//		}
		//		console.log('markRead='+markRead);

		//		console.log("open "+importId+":"+importedRes[importId]);
		count++;
				var parent=res.id;
		if (!parent) {
			var x=res.className.match(/\br\d+\b/);
			parent=x[0];
		}
		/*
		var y0=getElement(parent).offsetTop;
		var y1=getElement(container).offsetTop;
		console.log("parent y="+y0);
		console.log("child y="+y1);
		resPosition[importId]=new Array(parent,y1-y0);
		*/

	}

	if (count) {
		resblock_inner.className='resblock_inner';
		resblock.appendChild(resblock_inner);
		
		importId=res.id.replace(/r/,"qr");
//		console.log(importId);
		if (importId && !importedRes[importId]) {
			importedRes[importId]=1;
			openedAnchors.push(importId);
			// �I���W�i���̃��X������Ό����ڕύX
			var resClass=res.className.match(/(^| )(r\d+)/);
			if (!res.id) {markRead.push(resClass[2]);}// �e���X�̖{�̂����Ǐ���
			markRead.push(resClass[2].replace(/r/,'qm'));	// �e���X�̃|�b�v�A�b�v�����Ǐ���
		}
	}

	if (button && count) button.src=button.src.replace(/plus/,'minus');
	setTimeout("linkchange('"+children+"')", 100);
//	console.log(children);
//	linkchange(children);
	return new Array(markRead,openedAnchors);
}

function linkchange(children) {
	children=children.split(',');
//	console.log(children);
			var HTMLAnchorList=document.getElementsByName('linkfrom');
	for (i=0;i<children.length;i++) {
			var importId=children[i];
			var id_r=importId.replace(/qr/,'r');
			var id_rx=importId.replace(/qr/,'rx');
		

			var href=location.href.replace(/#.*$/,"#"+id_r);
//		console.log(href);
			for (ix=0;ix<HTMLAnchorList.length;ix++) {
//				console.log(HTMLAnchorList.item(ix).href);
				if (HTMLAnchorList.item(ix).href == href || 
					HTMLAnchorList.item(ix).href == "#"+id_r) {
					HTMLAnchorList.item(ix).href="#"+id_rx;
//				console.log(id_rx);
				}
			}
		}
}
function appendChildBackword(parent,child) {
		if (parent.childNodes.length) {
			parent.insertBefore(child,parent.firstChild);
		} else {
			parent.appendChild(child);
		}
}

function removeRes(res, button) {
//	console.log('removeRes');
	if (button) button.src=button.src.replace(/minus/,'plus');
	var closedAnchors=new Array();
	var markRead=new Array();		// �C���|�[�g���ꂽ���X�̔ԍ����i�[
	var children2=new Array();
	var resblock = _findChildByClassName(res, 'resblock');
	if (resblock) {
		var resblock_inner = _findChildByClassName(resblock, 'resblock_inner');
		if (resblock_inner) 
		{
			for (var c=0;c<resblock_inner.childNodes.length;c++) {
				result=removeRes(resblock_inner.childNodes[c]);
				closedAnchors=closedAnchors.concat(result[1]);
				markRead=markRead.concat(result[0]);
				var id=resblock_inner.childNodes[c].className.match(/(t\d+)?r\d+/);
				var id_r=id[0];
				var id_qr=id_r.replace(/r/,"qr");
				if (--importedRes[id_qr]<=0) {
					importedRes[id_qr]=0;
					closedAnchors.push(id_qr);
					markRead.push(id_qr.replace(/qr/,'qm'));	// �q���X�̃|�b�v�A�b�v�����ǉ���
					var child=_findChildByClassName(resblock_inner, id_r);
					//			if (!_findChildByClassName(child, 'resblock')) {
					markRead.push(id_r);	// �������Ȃ���Ύq���X�̖{�̂����ǉ���
					//			}
				}
			}
			resblock.removeChild(resblock_inner);
			
			var HTMLAnchorList=document.getElementsByName('linkfrom');
			/*�n�C�p�[�����N��URL�����ɖ߂�*/
			for (var c=0;c<resblock_inner.childNodes.length;c++) {
				var id=resblock_inner.childNodes[c].className.match(/(t\d+)?r\d+/);
				var id_r=id[0];
				var id_rx=id_r.replace(/r/,"rx");	
				var HTMLAnchorTo=document.getElementsByName(id_rx);

				if (!HTMLAnchorTo.length){
					var href=location.href.replace(/#.*/,"#"+id_rx);
					for (ix=0;ix<HTMLAnchorList.length;ix++) {
						if (HTMLAnchorList.item(ix).href == href) {
							HTMLAnchorList.item(ix).href="#"+id_r;
						}
					}
				}
			
			}
		}


	}
	
	// reslist������Ε\��
	var reslistP = _findChildByClassName(res, 'reslist');
//	if (reslistP) reslistP.style.display = 'block';
	if (reslistP) {
		for (var i=0;i<reslistP.childNodes.length;i++) {
//			reslistP.childNodes[i].innerHTML=reslistP.childNodes[i].innerHTML.replace(/<!\-\-(.+)\-\->/,"$1");
			reslistP.childNodes[i].style.display = 'block';

		}
	}
	id=res.id.replace(/q?r/,"qr");
	if (--importedRes[id]<=0) {
		importedRes[id]=0;
		closedAnchors.push(id);
		// �I���W�i���̃��X������Ό����ڕύX
		var resClass=res.className.match(/(^| )(r\d+)/);
		if (!res.id) {markRead.unshift(resClass[2]);}// �e���X�̖{�̂����ǉ���
		markRead.unshift(resClass[2].replace(/r/,'qm'));	// �e���X�̃|�b�v�A�b�v�����ǉ���
	}

	return new Array(markRead,closedAnchors);
}

function getElementForCopy(qresID) {
	if (qresID.indexOf("-") != -1) { return null; } // �A�� (>>1-100) �͔�Ή��Ȃ̂Ŕ�����
	
	if (document.all) { // IE�p
		aResPopUp = document.all[qresID];
	} else if (document.getElementById) { // DOM�Ή��p�iMozilla�j
		aResPopUp = document.getElementById(qresID);
	}

	return aResPopUp ? aResPopUp : null;
}

function _findChildByClassName(p, kls) {
	if (p != null) {
		for (var i=0;i<p.childNodes.length;i++) {
			if (!p.childNodes[i].className) {continue;}
			if (p.childNodes[i].className.split(' ').find(kls)) {
				return p.childNodes[i];
			}
		}
	}
	return null;
}

function _findAnchorComment(res) {
	if (res != null) {
		var i=res.childNodes.length;
		while(i--) {
//		for (var i=0;i<res.childNodes.length;i++) {
			if (res.childNodes[i].nodeName.toLowerCase().indexOf('comment') != -1) {
				var nv = res.childNodes[i].nodeValue.replace(/^\s+|\s+$/g, '');
				if (nv.indexOf('backlinks:') == 0) {
					return nv.substr('backlinks:'.length);
				}
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
function showResPopUp(divID, ev,anchor) {
	if (!getElement('footer')) return null;	// html�\�[�X�����ׂēǂݍ��܂�Ă��Ȃ��ꍇ�͍쓮�����Ȃ�
	if (divID.indexOf("-") != -1) { return; } // �A�� (>>1-100) �͔�Ή��Ȃ̂Ŕ�����

	var aResPopUp = gResPopCtl.getResPopUp(divID);
	if (aResPopUp) {
		if (aResPopUp.hideTimerID) { clearTimeout(aResPopUp.hideTimerID); } // ��\���^�C�}�[������
	} else {
		// doShowResPopUp(divID, ev);

		x = getPageX(ev);
		y = getPageY(ev);

		var from='';
		if (anchor) {
			var matched=anchor.className.match(/F((t\d+)?q?r\d+)/);
			if (matched) {from=matched[1].replace(/q?r/,'qr');}
		}
		aShowTimer = new Object();
		aShowTimer.timerID = setTimeout("doShowResPopUp('" + divID + "','"+from+"')", delayShowSec); // ��莞�Ԃ�����\������

		aShowTimer.x = x;
		aShowTimer.y = y;

		gShowTimerIds[divID] = aShowTimer;
		//alert(gShowTimerIds[divID].timerID);
	}

}

/**
 * ���X�|�b�v�A�b�v��\������
 */
function doShowResPopUp(divID,from) {
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
		aResPopUp = gResPopCtl.addResPopUp(divID,from); // �V�����|�b�v�A�b�v��ǉ�
	}

	aResPopUp.showResPopUp(x, y);

	(function (divID) {	// �|�b�v�A�b�v�̌�������΃n�C���C�g
		var orig;
		if (document.all) { // IE�p
			orig = document.all[divID.replace(/qr/,'r')];
		} else if (document.getElementById) { // DOM�Ή��p�iMozilla�j
			orig = document.getElementById(divID.replace(/qr/,'r'));
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
function hideResPopUp(divID,anchor) {
	if (divID.indexOf("-") != -1) { return; } // �A�� (>>1-100) �͔�Ή��Ȃ̂Ŕ�����

	// �\���^�C�}�[������
	if (gShowTimerIds[divID] && gShowTimerIds[divID].timerID) {
		clearTimeout(gShowTimerIds[divID].timerID);
	}

	var aResPopUp = gResPopCtl.getResPopUp(divID);
	if (aResPopUp) {
				var from='';
		if (anchor && anchor.className) {
			var matched=anchor.className.match(/F((t\d+)?q?r\d+)/);
			if (matched) {from=matched[1].replace(/q?r/,'qr');}
		}
		aResPopUp.hideResPopUp(from);
	}
}

/**
 * ���X�|�b�v�A�b�v���\���ɂ���
 */
function doHideResPopUp(divID,from) {
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
		 	orig.className=orig.className.remove('highlight',' ');
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
	ResPopCtl.prototype.addResPopUp = function (divID,from) {
		var aResPopUp = new ResPopUp(divID);
		// gPOPS.push(aResPopUp); Array.push ��IE5.5�������Ή��Ȃ̂ő�֏���
		aResPopUp.from.push(from);
		if (from) {
		var msgblock = _findChildByClassName(aResPopUp.popOBJ, 'message');
		if (msgblock) {
			var anchor=_findChildByClassName(msgblock,'T'+from);
			if (anchor) {
				anchor.className+= " popedFrom";
			}
		}
	}
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
	this.from=new Array();

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
				this.hideTimerID = setTimeout("hideResPopUp('" + this.divID + "','"+ from +"')", delaySec); // ��莞�ԕ\�����������
				return;
			}
		}

		this.popOBJ.style.visibility = "hidden"; // ���X�|�b�v�A�b�v��\��
		// clearTimeout(this.hideTimerID); // �^�C�}�[������
		gResPopCtl.rmResPopUp(this.divID);

		var from=this.from.pop();
		
//		console.log("doHideResPopUp "+from+"=>"+this.divID);
		if (from) {
			var msgblock = _findChildByClassName(this.popOBJ, 'message');
			if (msgblock) {
				var anchor=_findChildByClassName(msgblock,'T'+from);

				if (anchor) {
					anchor.className=anchor.className.remove('popedFrom',' ');
				}
			}
		}

	}

	return this;
}

// Array�N���X�Ƀ��\�b�h�ǉ�
Array.prototype.remove= function(el){
	var j=this.length;
	while(j--) {	
//	for (var j=this.length-1;j>=0;j--) {
		if (this[j] == el) {
			this.splice(j, 1);
			break;
		}
	}
	return this;
};

Array.prototype.find= function(el){
	var j=this.length;
	while(j--) {
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
	while(i--){
		if (!hashary[this[i]] && this[i]!='') {
			hashary[this[i]]=1;
			newarray.unshift(this[i]);
		}
   }
   return newarray;
};

function createNamedElement(type, name) {
   var element = null;
   // Try the IE way; this fails on standards-compliant browsers
   try {
      element = document.createElement('<'+type+' name="'+name+'">');
   } catch (e) {
   }
   if (!element || element.nodeName != type.toUpperCase()) {
      // Non-IE browser; use canonical method to create named element
      element = document.createElement(type);
      element.name = name;
   }
   return element;
}

// String�N���X�Ƀ��\�b�h�ǉ�
String.prototype.remove= function(str,delimiter){
	return this.split(delimiter).remove(str).join(delimiter);
};

function ShowSize() 
{
     var ua = navigator.userAgent;       // ���[�U�[�G�[�W�F���g
     var nWidth, nHeight;                   // �T�C�Y
     var nHit = ua.indexOf("MSIE");     // ���v���������̐擪�����̓Y����
     var bIE = (nHit >=  0);                 // IE ���ǂ���
     var bVer6 = (bIE && ua.substr(nHit+5, 1) == "6");  // �o�[�W������ 6 ���ǂ���
     var bStd = (document.compatMode && document.compatMode=="CSS1Compat");
                                                                           // �W�����[�h���ǂ���
     if (bIE) {
          if (bVer6 && bStd) {
               nWidth = document.documentElement.clientWidth;
               nHeight = document.documentElement.clientHeight;
          } else {
               nWidth = document.body.clientWidth;
               nHeight = document.body.clientHeight;
          }
     } else {
          nWidth = window.innerWidth;
          nHeight = window.innerHeight;
     }

//     alert("�T�C�Y�@�F�@�� " + nWidth + " / ���� " + nHeight);
	return nWidth;
}
width=ShowSize();