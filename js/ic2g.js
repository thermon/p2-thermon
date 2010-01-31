/*
 *ImageCache2::Downloader
 */

// {{{ checkSerial()

/*
 * URL �ɘA�Ԏw�肪�����Ă��邩�𔻒肵�A�t�H�[���̊e�v�f�𑀍삷��
 */
function checkSerial(url)
{
	var re = /\[[0-9]+-[0-9]+\]/;
	var chk = document.getElementById('s_chk');

	if (url.indexOf('%s') != -1) {
		chk.checked = true;
		setSerialAvailable(true);

	} else if (re.test(url)){
		chk.checked = true;
		setSerialAvailable(false);

	} else {
		chk.checked = false;
		setSerialAvailable(false);
	}
}

// }}}
// {{{ setSerialAvailable()

/*
 * �A�Ԃ͈̔͂��w�肷��v�f�̗L���E������؂�ւ���
 */
function setSerialAvailable(onoff)
{
	var from = document.getElementById('s_from');
	var to   = document.getElementById('s_to');
	var pad  = document.getElementById('s_pad');

	if (onoff == true) {
		from.disabled = false;
		to.disabled   = false;
		pad.disabled  = false;
		if (from.value == 'from') {
			from.value = '';
		}
		if (to.value == 'to') {
			to.value = '';
		}
		from.focus();
	} else {
		from.disabled = true;
		to.disabled   = true;
		pad.disabled  = true;
	}
}

// }}}

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
