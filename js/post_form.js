// textarea�̍��������C�u���߂���
function adjustTextareaRows(obj, org, plus) {
	var brlen = null;
	if (obj.wrap) {
		if (obj.wrap == 'virtual' || obj.wrap == 'soft') {
			brlen = obj.cols;
		}
	}
	var aLen = countLines(obj.value, brlen);
	var aRows = aLen + plus;
	var move = 0;
	var scroll = 14;
	if (org) {
		if (Math.max(aRows, obj.rows) > org) {
			move = Math.abs((aRows - obj.rows) * scroll);
			if (move) {
				obj.rows = Math.max(org, aRows);
				window.scrollBy(0, move);
			}
		}
		/*
		if (aRows > org + plus) {
			if (obj.rows < aRows) {
				move = (aRows - obj.rows) * scroll;
			} else if (obj.rows > aRows) {
				move = (aRows - obj.rows) * -scroll;
			}
			if (move != 0) {
				if (move < 0) {
					window.scrollBy(0, move);
				}
				obj.rows = aRows;
				if (move > 0) {
					window.scrollBy(0, move);
				}
			}
		}
		*/
	} else if (obj.rows < aRows) {
		move = (aRows - obj.rows) * scroll;
		obj.rows = aRows;
		window.scrollBy(0, move);
	}
}

/**
 * \n �����s�Ƃ��čs���𐔂���
 *
 * @param integer brlen ���s���镶�����B���w��Ȃ當�����ŉ��s���Ȃ�
 */
function countLines(str, brlen) {
	var lines = str.split("\n");
	var count = lines.length;
	var aLen = 0;
	for (var i = 0; i < lines.length; i++) {
		aLen = jstrlen(lines[i]);
		if (brlen) {
			var adjust =  1.15; // �P��P�ʂ̐܂�Ԃ��ɑΉ����Ă��Ȃ��̂ŃA�o�E�g����
			if ((aLen * adjust) > brlen) {
				count = count + Math.floor((aLen * adjust) / brlen);
			}
		}
	}
	return count;
}

// ��������o�C�g���Ő�����
function jstrlen(str) {
	var len = 0;
	str = escape(str);
	for (var i = 0; i < str.length; i++, len++) {
		if (str.charAt(i) == "%") {
			if (str.charAt(++i) == "u") {
				i += 3;
				len++;
			}
			i++;
		}
	}
	return len;
}

// (�Ώۂ�disable�łȂ����) �t�H�[�J�X�����킹��
function setFocus(ID){
	var obj;
	if (obj = document.getElementById(ID)) {
		if (obj.disabled != true) {
			obj.focus();
		}
	}
}

// sage�`�F�b�N�ɍ��킹�āA���[�����̓��e������������
function mailSage(){
	var mailran, cbsage;
	if (cbsage = document.getElementById('sage')) {
		if (mailran = document.getElementById('mail')) {
			if (cbsage.checked == true) {
				mailran.value = "sage";
			} else {
				if (mailran.value == "sage") {
					mailran.value = "";
				}
			}
		}
	}
}

// ���[�����̓��e�ɉ����āAsage�`�F�b�N��ON OFF����
function checkSage(){
	var mailran, cbsage;
	if (mailran = document.getElementById('mail')) {
		if (cbsage = document.getElementById('sage')) {
			if (mailran.value == "sage") {
				cbsage.checked = true;
			} else {
				cbsage.checked = false;
			}
		}
	}
}

/*
// �����œǂݍ��ނ��Ƃɂ����̂ŁA�g��Ȃ�

// �O��̏������ݓ��e�𕜋A����
function loadLastPosted(from, mail, message){
	if (fromran = document.getElementById('FROM')) {
		fromran.value = from;
	}
	if (mailran = document.getElementById('mail')) {
		mailran.value = mail;
	}
	if (messageran = document.getElementById('MESSAGE')) {
		messageran.value = message;
	}
	checkSage();
}
*/

// �������݃{�^���̗L���E������؂�ւ���
function switchBlockSubmit(onoff) {
	var kakiko_submit = document.getElementById('kakiko_submit');
	if (kakiko_submit) {
		kakiko_submit.disabled = onoff;
	}
	var submit_beres = document.getElementById('submit_beres');
	if (submit_beres) {
		submit_beres.disabled = onoff;
	}
}

// ��^����}������
function inputConstant(obj) {
	var msg = document.getElementById('MESSAGE');
	msg.value = msg.value + obj.options[obj.selectedIndex].value;
	msg.focus();
	obj.options[0].selected = true;
}

// �������ݓ��e�����؂���
function validateAll(doValidateMsg, doValidateSage) {
	var block_submit = document.getElementById('block_submit');
	if (block_submit && block_submit.checked) {
		alert('�������݃u���b�N��');
		return false;
	}
	if (doValidateMsg && !validateMsg()) {
		return false;
	}
	if (doValidateSage && !validateSage()) {
		return false;
	}
	return true;
}

// �{������łȂ������؂���
function validateMsg() {
	if (document.getElementById('MESSAGE').value.length == 0) {
		alert('�{��������܂���B');
		return false;
	}
	return true;
}

// sage�Ă��邩���؂���
function validateSage() {
	if (document.getElementById('mail').value.indexOf('sage') == -1) {
		if (window.confirm('sage�Ă܂����H')) {
			return true;
		} else {
			return false;
		}
	}
	return true;
}
