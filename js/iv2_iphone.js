/*
 * ImageCache2::Viewer - DOM�𑀍삵��iPhone�ɍœK������
 */

// {{{ DOMContentLoaded

window.addEventListener('DOMContentLoaded', function(event) {
	this.removeEventListener('DOMContentLoaded', arguments.callee, false);

	var styleSheets    = document.styleSheets;
	var commonStyle    = styleSheets[styleSheets.length - 3];
	var landscapeStyle = styleSheets[styleSheets.length - 2];
	var portraitStyle  = styleSheets[styleSheets.length - 1];

	if (typeof window.orientation != 'undefined') {
		var resize_image_table = function() {
			if (window.orientation % 180 == 0) {
				portraitStyle.disabled = false;
				landscapeStyle.disabled = true;
			} else {
				landscapeStyle.disabled = false;
				portraitStyle.disabled = true;
			}
		};

		// �e�[�u���̑傫���𒲐�
		resize_image_table();

		// ��]���̃C�x���g�n���h����ǉ�
		document.body.addEventListener('orientationchange', resize_image_table, false);
	} else {
		// ��]���T�|�[�g���Ȃ��u���E�U
		var table = document.getElementById('iv2-images');
		if (table) {
			var width = document.body.clientWidth;
			var cells = table.getElementsByTagName('td');

			commonStyle.insertRule('table#iv2-images { width: ' + width + 'px; }');
			if (cells && cells.length) {
				width -= (cells[0].clientWidth + 20);
				commonStyle.insertRule('div.iv2-image-title { width: ' + width + 'px; }');
			}
		}

		landscapeStyle.disabled = true;
		portraitStyle.disabled = true;
	}
}, false);

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
