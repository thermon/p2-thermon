/* p2 - �|�b�v�A�b�v�E�B���h�E�A�N���[�Y�^�C�}�[��JavaScript */

var delay = 5 // �y�[�W���ς��܂ł̎��ԁi�b�P�ʁj

var _swForm=0;
var _swElem=0;
var _run = 1;	// 1:run 0:stop
var _start;
var _now;

var ibtimer;

function startTimer(obj){
	ibtimer=obj;
	_start = new Date();
	closeTimer();
}
		
function closeTimer() {		// �X�N���v�g�̖{��
	_now = new Date();
	if (_run == 1) {
		nowtime=delay - ( _now.getTime() - _start.getTime() ) / 1000;
		nowtime=Math.ceil(nowtime);
		if(nowtime < 0){
			window.close();
		}else if(nowtime < delay-1){
			//document.forms[_swForm].elements[_swElem].value = "         " + nowtime + "         ";
			ibtimer.value = "         " + nowtime + "         ";
		}
		setTimeout("closeTimer()", 100);
	}
}

function stopTimer(obj) {  // �ݒ�
	if (_run == 1) {		// �^�C�}�[���Ȃ�
		_run = 0;
		obj.value = "�E�B���h�E�����";
		//closeTimer();
	} else if (_run == 0) {	// �X�g�b�v���Ă��Ȃ�
		window.close();
	}
}
