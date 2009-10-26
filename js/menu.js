/* vim: set fileencoding=cp932 ai noet ts=4 sw=4 sts=4: */
/* mi: charset=Shift_JIS */

/* p2 - ���j���[�𑀍삷�邽�߂�JavaScript */

/**
 * ���C�ɔɂ���
 */
function setFavIta(php, itaj, host, bbs, itaj_en, noconfirm)
{
	if (noconfirm || window.confirm('"' + itaj + '" �����C�ɔɓo�^���܂����H')) {
		var setURL = php + '?host=' + host + '&bbs=' +bbs + '&itaj_en=' + itaj_en+ '&setfavita=1';
		this.window.location.href = setURL;
	}
	return false;
}

/**
 * ���C�ɔ���O��
 */
function unSetFavIta(php, itaj, host, bbs, noconfirm)
{
	if (noconfirm || window.confirm('"' + itaj + '" �����C�ɔ���O���܂����H')) {
		// �v���_�E�����j���[�ŃZ�b�g��؂�ւ����Ƃ��̓��ʂȏ���
		if (php.indexOf('menu_async.php') != -1) {
			if (this.window.location.href.indexOf('menu_side.php') != -1) {
				php = php.replace(/menu_async\.php/, 'menu_side.php');
			} else {
				php = php.replace(/menu_async\.php/, 'menu.php');
			}
		}
		var unsetURL = php + '?host=' + host + '&bbs=' +bbs + '&setfavita=0';
		this.window.location.href = unsetURL;
	}
	return false;
}

/**
 * ���C�ɔERSS��؂�ւ���
 */
function replaceMenuItem(itemId, qKey, qValue)
{
	var uri = 'menu_async.php?' + qKey + '=' + qValue;

	var req = getXmlHttp();
	if (!req) {
		alert('XMLHttp not available.');
		return;
	}

	var receiver = document.getElementById(itemId);
	if (!receiver) {
		alert('replaceMenuItem() Error: A target element not exists.');
		return;
	}
	receiver.innerHTML = 'Now Loading...';

	req.open('get', uri, false);
	req.send(null);

	if (req.readyState == 4) {
		if (req.status == 200) {
			receiver.innerHTML = req.responseText;
		} else {
			receiver.innerHTML = '<em>HTTP Error:<br />' + req.status + ' ' + req.statusText + '</em>';
		}
	}
}

/**
 * ���C�ɃX�����J��
 */
function openFavList(subject_php, set_num, tgt)
{
	var url = subject_php + '?spmode=fav&norefresh=1&m_favlist_set=' + set_num;
	if (tgt) {
		tgt.location.href = url;
	} else {
		window.open(url, '', '');
	}
}

/**
 * �X�L����؂�ւ���
 */
function changeSkin(skinName)
{
	var uri = 'menu_async.php?m_skin_set=' + skinName;
	var req = getXmlHttp();
	var res;

	if (!req) {
		alert('XMLHttp not available.');
		return;
	}

	req.open('get', uri, false);
	req.send(null);

	try {
		if (req.readyState == 4) {
			if (req.status == 200) {
				res = req.responseText;
				if (!res) {
					window.alert('changeSkin: Unknown Error - empty response');
				} else if (res != skinName) {
					window.alert(res);
				} else {
					var css1 = 'css.php?css=style&skin=' + skinName;
					var css2 = 'css.php?css=menu&skin=' + skinName;
					document.getElementById('basicStyle').href = css1;
					document.getElementById('menuStyle').href = css2;
					if (window.top.subject) {
						changeWindowStyle(window.top.subject, skinName);
					}
					if (window.top.read) {
						changeWindowStyle(window.top.read, skinName);
					}
				}
			} else {
				window.alert('HTTP Error: ' + req.status + ' ' + req.statusText);
			}
		}
	} catch (e) {
		window.alert('changeSkin: Unknown Error - ' + e.toString());
	}
}

/**
 * �E�C���h�E/�t���[���̃X�^�C���V�[�g��ύX����
 */
function changeWindowStyle(winObj, skinName)
{
	if (!document.getElementsByTagName) {
		return;
	}
	var links = winObj.document.getElementsByTagName('link');
	var l = links.length;
	if (l == 0) {
		return;
	}
	var currentStyle;
	var newStyle;
	var i;
	for (i = 0; i < l; i++) {
		if (links[i].rel == 'stylesheet') {
			currentStyle = links[i].href.toString();
			if (currentStyle.indexOf('css.php?') == -1) {
				continue;
			}
			if (currentStyle.indexOf('&skin=') == -1) {
				newStyle = currentStyle + '&skin=' + skinName;
			} else {
				newStyle = currentStyle.replace(/(&skin=).+/, '$1') + skinName;
			}
			links[i].href = newStyle;
		}
	}
}
