<?php
/**
 * rep2expack - ���j���[�̔񓯊��ǂݍ���
 * ����ł͂��C�ɔ�RSS�̃Z�b�g�؂�ւ��̂ݑΉ�
 */

require_once './conf/conf.inc.php';

$_login->authorize(); //���[�U�F��

$_conf['ktai'] = false;

$menu_php_self = '';

// {{{ HTTP�w�b�_��XML�錾

P2Util::header_nocache();
header('Content-Type: text/html; charset=Shift_JIS');

// }}}
// {{{ �{�̐���

// ���C�ɔ�
if (isset($_GET['m_favita_set'])) {
    $aShowBrdMenuPc = new ShowBrdMenuPc;
    ob_start();
    $aShowBrdMenuPc->printFavIta();
    $menuItem = ob_get_clean();
    $menuItem = preg_replace('{^\\s*<div class="menu_cate">.+?<div class="itas" id="c_favita">\\s*}s', '', $menuItem);
    $menuItem = preg_replace('{\\s*</div>\\s*</div>\\s*$}s', '', $menuItem);

// RSS
} elseif (isset($_GET['m_rss_set'])) {
    ob_start();
    require_once P2EX_LIB_DIR . '/rss/menu.inc.php';
    $menuItem = ob_get_clean();
    $menuItem = preg_replace('{^\\s*<div class="menu_cate">.+?<div class="itas" id="c_rss">\\s*}s', '', $menuItem);
    $menuItem = preg_replace('{\\s*</div>\\s*</div>\\s*$}s', '', $menuItem);

// �X�L��
} elseif (isset($_GET['m_skin_set'])) {
    $menuItem = changeSkin($_GET['m_skin_set']);

// ���̑�
} else {
    $menuItem = 'p2 error: �K�v�Ȉ������w�肳��Ă��܂���';
}

// }}}
// {{{ �{�̏o��

if (P2Util::isBrowserSafariGroup()) {
    $menuItem = P2Util::encodeResponseTextForSafari($menuItem);
}
echo $menuItem;
exit;

// }}}
// {{{ changeSkin()

/**
 * �X�L����؂�ւ���
 */
function changeSkin($skin)
{
    global $_conf;

    if (!preg_match('/^\w+$/', $skin)) {
        return "p2 error: �s���ȃX�L�� ({$skin}) ���w�肳��܂����B";
    }

    if ($skin == 'conf_style') {
        $newskin = './conf/conf_user_style.php';
    } else {
        $newskin = './skin/' . $skin . '.php';
    }

    if (file_exists($newskin)) {
        if (FileCtl::file_write_contents($_conf['expack.skin.setting_path'], $skin) !== FALSE) {
            return $skin;
        } else {
            return "p2 error: {$_conf['expack.skin.setting_path']} �ɃX�L���ݒ���������߂܂���ł����B";
        }
    } else {
        return "p2 error: �s���ȃX�L�� ({$skin}) ���w�肳��܂����B";
    }
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
