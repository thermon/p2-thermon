<?php
/**
 * rep2expack - RSS Parser
 */

require_once P2EX_LIB_DIR . '/rss/common.inc.php';
require_once 'XML/RSS.php';

// {{{ ImageCache2�Ƃ̘A�g����

if ($GLOBALS['_conf']['expack.rss.with_imgcache'] &&
    ((!$GLOBALS['_conf']['ktai'] && $GLOBALS['_conf']['expack.ic2.enabled'] % 2 == 1) ||
    ($GLOBALS['_conf']['ktai'] && $GLOBALS['_conf']['expack.ic2.enabled'] >= 2)))
{
    if (!class_exists('IC2_Switch', false)) {
        require P2EX_LIB_DIR . '/ic2/Switch.php';
    }
    if (IC2_Switch::get($GLOBALS['_conf']['ktai'])) {
        if (!function_exists('rss_get_image')) {
            require P2EX_LIB_DIR . '/rss/getimage.inc.php';
        }
        define('P2_RSS_IMAGECACHE_AVAILABLE', 1);
    } else {
        define('P2_RSS_IMAGECACHE_AVAILABLE', 0);
    }
} else {
    define('P2_RSS_IMAGECACHE_AVAILABLE', 0);
}

// }}}
// {{{ p2GetRSS()

/**
 * RSS���_�E�����[�h���A�p�[�X���ʂ�Ԃ�
 */
function p2GetRSS($remotefile, $atom = 0)
{
    global $_conf, $_info_msg_ht;

    $refresh = (!empty($_GET['refresh']) || !empty($_POST['refresh']));

    $localpath = rss_get_save_path($remotefile);
    if (PEAR::isError($localpath)) {
        $_info_msg_ht .= "<p>" . $localpath->getMessage() . "</p>\n";
        return $localpath;
    }

    // �ۑ��p�f�B���N�g�����Ȃ���΂���
    if (!is_dir(dirname($localpath))) {
        FileCtl::mkdir_for($localpath);
    }

    // If-Modified-Since���Ń_�E�����[�h�i�t�@�C�����������A�Â����A���������[�h�̂Ƃ��j
    if (!file_exists($localpath) || $refresh ||
        filemtime($localpath) < (time() - $_conf['expack.rss.check_interval'] * 60)
    ) {
        $dl = P2Util::fileDownload($remotefile, $localpath, true, 301);
        if ($dl->isSuccess()) {
            chmod($localpath, $_conf['expack.rss.setting_perm']);
        }
    }

    // �L���b�V�����X�V����Ȃ��������A�_�E�����[�h�����Ȃ�RSS���p�[�X
    if (file_exists($localpath) && (!isset($dl) || $dl->isSuccess())) {
        if ($atom) {
            $atom = (isset($dl) && $dl->code == 200) ? 2 : 1;
        }
        $rss = p2ParseRSS($localpath, $atom);
        return $rss;
    } else {
        return $dl;
    }

}

// }}}
// {{{ p2ParseRSS()

/**
 * RSS���p�[�X����
 */
function p2ParseRSS($xmlpath, $atom=0)
{
    global $_info_msg_ht;

    // $atom���^�Ȃ�XSL���g����RSS 1.0�ɕϊ�
    // �i�ϊ��ς݃t�@�C�������݂��Ȃ����A$atom==2�̂Ƃ��Ɏ��s�����j
    // ����XML(Atom)��encoding�������������w�肳��Ă����XSLT�v���Z�b�T��������
    // �����R�[�h��UTF-8(XSL�Ŏw�肵�������R�[�h)�ɕϊ����Ă����
    if ($atom) {
        $xslpath = P2EX_LIB_DIR . '/rss/atom03-to-rss10.xsl';
        $rsspath = $xmlpath . '.rss';
        if (file_exists($rsspath) && $atom != 2) {
            // OK
        } elseif (extension_loaded('xslt') || extension_loaded('xsl')) {
            if (!atom_to_rss($xmlpath, $xslpath, $rsspath)) {
                $retval = false;
                return $retval;
            }
        } else {
            $_info_msg_ht = '<p>p2 error: Atom�t�B�[�h��ǂނɂ�PHP��XSLT�@�\�g���܂���XSL�@�\�g�����K�v�ł��B</p>';
            $retval = false;
            return $retval;
        }
    } else {
        $rsspath = $xmlpath;
    }

    // �G���R�[�f�B���O�𔻒肵�AXML_RSS�N���X�̃C���X�^���X�𐶐�����
    // 2006-02-01 �蓮����p�~
    /*$srcenc = 'UTF-8';
    $tgtenc = 'UTF-8';
    if ($fp = @fopen($rsspath, 'rb')) {
        $content = fgets($fp, 64);
        if (preg_match('/<\\?xml version=(["\'])1.0\\1 encoding=(["\'])(.+?)\\2 ?\\?>/', $content, $matches)) {
            $srcenc = $matches[3];
        }
        fclose($fp);
    }
    $rss = new XML_RSS($rsspath, $srcenc, $tgtenc);*/
    $rss = new XML_RSS($rsspath);
    if (PEAR::isError($rss)) {
        $_info_msg_ht = '<p>p2 error: RSS - ' . $rss->getMessage() . '</p>';
        return $rss;
    }
    // ��͑Ώۂ̃^�O���㏑��
    $rss->channelTags = array_unique(array_merge($rss->channelTags, array (
        'CATEGORY', 'CLOUD', 'COPYRIGHT', 'DESCRIPTION', 'DOCS', 'GENERATOR', 'IMAGE',
        'ITEMS', 'LANGUAGE', 'LASTBUILDDATE', 'LINK', 'MANAGINGEditor', 'PUBDATE',
        'RATING', 'SKIPDAYS', 'SKIPHOURS', 'TEXTINPUT', 'TITLE', 'TTL', 'WEBMASTER'
    )));
    $rss->itemTags = array_unique(array_merge($rss->itemTags, array (
        'AUTHOR', 'CATEGORY', 'COMMENTS', 'CONTENT:ENCODED', 'DESCRIPTION',
        'ENCLOSURE', 'GUID', 'LINK', 'PUBDATE', 'SOURCE', 'TITLE'
    )));
    $rss->imageTags = array_unique(array_merge($rss->imageTags, array (
        'DESCRIPTION', 'HEIGHT', 'LINK', 'TITLE', 'URL', 'WIDTH'
    )));
    $rss->textinputTags = array_unique(array_merge($rss->textinputTags, array (
        'DESCRIPTION', 'LINK', 'NAME', 'TITLE'
    )));
    $rss->moduleTags = array_unique(array_merge($rss->moduleTags, array (
        'BLOGCHANNEL:BLOGROLL', 'BLOGCHANNEL:CHANGES', 'BLOGCHANNEL:MYSUBSCRIPTIONS',
        'CC:LICENSE', 'CONTENT:ENCODED', 'DC:CONTRIBUTOR', 'DC:COVERAGE',
        'DC:CREATOR', 'DC:DATE', 'DC:DESCRIPTION', 'DC:FORMAT', 'DC:IDENTIFIER',
        'DC:LANGUAGE', 'DC:PUBDATE', 'DC:PUBLISHER', 'DC:RELATION', 'DC:RIGHTS',
        'DC:SOURCE', 'DC:SUBJECT', 'DC:TITLE', 'DC:TYPE',
        'SY:UPDATEBASE', 'SY:UPDATEFREQUENCY', 'SY:UPDATEPERIOD'
    )));
    // RSS���p�[�X
    $result = $rss->parse();
    if (PEAR::isError($result)) {
        $_info_msg_ht = '<p>p2 error: RSS - ' . $result->getMessage() . '</p>';
        return $result;
    }

    return $rss;
}

// }}}
// {{{ atom_to_rss()

/**
 * Atom 0.3 �� RSS 1.0 �ɕϊ�����i���ʁj
 */
function atom_to_rss($input, $stylesheet, $output)
{
    global $_conf, $_info_msg_ht;

    // �ۑ��p�f�B���N�g�����Ȃ���΂���
    if (!is_dir(dirname($output))) {
        FileCtl::mkdir_for($output);
    }

    // �ϊ�
    if (extension_loaded('xslt')) { // PHP4, Sablotron
        $rss_content = atom_to_rss_by_xslt($input, $stylesheet, $output);
    } elseif (extension_loaded('xsl')) { // PHP5, LibXSLT
        $rss_content = atom_to_rss_by_xsl($input, $stylesheet, $output);
    }

    // �`�F�b�N
    if (!$rss_content) {
        if (file_exists($output)) {
            unlink($output);
        }
        return FALSE;
    }
    chmod($output, $_conf['expack.rss.setting_perm']);

    // FreeBSD 5.3 Ports �� textproc/php4-xslt �ł̓o�O�̂������ϊ��̍ۂɖ��O��Ԃ�������̂ŕ␳����
    // (php4-xslt-4.3.10_2, expat-1.95.8, libiconv-1.9.2_1, Sablot-1.0.1)
    // �o�O�̂Ȃ����Ȃ牽���ς��Ȃ��E�E�E�͂��B
    $rss_fix_patterns = array(
        '/<(\/)?(RDF|Seq|li)( .+?)?>/u'       => '<$1rdf:$2$3>',
        '/<(channel|item) about=/u'           => '<$1 rdf:about=',
        '/<(\/)?(encoded)>/u'                 => '<$1content:$2>',
        '/<(\/)?(creator|subject|date|pubdate)>/u' => '<$1dc:$2>');
    $rss_fixed = preg_replace(array_keys($rss_fix_patterns), array_values($rss_fix_patterns), $rss_content);
    if (md5($rss_content) != md5($rss_fixed)) {
        $fp = @fopen($output, 'wb') or p2die("cannot write. ({$output})");
        flock($fp, LOCK_EX);
        fwrite($fp, $rss_fixed);
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    return TRUE;
}

// }}}
// {{{ atom_to_rss_by_xslt()

/**
 * Atom 0.3 �� RSS 1.0 �ɕϊ�����iPHP4, XSLT�j
 */
function atom_to_rss_by_xslt($input, $stylesheet, $output)
{
    global $_info_msg_ht;

    $xh = xslt_create();
    if (!@xslt_process($xh, $input, $stylesheet, $output)) {
        $errmsg = xslt_errno($xh) . ': ' . xslt_error($xh);
        $_info_msg_ht = '<p>p2 error: XSLT - Atom��RSS�ɕϊ��ł��܂���ł����B(' . $errmsg . ')</p>';
        xslt_free($xh);
        return FALSE;
    }
    xslt_free($xh);

    return FileCtl::file_read_contents($output);
}

// }}}
// {{{ atom_to_rss_by_xsl()

/**
 * Atom 0.3 �� RSS 1.0 �ɕϊ�����iPHP5, DOM & XSL�j
 */
function atom_to_rss_by_xsl($input, $stylesheet, $output)
{
    global $_info_msg_ht;

    $xmlDoc = new DomDocument;
    if ($xmlDoc->load(realpath($input))) {
        $xslDoc = new DomDocument;
        $xslDoc->load(realpath($stylesheet));

        $proc = new XSLTProcessor;
        $proc->importStyleSheet($xslDoc);

        $rssDoc = $proc->transformToDoc($xmlDoc);
        $rssDoc->save($output);

        $rss_content = FileCtl::file_read_contents($output);
    } else {
        $rss_content = null;
    }

    if (!$rss_content) {
        $_info_msg_ht = '<p>p2 error: XSL - Atom��RSS�ɕϊ��ł��܂���ł����B</p>';
        return FALSE;
    }

    return $rss_content;
}

// }}}
// {{{ rss_item_exists()

/**
 * RSS��item�v�f�ɔC�ӂ̎q�v�f�����邩�ǂ������`�F�b�N����
 * ��v�f�͖���
 */
function rss_item_exists($items, $element)
{
    foreach ($items as $item) {
        if (isset($item[$element]) && strlen(trim($item[$element])) > 0) {
            return TRUE;
        }
    }
    return FALSE;
}

// }}}
// {{{ rss_format_date()

/**
 * RSS�̓��t��\���p�ɒ�������
 */
function rss_format_date($date)
{
    if (preg_match('/(?P<date>(\d\d)?\d\d-\d\d-\d\d)T(?P<time>\d\d:\d\d(:\d\d)?)(?P<zone>([+\-])(\d\d):(\d\d)|Z)?/', $date, $t)) {
        $time = $t['date'].' '.$t['time'].' ';
        if ($t['zone'] && $t['zone'] != 'Z') {
            $time .= $t[6].$t[7].$t[8]; // [+-]HHMM
        } else {
            $time .= 'GMT';
        }
        return date('y/m/d H:i:s', strtotime($time));
    }
    return htmlspecialchars($date, ENT_QUOTES);
}

// }}}
// {{{ rss_desc_converter()

/**
 * RSS��description�v�f��\���p�ɒ�������
 */
function rss_desc_converter($description)
{
    // HTML�^�O���Ȃ����CR+LF/CR/LF��<br>+LF�ɂ���ȂǁA�y�����`����
    if (!preg_match('/<(\/?[A-Za-z]+[1-6]?)( [^>]+>)?( ?\/)?>/', $description)) {
        return preg_replace('/[ \t]*(\r\n?|\n)[ \t]*/', "<br>\n", trim($description));
    }

    // ������^�O�ꗗ
    $allowed_tags = '<a><b><i><u><s><strong><em><code><br><h1><h2><h3><h4><h5><h6><p><div><address><blockquote><ol><ul><li><img>';

    // script�v�f��style�v�f�͒��g���Ƃ܂Ƃ߂ď���
    $description = preg_replace('/<(script|style)(?: .+?)?>(.+?)?<\/\1>/is', '', $description);
    // �s���̃^�O������
    $description = strip_tags($description, $allowed_tags);
    // �^�O�̑����`�F�b�N
    $description = preg_replace_callback('/<(\/?[A-Za-z]+[1-6]?)( [^>]+?)?>/', 'rss_desc_tag_cleaner', $description);

    return $description;
}

// }}}
// {{{ rss_desc_tag_cleaner()

/**
 * �����^�O�����Ȃǂ���������R�[���o�b�N�֐�
 */
function rss_desc_tag_cleaner($tag)
{
    global $_conf;

    $element = strtolower($tag[1]);
    $attributes = trim($tag[2]);
    $close = trim($tag[3]); // HTML 4.01�`���ŕ\������̂Ŗ���

    // �I���^�O�Ȃ�
    if (!$attributes || substr($element, 0, 1) == '/') {
        return '<'.$element.'>';
    }

    $tag = '<'.$element;
    if (preg_match_all('/(?:^| )([A-Za-z\-]+)\s*=\s*("[^"]*"|\'[^\']*\'|\w[^ ]*)(?: |$)/', $attributes, $matches, PREG_SET_ORDER)) {

        foreach ($matches as $attr) {
            $key = strtolower($attr[1]);
            $value = $attr[2];

            // JavaScript�C�x���g�n���h���E�X�^�C���V�[�g�E�^�[�Q�b�g�Ȃǂ̑����͋֎~
            if (preg_match('/^(on[a-z]+|style|class|id|target)$/', $key)) {
                continue;
            }

            // �l�̈��p�����폜
            $q = substr($value, 0, 1);
            if ($q == "'") {
                $value = str_replace('"', '&quot;', substr($value, 1, -1));
            } elseif ($q == '"') {
                $value = substr($value, 1, -1);
            }

            // �����ŕ���
            switch ($key) {
                case 'href':
                    if ($element != 'a' || preg_match('/^javascript:/i', $value)) {
                        break; // a�v�f�ȊO��href�����֎~
                    }
                    if (preg_match('|^[^/:]*/|', $value)) {
                        $value = rss_url_rel_to_abs($value);
                    }
                    return '<a href="'.P2Util::throughIme($value).'"'.$_conf['ext_win_target_at'].'>';
                case 'src':
                    if ($element != 'img' || preg_match('/^javascript:/i', $value)) {
                        break; // img�v�f�ȊO��src�����֎~
                    }
                    if (preg_match('|^[^/:]*/|', $value)) {
                        $value = rss_url_rel_to_abs($value);
                    }
                    if (P2_RSS_IMAGECACHE_AVAILABLE) {
                        $image = rss_get_image($value, $GLOBALS['channel']['title']);
                        if ($image[3] != P2_IMAGECACHE_OK) {
                            if ($_conf['ktai']) {
                                // ���ځ[��摜 - �g��
                                switch ($image[3]) {
                                    case P2_IMAGECACHE_ABORN:return '[p2:���ځ[��摜]';
                                    case P2_IMAGECACHE_BROKEN: return '[p2:��]'; // �����
                                    case P2_IMAGECACHE_LARGE: return '[p2:��]'; // ����͌���ł͖���
                                    case P2_IMAGECACHE_VIRUS: return '[p2:�E�B���X�x��]';
                                    default : return '[p2:unknown error]'; // �\��
                                }
                            } else {
                                // ���ځ[��摜 - PC
                                return "<img src=\"{$image[0][0]}\" {$image[0][1]}>";
                            }
                        } elseif ($_conf['ktai']) {
                            // �C�����C���\�� - �g�сiPC�p�T���l�C���T�C�Y�j
                            return "<img src=\"{$image[1][0]}\" {$image[1][1]}>";
                        } else {
                            // �C�����C���\�� - PC�i�t���T�C�Y�j
                            return "<img src=\"{$image[0][0]}\" {$image[0][1]}>";
                        }
                    }
                    // �C���[�W�L���b�V���������̂Ƃ��摜�͕\�����Ȃ�
                    break '';
                case 'alt':
                    if ($element == 'img' && !P2_RSS_IMAGECACHE_AVAILABLE) {
                        return ' [img:'.$value.']'; // �摜��alt���������ɕ\��
                    }
                    $tag .= ' ="'.$value.'"';
                    break;
                case 'width':
                case 'height':
                    // �Ƃ肠��������
                    break;
                default:
                    $tag .= ' ="'.$value.'"';
            }

        } // endforeach

        // �v�f�ōŏI�m�F
        switch ($element) {
            // href�������Ȃ�����a�v�f
            case 'a':
                return '<a>';
            // alt�������Ȃ�����img�v�f
            case 'img':
                return '';
        }
    } // endif
    $tag .= '>';

    return $tag;
}

// }}}
// {{{ rss_url_rel_to_abs()

/**
 * ���� URL ���� URL �ɂ��ĕԂ��֐�
 *
 * �O���[�o���ϐ����Q�Ƃ���������Ƃ��� RSS �� URL ��^����������]�܂�����
 * �ύX���K�v�ȉӏ������������̂Ŏ蔲��
 */
function rss_url_rel_to_abs($url)
{
    // URL ���p�[�X
    $p = @parse_url($GLOBALS['channel']['link']);
    if (!$p || !isset($p['scheme']) || $p['scheme'] != 'http' || !isset($p['host'])) {
        return $url;
    }

    // ���[�g URL ���쐬
    $top = $p['scheme'] . '://';
    if (isset($p['user'])) {
        $top .= $p['user'];
        if (isset($p['pass'])) {
            $top .= '@' . $p['pass'];
        }
        $top .= ':';
    }
    $top .= $p['host'];
    if (isset($p['port'])) {
        $top .= ':' . $p['port'];
    }

    // ��΃p�X�Ȃ烋�[�g URL �ƌ������ĕԂ�
    if (substr($url, 0, 1) == '/') {
        return $top . $url;
    }

    // ���[�g URL �ɃX���b�V����t��
    $top .= '/';

    // �`�����l���̃p�X�𕪉�
    if (isset($p['path'])) {
        $paths1 = explode('/', trim($p['path'], '/'));
    } else {
        $paths1 = array();
    }

    // ���� URL �𕪉�
    if ($query = strstr($url, '?')) {
        $paths2 = explode('/', substr($url, 0, strlen($query) * -1));
    } else {
        $paths2 = explode('/', $url);
        $query = '';
    }

    // ������������ URL �̃p�X���΃p�X�ɉ�����
    while (($s = array_shift($paths2)) !== null) {
        $r = $s;
        switch ($s) {
            case '':
            case '.':
                // pass
                break;
            case '..':
                array_pop($paths1);
                break;
            default:
                array_push($paths1, $s);
        }
    }
    // ���΃p�X���X���b�V���ŏI����Ă����Ƃ��̏���
    if ($r === '') {
        array_push($paths1, '');
    }

    //��� URL ��Ԃ�
    return $top . implode('/', $paths1) . $query;
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
