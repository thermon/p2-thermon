<?php
/**
 * rep2expck - RSS�摜�L���b�V��
 */

require_once P2EX_LIB_DIR . '/bootstrap.php';

// {{{ rss_get_image()

/**
 * �C���[�W�L���b�V����URL�Ɖ摜�T�C�Y��Ԃ�
 */
function rss_get_image($src_url, $memo='')
{
    static $cache = array();

    $key = md5(serialize(func_get_args()));

    if (!isset($cache[$key])) {
        $cache[$key] = rss_get_image_ic2($src_url, $memo);
    }

    return $cache[$key];
}

// }}}
// {{{ rss_get_image_ic2()

/**
 * �C���[�W�L���b�V����URL�Ɖ摜�T�C�Y��Ԃ� (ImageCache2)
 */
function rss_get_image_ic2($src_url, $memo='')
{
    static $thumbnailer = NULL;
    static $thumbnailer_k = NULL;

    if (is_null($thumbnailer)) {
        $thumbnailer = new IC2_Thumbnailer(IC2_Thumbnailer::SIZE_PC);
        $thumbnailer_k = new IC2_Thumbnailer(IC2_Thumbnailer::SIZE_MOBILE);
    }

    $icdb = new IC2_DataObject_Images;

    if ($thumbnailer->ini['General']['automemo'] && $memo !== '') {
        $img_memo = $icdb->uniform($memo, 'CP932');
        if ($memo !== '') {
            $img_memo_query = '&amp;' . $_conf['detect_hint_q_utf8'];
            $img_memo_query .= '&amp;memo=' . rawurlencode($img_memo);
        } else {
            $img_memo = NULL;
            $img_memo_query = '';
        }
    } else {
        $img_memo = NULL;
        $img_memo_query = '';
    }

    $url_en = rawurlencode($src_url);

    // �摜�\�����@
    //   r=0:HTML;r=1:���_�C���N�g;r=2:PHP�ŕ\��
    //   �C�����C���\���p�T���l�C���̓I���W�i�����L���b�V������Ă����URL���Z���Ȃ�̂�r=2
    //   �g�їp�T���l�C���i�S��ʕ\�����ړI�j�̓C�����C���\�����Ȃ��̂�r=0
    // �T���l�C���̑傫��
    //   t=0:�I���W�i��;t=1:PC�p�T���l�C��;t=2:�g�їp�T���l�C��;t=3:���ԃC���[�W
    $img_url = 'ic2.php?r=1&amp;uri=' . $url_en;
    $img_size = '';
    $thumb_url = 'ic2.php?r=2&amp;t=1&amp;uri=' . $url_en;
    $thumb_url2 = 'ic2.php?r=2&amp;t=1&amp;id=';
    $thumb_size = '';
    $thumb_k_url = 'ic2.php?r=0&amp;t=2&amp;uri=' . $url_en;
    $thumb_k_url2 = 'ic2.php?r=0&amp;t=1&amp;id=';
    $thumb_k_size = '';
    $src_exists = FALSE;

    // DB�ɉ摜��񂪓o�^����Ă����Ƃ�
    if ($icdb->get($src_url)) {

        // �E�B���X�Ɋ������Ă����t�@�C���̂Ƃ�
        if ($icdb->mime == 'clamscan/infected') {
            $aborn_img = array('./img/x04.png', 'width="32" height="32"');
            return array($aborn_img, $aborn_img, $aborn_img, P2_IMAGECACHE_ABORN);
        }
        // ���ځ[��摜�̂Ƃ�
        if ($icdb->rank < 0) {
            $virus_img = array('./img/x01.png', 'width="32" height="32"');
            return array($virus_img, $virus_img, $virus_img, P2_IMAGECACHE_VIRUS);
        }

        // �I���W�i�����L���b�V������Ă���Ƃ��͉摜�𒼐ړǂݍ���
        $_img_url = $thumbnailer->srcPath($icdb->size, $icdb->md5, $icdb->mime);
        if (file_exists($_img_url)) {
            $img_url = $_img_url;
            $img_size = "width=\"{$icdb->width}\" height=\"{$icdb->height}\"";
            $src_exists = TRUE;
        }

        // �T���l�C�����쐬����Ă��Ă���Ƃ��͉摜�𒼐ړǂݍ���
        $_thumb_url = $thumbnailer->thumbPath($icdb->size, $icdb->md5, $icdb->mime);
        if (file_exists($_thumb_url)) {
            $thumb_url = $_thumb_url;
            // �����^�C�g�������@�\��ON�Ń^�C�g�����L�^����Ă��Ȃ��Ƃ���DB���X�V
            if (!is_null($img_memo) && strpos($icdb->memo, $img_memo) === false){
                $update = new IC2_DataObject_Images;
                if (!is_null($icdb->memo) && strlen($icdb->memo) > 0) {
                    $update->memo = $img_memo . ' ' . $icdb->memo;
                } else {
                    $update->memo = $img_memo;
                }
                $update->whereAddQuoted('uri', '=', $src_url);
                $update->update();
            }
        } elseif ($src_exists) {
            $thumb_url = $thumb_url2 . $icdb->id;
        }

        // �g�їp�T���l�C�����쐬����Ă��Ă���Ƃ��͉摜�𒼐ړǂݍ���
        $_thumb_k_url = $thumbnailer_k->thumbPath($icdb->size, $icdb->md5, $icdb->mime);
        if (file_exists($_thumb_k_url)) {
            $thumb_k_url = $_thumb_k_url;
        } elseif ($src_exists) {
            $thumb_k_url = $thumb_k_url2 . $icdb->id;
        }

        // �T���l�C���̉摜�T�C�Y
        $thumb_size = $thumbnailer->calc($icdb->width, $icdb->height);
        $thumb_size = preg_replace('/(\d+)x(\d+)/', 'width="$1" height="$2"', $thumb_size);

        // �g�їp�T���l�C���̉摜�T�C�Y
        $thumb_k_size = $thumbnailer_k->calc($icdb->width, $icdb->height);
        $thumb_k_size = preg_replace('/(\d+)x(\d+)/', 'width="$1" height="$2"', $thumb_k_size);

    // �摜���L���b�V������Ă��Ȃ��Ƃ�
    // �����^�C�g�������@�\��ON�Ȃ�N�G����UTF-8�G���R�[�h�����^�C�g�����܂߂�
    } else {
        $img_url .= $img_memo_query;
        $thumb_url .= $img_memo_query;
        $thumb_k_url .= $img_memo_query;
    }

    $result = array();
    $result[] = array($img_url, $img_size);
    $result[] = array($thumb_url, $thumb_size);
    $result[] = array($thumb_k_url, $thumb_k_size);
    $result[] = P2_IMAGECACHE_OK;

    return $result;
}

// }}}

/*
 * Local Variables:
 * mode: php
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
