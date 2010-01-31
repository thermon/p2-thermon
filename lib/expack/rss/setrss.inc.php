<?php
/**
 * rep2expack - RSS���X�g�̏���
 */

require_once P2EX_LIB_DIR . '/rss/parser.inc.php';

// {{{ �ϐ�

// ���N�G�X�g�ǂݍ���
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['submit_setrss'])) {
        FileCtl::make_datafile($_conf['expack.rss.setting_path'], $_conf['expack.rss.setting_perm']);

        $fp = fopen($_conf['expack.rss.setting_path'], 'wb');
        if (!$fp) {
            p2die("{$_conf['expack.rss.setting_path']} ���X�V�ł��܂���ł���");
        }
        flock($fp, LOCK_EX);

        if (isset($_POST['list'])) {
            if (preg_match_all('/^([^\\t]+)\\t([^\\t]+)(?:\\t([^\\t]*))?$/m', $_POST['list'], $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $site = trim($m[1]);
                    $xml  = trim($m[2]);
                    if (isset($m[3])) {
                        $atom = trim($m[3]);
                        $atom = ($atom === '' || $atom === '0') ? '0' : '1';
                    } else {
                        $atom = '0';
                    }
                    fputs($fp, "{$site}\t{$xml}\t{$atom}\n");
                }
            }
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        $script = <<<EOJS
<script type="text/javascript">
//<![CDATA[
if (parent.menu) {
    parent.menu.location.href = '{$_conf['menu_php']}?nr=1';
}
//]]>
</script>\n
EOJS;
        P2Util::pushInfoHtml($script);

        unset($site, $xml, $atom, $m, $matches, $fp);
        return;
    }
}

$setrss  = isset($_REQUEST['setrss'])  ? trim($_REQUEST['setrss'])  : '';
$xml     = isset($_REQUEST['xml'])     ? trim($_REQUEST['xml'])     : '';
$site    = isset($_REQUEST['site'])    ? trim($_REQUEST['site'])    : '';
$site_en = isset($_REQUEST['site_en']) ? trim($_REQUEST['site_en']) : '';
$atom    = empty($_REQUEST['atom'])    ? 0 : 1;

// feed�X�L�[����http�X�L�[���Œu��
$xml = preg_replace('|^feed://|', 'http://', $xml);

// RSS�̃^�C�g���ݒ�
if ($site === '') {
    if ($site_en !== '') {
        $site = UrlSafeBase64::decode($site_en);
    } else {
        $purl = @parse_url($xml);
        if (is_array($purl)) {
            if (array_key_exists('host', $purl)) {
                $site .= $purl['host'];
            }
            if (array_key_exists('path', $purl)) {
                $site .= '.' . basename($purl['path']);
            }
            if (array_key_exists('query', $purl)) {
                $site .= '?' . $purl['query'];
            }
        }
        $site = basename($xml);

        $rss = p2GetRSS($xml);
        if ($rss instanceof XML_RSS) {
            $channelInfo = $rss->getChannelInfo();
            if (is_array($channelInfo) && array_key_exists('title', $channelInfo)) {
                $site = mb_convert_encoding($channelInfo['title'], 'CP932', 'UTF-8,CP51932,CP932,ASCII');
            }
        }
    }
}

// ���O�ɋL�^����ϐ����Œ���̃T�j�^�C�Y
$xml = preg_replace_callback('/\\s/', 'rawurlencode', $xml);
$site = preg_replace('/\\s+/', ' ', $site);
$site = htmlspecialchars($site, ENT_QUOTES);

// }}}
// {{{ �ǂݍ���

// rss_path�t�@�C�����Ȃ���ΐ���
FileCtl::make_datafile($_conf['expack.rss.setting_path'], $_conf['expack.rss.setting_perm']);

// rss_path�ǂݍ���;
$lines = FileCtl::file_read_lines($_conf['expack.rss.setting_path'], FILE_IGNORE_NEW_LINES);

// }}}
// {{{ ����

// �ŏ��ɏd���v�f������
if ($lines) {
    $i = -1;
    unset($neolines);
    foreach ($lines as $l) {
        $i++;

        $lar = explode("\t", $l);

        if ($lar[1] == $xml) { // �d�����
            $before_line_num = $i;
            continue;
        } elseif (strlen($lar[1]) == 0) { // URL�Ȃ����A�E�g
            continue;
        } else {
            $neolines[] = $l;
        }
    }
}

// �V�K�f�[�^�ݒ�
if ($setrss) {
    if ($xml && $site) {
        if ($atom == 1 || $setrss == 'atom') {
            $newdata = implode("\t", array($site, $xml, '1'));
        } else {
            $newdata = implode("\t", array($site, $xml, '0'));
        }
    }
    switch ($setrss) {
        case '0':
            $after_line_num = -1;
        case '1':
        case 'top':
            $after_line_num = 0;
            break;
        case 'up':
            $after_line_num = $before_line_num -1 ;
            if ($after_line_num < 0) {
                $after_line_num = 0;
            }
            break;
        case 'down':
            $after_line_num = $before_line_num + 1;
            if ($after_line_num >= count($neolines)) {
                $after_line_num = 'bottom';
            }
            break;
        case 'bottom';
            $after_line_num = 'bottom';
            break;
        default:
            $after_line_num = $before_line_num;
            if ($after_line_num >= count($neolines)) {
                $after_line_num = 'bottom';
            }
    }
}

// }}}
// {{{ ��������

$fp = @fopen($_conf['expack.rss.setting_path'], 'wb');
if (!$fp) {
    p2die("{$_conf['expack.rss.setting_path']} ���X�V�ł��܂���ł���");
}
flock($fp, LOCK_EX);
if ($neolines) {
    $i = 0;
    foreach ($neolines as $l) {
        if ($i === $after_line_num) {
            fputs($fp, $newdata."\n");
        }
        fputs($fp, $l."\n");
        $i++;
    }
    if ($after_line_num === 'bottom') {
        fputs($fp, $newdata."\n");
    }
    //�u$after_line_num == 'bottom'�v���ƌ듮�삷��B
} else {
    fputs($fp, $newdata);
}
flock($fp, LOCK_UN);
fclose($fp);

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
