/*
 * rep2expack - NG���ځ[�񑀍�
 */

// {{{ show_ng_message()

/*
 * NG���b�Z�[�W��\������
 *
 * @param String id
 * @param Element ng
 * @return void
 */
function show_ng_message(id, ng)
{
	if (document.getElementById(id).style.display == 'block') {
		document.getElementById(id).style.display = 'none';
	} else {
		document.getElementById(id).style.display = 'block';
	}

/*	if (ng && ng.parentNode) {
		ng.parentNode.removeChild(ng);
	}*/
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
