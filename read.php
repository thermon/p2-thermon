<?php
/**
 * rep2 - �X���b�h�\���X�N���v�g
 * �t���[��������ʁA�E������
 */

require_once '/opt/lampp/htdocs/pqp/classes/PhpQuickProfiler.php';
class ExampleLandingPage {

    private $profiler;
    private $db;

    public function __construct() {
        $this->profiler = new PhpQuickProfiler(PhpQuickProfiler::getMicroTime());
    }

    public function __destruct() {
		global $debugMode;

        if($debugMode == true) $this->profiler->display($this->db);
    }

}
$db=new ExampleLandingPage;
//$debugMode=true;
require_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

//================================================================
// �ϐ�
//================================================================
$newtime = date('gis');  // ���������N���N���b�N���Ă��ēǍ����Ȃ��d�l�ɑ΍R����_�~�[�N�G���[
// $_today = date('y/m/d');
$is_ajax = !empty($_GET['ajax']);

//=================================================
// �X���̎w��
//=================================================
detectThread();    // global $host, $bbs, $key, $ls

//=================================================
// ���X�t�B���^
//=================================================
$word = isset($_REQUEST['word']) ? $_REQUEST['word'] : null;
$res_filter = array('field' => 'hole', 'match' => 'on', 'method' => 'or');
if (!empty($_REQUEST['field']))  { $res_filter['field']  = $_REQUEST['field'];  }
if (!empty($_REQUEST['match']))  { $res_filter['match']  = $_REQUEST['match'];  }
if (!empty($_REQUEST['method'])) { $res_filter['method'] = $_REQUEST['method']; }

if (isset($word) && strlen($word) > 0) {
    if ($res_filter['method'] == 'regex' && substr_count($word, '.') == strlen($word)) {
        $word = null;
    } elseif (p2_set_filtering_word($word, $res_filter['method']) !== null) {
        $_conf['filtering'] = true;
        if ($_conf['ktai']) {
            $page = (isset($_REQUEST['page'])) ? max(1, intval($_REQUEST['page'])) : 1;
            $filter_range = array(
                'page'  => $page,
                'start' => ($page - 1) * $_conf['mobile.rnum_range'] + 1,
                'to'    => $page * $_conf['mobile.rnum_range'],
            );
        }
    } else {
        $word = null;
    }
} else {
    $word = null;
}

//=================================================
// �t�B���^�l�ۑ�
//=================================================
$cachefile = $_conf['pref_dir'] . '/p2_res_filter.txt';

// �t�B���^�w�肪�Ȃ���ΑO��ۑ���ǂݍ��ށi�t�H�[���̃f�t�H���g�l�ŗ��p�j
if (!isset($GLOBALS['word'])) {

    if ($res_filter_cont = FileCtl::file_read_contents($cachefile)) {
        $res_filter = unserialize($res_filter_cont);
    }

// �t�B���^�w�肪�����
} else {

    // �{�^����������Ă����Ȃ�A�t�@�C���ɐݒ��ۑ�
    if (isset($_REQUEST['submit_filter'])) { // !isset($_REQUEST['idpopup'])
        FileCtl::make_datafile($cachefile, $_conf['p2_perm']); // �t�@�C�����Ȃ���ΐ���
        if ($res_filter) {
            $res_filter_cont = serialize($res_filter);
        }
        if ($res_filter_cont && !$popup_filter) {
            if (FileCtl::file_write_contents($cachefile, $res_filter_cont) === false) {
                p2die('cannot write file.');
            }
        }
    }
}


//=================================================
// ���ځ[��&NG���[�h�ݒ�ǂݍ���
//=================================================
$GLOBALS['ngaborns'] = NgAbornCtl::loadNgAborns();

//==================================================================
// ���C��
//==================================================================

if (!isset($aThread)) {
    $aThread = new ThreadRead();
}

// ls�̃Z�b�g
if (!empty($ls)) {
    $aThread->ls = mb_convert_kana($ls, 'a');
}

//==========================================================
// idx�̓ǂݍ���
//==========================================================

// host�𕪉�����idx�t�@�C���̃p�X�����߂�
if (!isset($aThread->keyidx)) {
    $aThread->setThreadPathInfo($host, $bbs, $key);
}

// �f�B���N�g����������΍��
// FileCtl::mkdir_for($aThread->keyidx);

$aThread->itaj = P2Util::getItaName($host, $bbs);
if (!$aThread->itaj) { $aThread->itaj = $aThread->bbs; }

// idx�t�@�C��������Γǂݍ���
if ($lines = FileCtl::file_read_lines($aThread->keyidx, FILE_IGNORE_NEW_LINES)) {
    $idx_data = explode('<>', $lines[0]);
} else {
    $idx_data = array_fill(0, 12, '');
}
$aThread->getThreadInfoFromIdx();

//==========================================================
// preview >>1
//==========================================================

//if (!empty($_GET['onlyone'])) {
if (!empty($_GET['one'])) {
    $aThread->ls = '1';
    $aThread->resrange = array('start' => 1, 'to' => 1, 'nofirst' => false);

    // �K���������m�ł͂Ȃ����֋X�I��
    //if (!isset($aThread->rescount) && !empty($_GET['rc'])) {
    if (!isset($aThread->rescount) && !empty($_GET['rescount'])) {
        //$aThread->rescount = $_GET['rc'];
        $aThread->rescount = (int)$_GET['rescount'];
    }

    $preview = $aThread->previewOne();
    $ptitle_ht = htmlspecialchars($aThread->itaj, ENT_QUOTES) . ' / ' . $aThread->ttitle_hd;

    // PC
    if (!$_conf['ktai']) {
        $read_header_inc_php = P2_LIB_DIR . '/read_header.inc.php';
        $read_footer_inc_php = P2_LIB_DIR . '/read_footer.inc.php';
    // �g��
    } else {
        $read_header_inc_php = P2_LIB_DIR . '/read_header_k.inc.php';
        $read_footer_inc_php = P2_LIB_DIR . '/read_footer_k.inc.php';
    }

    require_once $read_header_inc_php;
    echo $preview;
    require_once $read_footer_inc_php;

    return;
}

//===========================================================
// DAT�̃_�E�����[�h
//===========================================================
$offline = !empty($_GET['offline']);

if (!$offline) {
    $aThread->downloadDat();
}

// DAT��ǂݍ���
$aThread->readDat();

// �I�t���C���w��ł����O���Ȃ���΁A���߂ċ����ǂݍ���
if (empty($aThread->datlines) && $offline) {
    $aThread->downloadDat();
    $aThread->readDat();
}

// �^�C�g�����擾���Đݒ�
$aThread->setTitleFromLocal();

//===========================================================
// �\�����X�Ԃ͈̔͂�ݒ�
//===========================================================
if ($_conf['ktai']) {
    $before_respointer = $_conf['mobile.before_respointer'];
} else {
    $before_respointer = $_conf['before_respointer'];
}

// �擾�ς݂Ȃ�
if ($aThread->isKitoku()) {

    //�u�V�����X�̕\���v�̎��͓��ʂɂ�����ƑO�̃��X����\��
    if (!empty($_GET['nt'])) {
        if (substr($aThread->ls, -1) == '-') {
            $n = $aThread->ls - $before_respointer;
            if ($n < 1) { $n = 1; }
            $aThread->ls = $n . '-';
        }

    } elseif (!$aThread->ls) {
        $from_num = $aThread->readnum +1 - $_conf['respointer'] - $before_respointer;
        if ($from_num < 1) {
            $from_num = 1;
        } elseif ($from_num > $aThread->rescount) {
            $from_num = $aThread->rescount - $_conf['respointer'] - $before_respointer;
        }
        $aThread->ls = $from_num . '-';
    }

    if ($_conf['ktai'] && strpos($aThread->ls, 'n') === false) {
        $aThread->ls = $aThread->ls . 'n';
    }

// ���擾�Ȃ�
} else {
    if (!$aThread->ls) {
        $aThread->ls = $_conf['get_new_res_l'];
    }
}

// �t�B���^�����O�̎��́Aall�Œ�Ƃ���
if (isset($word)) {
    $aThread->ls = 'all';
}

$aThread->lsToPoint();

//===============================================================
// �v�����g
//===============================================================
$ptitle_ht = htmlspecialchars($aThread->itaj, ENT_QUOTES)." / ".$aThread->ttitle_hd;

if ($_conf['ktai']) {

    if (isset($GLOBALS['word']) && strlen($GLOBALS['word']) > 0) {
        $GLOBALS['filter_hits'] = 0;
    } else {
        $GLOBALS['filter_hits'] = NULL;
    }

    $aShowThread = new ShowThreadK($aThread);

    if ($is_ajax) {
        $response = trim(mb_convert_encoding($aShowThread->getDatToHtml(true), 'UTF-8', 'CP932'));
        if (isset($_GET['respop_id'])) {
            $response = preg_replace('/<[^<>]+? id="/u', sprintf('$0_respop%d_', $_GET['respop_id']), $response);
        }
        /*if ($_conf['iphone']) {
            // HTML�̒f�Ђ�XML�Ƃ��ēn���Ă�DOM��id��class�����Ғʂ�ɔ��f����Ȃ�
            header('Content-Type: application/xml; charset=UTF-8');
            //$responseId = 'ajaxResponse' . time();
            $doc = new DOMDocument();
            $err = error_reporting(E_ALL & ~E_WARNING);
            $html = '<html><head>'
                  . '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'
                  . '</head><body>'
                  . $response
                  . '</body></html>';
            $doc->loadHTML($html);
            error_reporting($err);
            echo '<?xml version="1.0" encoding="utf-8" ?>';
            echo $doc->saveXML($doc->getElementsByTagName('div')->item(0));
        } else {*/
            // ����āAHTML�̒f�Ђ����̂܂ܕԂ���innterHTML�ɑ�����Ȃ��Ƃ����Ȃ��B
            // (���{�I�Ƀ��X�|���X�̃t�H�[�}�b�g�ƃN���C�A���g���ł̏�����ς��Ȃ������)
            header('Content-Type: text/html; charset=UTF-8');
            echo $response;
        //}
    } else {
        $content = $aShowThread->getDatToHtml();

        require_once P2_LIB_DIR . '/read_header_k.inc.php';

        if ($_conf['iphone'] && $_conf['expack.spm.enabled']) {
            echo $aShowThread->getSpmObjJs();
        }

        echo $content;

        require_once P2_LIB_DIR . '/read_footer_k.inc.php';
    }

} else {

    // �w�b�_ �\��
    require_once P2_LIB_DIR . '/read_header.inc.php';
    flush();

    //===========================================================
    // ���[�J��Dat��ϊ�����HTML�\��
    //===========================================================
    // ���X������A�����w�肪�����
    if (isset($word) && $aThread->rescount) {

        $all = $aThread->rescount;

        $GLOBALS['filter_hits'] = 0;

        echo "<p><b id=\"filterstart\">{$all}���X�� <span id=\"searching\">{$GLOBALS['filter_hits']}</span>���X���q�b�g</b></p>\n";
        echo <<<EOP
<script type="text/javascript">
//<![CDATA[
var searching = document.getElementById('searching');
function filterCount(n){
    if (searching) {
        searching.innerHTML = n;
    }
}
//]]>
</script>
EOP;
    }

    //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection("datToHtml");

    if ($aThread->rescount) {
        $aShowThread = new ShowThreadPc($aThread);

        if ($_conf['expack.spm.enabled']) {
            echo $aShowThread->getSpmObjJs();
        }

        $res1 = $aShowThread->quoteOne(); // >>1�|�b�v�A�b�v�p
        if ($_conf['coloredid.enable'] > 0 && $_conf['coloredid.click'] > 0 &&
            $_conf['coloredid.rate.type'] > 0) {
            $mainhtml .= $aShowThread->datToHtml(true);
            $mainhtml .= $res1['q'];
        } else {
            $aShowThread->datToHtml();
            echo $res1['q'];
        }


        // ���X�ǐՃJ���[
        if ($_conf['backlink_coloring_track']) {
            echo $aShowThread->getResColorJs();
        }

        // ID�J���[�����O
        if ($_conf['coloredid.enable'] > 0 && $_conf['coloredid.click'] > 0) {
            echo $aShowThread->getIdColorJs();
            // �u���E�U���׌y���̂��߁ACSS���������X�N���v�g�̌�ŃR���e���c��
            // �����_�����O������
            echo $mainhtml;
        }
    } else if ($aThread->diedat && count($aThread->datochi_residuums) > 0) {
        require_once P2_LIB_DIR . '/ShowThreadPc.php';
        $aShowThread = new ShowThreadPc($aThread);
        echo $aShowThread->getDatochiResiduums();
    }

    //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection("datToHtml");

    //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection("datToHtml");

    // �t�B���^���ʂ�\��
    if ($word && $aThread->rescount) {
        echo <<<EOP
<script type="text/javascript">
//<![CDATA[
var filterstart = document.getElementById('filterstart');
if (filterstart) {
    filterstart.style.backgroundColor = 'yellow';
    filterstart.style.fontWeight = 'bold';
}
//]]>
</script>\n
EOP;
        if ($GLOBALS['filter_hits'] > 5) {
            echo "<p><b class=\"filtering\">{$all}���X�� {$GLOBALS['filter_hits']}���X���q�b�g</b></p>\n";
        }
    }

    // �t�b�^ �\��
    require_once P2_LIB_DIR . '/read_footer.inc.php';

}
flush();

//===========================================================
// idx�̒l��ݒ�A�L�^
//===========================================================
if ($aThread->rescount) {

    // �����̎��́A���ǐ����X�V���Ȃ�
    if ((isset($GLOBALS['word']) && strlen($GLOBALS['word']) > 0) || $is_ajax) {
        $aThread->readnum = $idx_data[5];
    } else {
        $aThread->readnum = min($aThread->rescount, max(0, $idx_data[5], $aThread->resrange['to']));
    }
    $newline = $aThread->readnum + 1; // $newline�͔p�~�\�肾���A���݊��p�ɔO�̂���

    $sar = array($aThread->ttitle, $aThread->key, $idx_data[2], $aThread->rescount, '',
                $aThread->readnum, $idx_data[6], $idx_data[7], $idx_data[8], $newline,
                $idx_data[10], $idx_data[11], $aThread->datochiok);
    P2Util::recKeyIdx($aThread->keyidx, $sar); // key.idx�ɋL�^
}

//===========================================================
// �������L�^
//===========================================================
if ($aThread->rescount && !$is_ajax) {
    $newdata = "{$aThread->ttitle}<>{$aThread->key}<>$idx_data[2]<><><>{$aThread->readnum}<>$idx_data[6]<>$idx_data[7]<>$idx_data[8]<>{$newline}<>{$aThread->host}<>{$aThread->bbs}";
    recRecent($newdata);
}

// NG���ځ[����L�^
NgAbornCtl::saveNgAborns();

// �ȏ� ---------------------------------------------------------------
exit;

//===============================================================================
// �֐�
//===============================================================================
// {{{ detectThread()

/**
 * �X���b�h���w�肷��
 */
function detectThread()
{
    global $_conf, $host, $bbs, $key, $ls;

    list($nama_url, $host, $bbs, $key, $ls) = P2Util::detectThread();

    if (!($host && $bbs && $key)) {
        if ($nama_url) {
            $nama_url = htmlspecialchars($nama_url, ENT_QUOTES);
            p2die('�X���b�h�̎w�肪�ςł��B', "<a href=\"{$nama_url}\">{$nama_url}</a>", true);
        } else {
            p2die('�X���b�h�̎w�肪�ςł��B');
        }
    }
}

// }}}
// {{{ recRecent()

/**
 * �������L�^����
 */
function recRecent($data)
{
    global $_conf;

    $lock = new P2Lock($_conf['recent_idx'], false);

    // $_conf['recent_idx'] �t�@�C�����Ȃ���ΐ���
    FileCtl::make_datafile($_conf['recent_idx'], $_conf['rct_perm']);

    $lines = FileCtl::file_read_lines($_conf['recent_idx'], FILE_IGNORE_NEW_LINES);
    $neolines = array();

    // {{{ �ŏ��ɏd���v�f���폜���Ă���

    if (is_array($lines)) {
        foreach ($lines as $l) {
            $lar = explode('<>', $l);
            $data_ar = explode('<>', $data);
            if ($lar[1] == $data_ar[1]) { continue; } // key�ŏd�����
            if (!$lar[1]) { continue; } // key�̂Ȃ����͕̂s���f�[�^
            $neolines[] = $l;
        }
    }

    // }}}

    // �V�K�f�[�^�ǉ�
    array_unshift($neolines, $data);

    while (sizeof($neolines) > $_conf['rct_rec_num']) {
        array_pop($neolines);
    }

    // {{{ ��������

    if ($neolines) {
        $cont = '';
        foreach ($neolines as $l) {
            $cont .= $l . "\n";
        }

        if (FileCtl::file_write_contents($_conf['recent_idx'], $cont) === false) {
            p2die('cannot write file.');
        }
    }

    // }}}

    return true;
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
