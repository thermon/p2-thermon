/*
 * ImageCache2::Load Thumbnail
 */

// {{{ loadThumb()

/**
 * ��\����Ԃ̃T���l�C����ǂݍ���
 * 
 * �ǂݍ��ݔ���ɂ͒u���ΏۃI�u�W�F�N�g�̗L���𗘗p�B
 * �Ԃ�l�͉摜���ǂݍ��ݍς݂��ۂ��B
 *
 * @param String thumb_url
 * @param String thumb_id
 * @return void
 */
function loadThumb(thumb_url, thumb_id)
{
	var tmp_thumb = document.getElementById(thumb_id);
	if (!tmp_thumb) {
		return true;
	}

	var thumb = document.createElement('img');
	thumb.className = 'thumbnail';
	thumb.setAttribute('src', thumb_url);
	thumb.setAttribute('hspace', 4);
	thumb.setAttribute('vspace', 4);
	thumb.setAttribute('align', 'middle');

	tmp_thumb.parentNode.replaceChild(thumb, tmp_thumb);

	// IE�ł͓ǂݍ��݊������Ă��烊�T�C�Y���Ȃ��ƕςȋ����ɂȂ�̂�
	if (document.all) {
		thumb.onload = function() {
			autoImgSize(thumb_id);
		}
	// ���̑�
	} else {
		autoImgSize(thumb_id);
	}

	return false;
}

// }}}
// {{{ autoImgSize()

/**
 * �ǂݍ��݂����������T���l�C����{���̃T�C�Y�ŕ\������
 *
 * @param String thumb_id
 * @return void
 */
function autoImgSize(thumb_id)
{
	var thumb = document.getElementById(thumb_id);
	if (!thumb) {
		return;
	}

	thumb.style.width = 'auto';
	thumb.style.height = 'auto';
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
