/* vim: set fileencoding=cp932 ai noet ts=4 sw=4 sts=4: */
/* mi: charset=Shift_JIS */

/* p2 - AA�␳JavaScript�t�@�C�� */

// HTML�\�[�X�̃N���[���A�b�v�p���K�\��
var amhtre = new Array(7);
var amhtrp = new Array(7);
amhtre[0] = /<br( .*?)?>/ig;
amhtrp[0] = "\n";
amhtre[1] = /<.*?>/g;
amhtrp[1] = "";
amhtre[2] = /\s+$/g;
amhtrp[2] = "";
amhtre[3] = /&gt;/g;
amhtrp[3] = ">";
amhtre[4] = /&lt;/g;
amhtrp[4] = "<";
amhtre[5] = /&quot;/g;
amhtrp[5] = '"';
amhtre[6] = /&amp;/g;
amhtrp[6] = "&";

// AA �ɂ悭�g����p�f�B���O��
// Latin-1,�S�p�X�y�[�X�Ƌ�Ǔ_,�Ђ炪��,�J�^�J�i,���p�E�S�p�` �ȊO�̓���������3�A������p�^�[��
/* Firefox �ł͊��Ғʂ�ɓ��삷�邪�ASafari �͑S�p���������܂������Ȃ����ۂ�... orz */
var amaare = new Array(2);
amaare[0] = /\u3000{4}|(\x20\u3000){2}/;
amaare[1] = /([^\x00-\x7F\u2010-\u203B\u3000-\u3002\u3040-\u309F\u30A0-\u30FF\uFF00-\uFFEF])\1\1/;
// Unicode Note: ��ʋ�Ǔ_ = \u2000-\u206F, CJK�̋L������ы�Ǔ_ = \u3000-\u303F, CJK�������� = \u4E00-\u9FFF

// activeMona -- AA��������
function detectAA(blockId)
{
//	var amTargetObj = document.getElementById(blockId);	
	var amTargetObj = getElementsByClass('div',blockId);
	if (!amTargetObj) {
		return false;
	}
	if (blockId.match(/q/)) {
		var origId = blockId.replace(/q/,'');
		var orig = (document.all) ?  document.all[origId]
			: ((document.getElementById) ? document.getElementById(origId)
					: null);
		if (orig) {
			amTargetObj.unshift(orig);
		}
	}
	var amTargetSrc = amTargetObj[0].innerHTML.replace(amhtre[0], amhtrp[0]).replace(amhtre[1], amhtrp[1]).replace(amhtre[2], amhtrp[2]).replace(amhtre[3], amhtrp[3]).replace(amhtre[4], amhtrp[4]).replace(amhtre[5], amhtrp[5]).replace(amhtre[6], amhtrp[6]);
	// ���s��3�ȏ゠��AAA�p�^�[���Ƀ}�b�`������^��Ԃ�
	if (amTargetSrc.split("\n").length > 3 && (amTargetSrc.search(amaare[0]) != -1 || amTargetSrc.search(amaare[1]) != -1)) {
		//window.alert(amTargetSrc);
		return true;
	}
	return false;
}

// activeMona -- ���i�[�t�H���g�ɐ؂�ւ��A�s�̍������k�߂�
function activeMona(blockId)
{
//	blockId+="|"+blockId.replace(/q?m/,'qm');
	var amTargetObj = getElementsByClass('div',blockId);

	if (!amTargetObj) {
		return;
	}
	
	var aa=(amTargetObj[0].className.search(/\bActiveMona\b/) != -1);
	var pre=(amTargetObj[0].className.search(/\bpre\b/) != -1);
	
	for (i=0;i<amTargetObj.length;i++){
		amTargetObj[i].className = amTargetObj[i].className.replace(/ ?(ActiveMona|pre)/, '');
		if (!aa && !pre) {	// aa���[�h�ł�pre���[�h�ł��Ȃ����aa���[�h��
			amTargetObj[i].className += ' ActiveMona';
		}
/*		else if (aa) {	// aa���[�h����pre���[�h��
			amTargetObj[i].className += ' pre';
		}*/
	}
}

// �N���X���ŗv�f��T��
function getElementsByClass(tag,class){
	el=document.getElementsByTagName(tag);
	re=new RegExp('\\b'+class+'\\b');
	matched=new Array();
	for (i=0;i<el.length;i++){
		if(el[i].className.match(re)){
			matched.push(el[i]);
		}
	}

	return matched;
}

// activeMonaForm -- �A�N�e�B�u���i�[ on �t�H�[��
function activeMonaForm(size)
{
	var message, mail;
	if (size == "") {
		return;
	}
	if (dpreview_ok) {
		var dp = document.getElementById("dpreview");
		if (dp) {
			if (dp.style.display == "none") {
				DPInit();
				dp.style.display = "block";
			}
			activeMona("dp_msg", size);
			return;
		} else {
			message = document.getElementById("MESSAGE");
			mail = document.getElementById("mail");
		}
	} else {
		message = document.getElementById("MESSAGE");
		mail = document.getElementById("mail");
	}
	if (!message || !mail) {
		return;
	}
	if (size == "normal") {
		message.style.fontFamily = mail.style.fontFamily;
		message.style.fontSize = mail.style.fontSize;
	} else {
		message.style.fontFamily = am_aa_fontFamily;
		message.style.fontSize = size;
	}
}
