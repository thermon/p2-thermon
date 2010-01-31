/*
 * rep2expack - tGrep���j���[�𑀍삷�邽�߂�JavaScript
 */

// {{{ GLOBALS

var tGrep = {};

// }}}
// {{{ execRequest()

/**
 * XmlHttpRequest�����s
 */
tGrep.execRequest = function (uri, menuId) {
	var req = getXmlHttp();
	if (!req) {
		alert('XMLHttp not available.');
		return false;
	}

	var receiver = document.getElementById(menuId);
	if (!receiver) {
		alert('tGrep.execRequest() Error: The target element does not exist.');
		return false;
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

	return false;
};

// }}}
// {{{ appendListInput()

/**
 * ���[�U����̓��͂����X�g�ɒǉ�����
 */
tGrep.appendListInput = function (file, menuId) {
	var query = window.prompt('�L�[���[�h����͂��Ă�������', '');
	if (query !== null && query.length > 0) {
		query = encodeURIComponent(query) + '&_hint=' + encodeURIComponent('����');
		tGrep.appendListItem(file, menuId, query);
		if (parent.frames['subject'] && window.confirm('���̃L�[���[�h�Ō������܂����H')) {
			parent.frames['subject'].location.href = 'tgrepc.php?Q=' + query;
		}
	}
	return false;
};

// }}}
// {{{ appendListItem()

/**
 * ���X�g�ɒǉ�����
 */
tGrep.appendListItem = function (file, menuId, query) {
	var uri = 'tgrepctl.php?file=' + file + '&query=' + query;
	tGrep.execRequest(uri, menuId);
	return false;
};

// }}}
// {{{ removeListItem()

/**
 * ���X�g����폜����
 */
tGrep.removeListItem = function (file, menuId, query) {
	var uri = 'tgrepctl.php?file=' + file + '&query=' + query + '&purge=true';
	tGrep.execRequest(uri, menuId);
	return false;
};

// }}}
// {{{ clearList()

/**
 * ���X�g���N���A����
 */
tGrep.clearList = function (file, menuId) {
	var uri = 'tgrepctl.php?file=' + file + '&clear=all';
	tGrep.execRequest(uri, menuId);
	return false;
};

// }}}
// {{{ updateList()

/**
 * ���X�g���X�V����
 */
tGrep.updateList = function (file, menuId) {
	var uri = 'tgrepctl.php?file=' + file;
	tGrep.execRequest(uri, menuId);
	return false;
};

// }}}
// {{{ �݊��֐�

(function () {
	var f, n, p;
	for (p in tGrep) {
		f = tGrep[p];
		if (typeof f === 'function') {
			n = 'tGrep' + p.charAt(0).toUpperCase() + p.substring(1);
			window[n] = f;
		}
	}
})();

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
