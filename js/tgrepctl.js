/* vim: set fileencoding=cp932 ai noet ts=4 sw=4 sts=4: */
/* mi: charset=Shift_JIS */

/* p2 - tGrep���j���[�𑀍삷�邽�߂�JavaScript */

/**
 * XmlHttpRequest�����s
 */
function tGrepExecRequest(uri, menuId)
{
	var req = getXmlHttp();
	if (!req) {
		alert('XMLHttp not available.');
		return false;
	}

	var receiver = document.getElementById(menuId);
	if (!receiver) {
		alert('replaceMenuItem() Error: A target element not exists.');
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
}

/**
 * ���[�U����̓��͂����X�g�ɒǉ�����
 */
function tGrepAppendListInput(file, menuId)
{
	var query = window.prompt('�L�[���[�h����͂��Ă�������', '');
	if (query !== null && query.length > 0) {
		query = encodeURIComponent(query);
		tGrepAppendListItem(file, menuId, query);
		if (parent.frames['subject'] && window.confirm('���̃L�[���[�h�Ō������܂����H')) {
			parent.frames['subject'].location.href = 'tgrepc.php?Q=' + query;
		}
	}
	return false;
}

/**
 * ���X�g�ɒǉ�����
 */
function tGrepAppendListItem(file, menuId, query)
{
	var uri = 'tgrepctl.php?file=' + file + '&query=' + query;
	tGrepExecRequest(uri, menuId);
	return false;
}

/**
 * ���X�g����폜����
 */
function tGrepRemoveListItem(file, menuId, query)
{
	var uri = 'tgrepctl.php?file=' + file + '&query=' + query + '&purge=true';
	tGrepExecRequest(uri, menuId);
	return false;
}

/**
 * ���X�g���N���A����
 */
function tGrepClearList(file, menuId)
{
	var uri = 'tgrepctl.php?file=' + file + '&clear=all';
	tGrepExecRequest(uri, menuId);
	return false;
}

/**
 * ���X�g���X�V����
 */
function tGrepUpdateList(file, menuId)
{
	var uri = 'tgrepctl.php?file=' + file;
	tGrepExecRequest(uri, menuId);
	return false;
}
