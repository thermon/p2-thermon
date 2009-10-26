<?php
/**
 * rep2 - �X���b�h�T�u�W�F�N�g�\���X�N���v�g
 * �t���[��������ʁA�E�㕔��
 *
 * lib/subject_new.inc.php �ƌZ��Ȃ̂ŁA�ꏏ�ɖʓ|���݂邱��
 */

require_once './conf/conf.inc.php';
require_once P2_LIB_DIR . '/Thread.php';
require_once P2_LIB_DIR . '/ThreadList.php';
// +Wiki
require_once P2_LIB_DIR . '/wiki/subject.inc.php';

//$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('HEAD');

$_login->authorize(); // ���[�U�F��

//============================================================
// �ϐ��ݒ�
//============================================================
$newtime = date('gis');
$nowtime = time();

$abornoff_st = '���ځ[�����';
$deletelog_st = '���O���폜';

$kitoku_only = false;
$online_num = 0;
$shinchaku_num = 0;
$shinchaku_attayo = false;

$sb_disp_from = !empty($_REQUEST['from']) ? $_REQUEST['from'] : 1;

// {{{ �z�X�g�A�A���[�h�ݒ�

$host   = isset($_REQUEST['host'])   ? $_REQUEST['host']   : null;
$bbs    = isset($_REQUEST['bbs'])    ? $_REQUEST['bbs']    : null;
$spmode = isset($_REQUEST['spmode']) ? $_REQUEST['spmode'] : null;

if (!($host && $bbs) && !$spmode) {
    p2die('�K�v�Ȉ������w�肳��Ă��܂���');
}

// }}}
// {{{ p2_setting, sb_keys �ݒ�

if ($spmode) {
    if ($_conf['expack.misc.multi_favs'] && ($spmode == 'fav' || $spmode == 'merge_favita')) {
        $favset_key = ($spmode == 'fav') ? 'm_favlist_set' : 'm_favita_set';
        $favset_suffix = (empty($_conf[$favset_key])) ? '' : $_conf[$favset_key];
        $p2_setting_txt = $_conf['pref_dir'] . '/p2_setting_' . $spmode . $favset_suffix . '.txt';
    } else {
        $p2_setting_txt = $_conf['pref_dir'] . '/p2_setting_' . $spmode . '.txt';
    }
} else {
    $idx_host_bbs_dir_s = P2Util::idxDirOfHostBbs($host, $bbs);

    $p2_setting_txt = $idx_host_bbs_dir_s . 'p2_setting.txt';
    $sb_keys_b_txt =  $idx_host_bbs_dir_s . 'p2_sb_keys_b.txt';
    $sb_keys_txt =    $idx_host_bbs_dir_s . 'p2_sb_keys.txt';

    $pre_subject_keys = getSubjectKeys($sb_keys_txt, $sb_keys_b_txt);
    $subject_keys = array();
}

// }}}
// {{{ p2_setting �ǂݍ��݁A�Z�b�g

$p2_setting = array('viewnum' => null, 'sort' => null, 'itaj' => null);
if ($p2_setting_cont = FileCtl::file_read_contents($p2_setting_txt)) {
    $p2_setting = array_merge($p2_setting, unserialize($p2_setting_cont));
}

$pre_setting['viewnum'] = isset($p2_setting['viewnum']) ? $p2_setting['viewnum'] : null;
$pre_setting['sort']    = isset($p2_setting['sort'])    ? $p2_setting['sort']    : null;
$pre_setting['itaj']    = isset($p2_setting['itaj'])    ? $p2_setting['itaj']    : null;

$sb_view = !empty($_REQUEST['sb_view']) ? $_REQUEST['sb_view'] : 'normal';

if (!empty($_REQUEST['viewnum'])) {
    $p2_setting['viewnum'] = $_REQUEST['viewnum'];
} elseif (!$p2_setting['viewnum']) {
    $p2_setting['viewnum'] = $_conf['display_threads_num']; // �f�t�H���g�l
}

if (isset($_GET['itaj_en'])) {
    $p2_setting['itaj'] = base64_decode($_GET['itaj_en']);
}

// }}}
// {{{ �\�[�g�̎w��

if (!empty($_REQUEST['sort'])) {
    $now_sort = $_REQUEST['sort'];
} else {
    if ($p2_setting['sort']) {
        $now_sort = $p2_setting['sort'];
    } else {
        if (!$spmode) {
            $now_sort = !empty($_conf['sb_sort_ita']) ? $_conf['sb_sort_ita'] : 'ikioi'; // ����
        } else {
            $now_sort = 'midoku'; // �V��
        }
    }
}

// }}}
// {{{ �\���X���b�h���ݒ�

$threads_num_max = 2000;

if (!$spmode || $spmode == 'merge_favita') {
    $threads_num = $p2_setting['viewnum'];
} elseif ($spmode == 'recent') {
    $threads_num = $_conf['rct_rec_num'];
} elseif ($spmode == 'res_hist') {
    $threads_num = $_conf['res_hist_rec_num'];
} else {
    $threads_num = 2000;
}

if ($p2_setting['viewnum'] == 'all' or $sb_view == 'shinchaku' or $sb_view == 'edit' or isset($_GET['word']) or $_conf['ktai']) {
    $threads_num = $threads_num_max;
}

// }}}
// {{{ ���[�h�t�B���^�ݒ�

$word = '';
$do_filtering = false;
$GLOBALS['sb_mikke_num'] = 0;

// �f�t�H���g�I�v�V����, $sb_filter �� global @see sb_print.inc.php
$sb_filter = array('method' => 'and');

// �����w�肪�����
if (empty($_REQUEST['submit_refresh']) or !empty($_REQUEST['submit_kensaku'])) {
    if (isset($_GET['word'])) {
        $GLOBALS['word'] = $_GET['word'];
    } elseif (isset($_POST['word'])) {
        $GLOBALS['word'] = $_POST['word'];
    }


    if (isset($_GET['method'])) {
        $sb_filter['method'] = $_GET['method'];
    } elseif (isset($_POST['method'])) {
        $sb_filter['method'] = $_POST['method'];
    }

    if ($sb_filter['method'] == 'similar') {
        $GLOBALS['wakati_word'] = $GLOBALS['word'];
        $GLOBALS['wakati_words'] = p2_wakati($GLOBALS['word']);
        if (!$GLOBALS['wakati_words']) {
            unset($GLOBALS['wakati_word'], $GLOBALS['wakati_words']);
        } else {
            $GLOBALS['wakati_hl_regex'] = p2_get_highlighting_regex($GLOBALS['wakati_words']);
            $GLOBALS['wakati_length'] = mb_strlen($GLOBALS['wakati_word'], 'CP932');
            $GLOBALS['wakati_score'] = getSbScore($GLOBALS['wakati_words'], $GLOBALS['wakati_length']);
            if (!isset($_conf['expack.min_similarity'])) {
                $_conf['expack.min_similarity'] = 0.05;
            } elseif ($_conf['expack.min_similarity'] > 1) {
                $_conf['expack.min_similarity'] /= 100;
            }
            $_conf['expack.min_similarity'] = (float) $_conf['expack.min_similarity'];
        }
        $word = '';
    } elseif (substr_count($word, '.') == strlen($word)) {
        $word = '';
    }

    if (strlen($word) > 0)  {
        if (p2_set_filtering_word($word, $sb_filter['method']) !== null) {
            $do_filtering = true;
        }
    }
}

// }}}

//============================================================
// ����ȑO����
//============================================================
// {{{ �폜

if (!empty($_GET['dele']) || (isset($_POST['submit']) && $_POST['submit'] == $deletelog_st)) {
    if ($host && $bbs) {
        require_once P2_LIB_DIR . '/dele.inc.php';
        if ($_POST['checkedkeys']) {
            $dele_keys = $_POST['checkedkeys'];
        } else {
            $dele_keys = array($_GET['key']);
        }
        deleteLogs($host, $bbs, $dele_keys);
    }

// }}}

// ���C�ɓ���X���b�h
} elseif (isset($_GET['setfav']) && !empty($_GET['key']) && $host && $bbs) {
    require_once P2_LIB_DIR . '/setfav.inc.php';
    setFav($host, $bbs, $_GET['key'], $_GET['setfav'],
           isset($_GET['ttitle_en']) ? base64_decode($_GET['ttitle_en']) : null);

// �a������
} elseif (isset($_GET['setpal']) && $_GET['key'] && $host && $bbs) {
    require_once P2_LIB_DIR . '/setpalace.inc.php';
    setPal($host, $bbs, $_GET['key'], $_GET['setpal']);

// ���ځ[��X���b�h����
} elseif ((isset($_POST['submit']) && $_POST['submit'] == $abornoff_st) && $host && $bbs && $_POST['checkedkeys']) {
    require_once P2_LIB_DIR . '/settaborn_off.inc.php';
    settaborn_off($host, $bbs, $_POST['checkedkeys']);

// �X���b�h���ځ[��
} elseif (isset($_GET['taborn']) && !is_null($_GET['key']) && $host && $bbs) {
    require_once P2_LIB_DIR . '/settaborn.inc.php';
    settaborn($host, $bbs, $_GET['key'], $_GET['taborn']);
}

// ���C�ɔ��}�[�W
if ($spmode == 'merge_favita') {
    $favitas = array();
    $pre_subject_keys = array();
    $subject_keys = array();
    $sb_key_txts = array();

    if (file_exists($_conf['favita_brd'])) {
        foreach (file($_conf['favita_brd']) as $l) {
            if (preg_match("/^\t?(.+?)\t(.+?)\t.+?\$/", rtrim($l), $matches)) {
                $_host = $matches[1];
                $_bbs  = $matches[2];
                $_id   = $_host . '/' . $_bbs;

                $_idx_host_bbs_dir_s = P2Util::idxDirOfHostBbs($_host, $_bbs);
                $_sb_keys_txt   = $_idx_host_bbs_dir_s . 'p2_sb_keys.txt';
                $_sb_keys_txt_a = $_idx_host_bbs_dir_s . 'p2_sb_keys_m.txt';
                $_sb_keys_txt_b = $_idx_host_bbs_dir_s . 'p2_sb_keys_m_b.txt';

                $favitas[$_id] = array('host' => $_host, 'bbs' => $_bbs);
                $pre_subject_keys[$_id] = getSubjectKeys($_sb_keys_txt, $_sb_keys_txt);
                foreach (getSubjectKeys($_sb_keys_txt_a, $_sb_keys_txt_b) as $_key => $_value) {
                    $pre_subject_keys[$_id][$_key] = $_value;
                }
                $subject_keys[$_id] = array();
                $sb_key_txts[$_id] = array($_sb_keys_txt_a, $_sb_keys_txt_b);
            }
        }
    }

    if ($_conf['merge_favita'] == 2) {
        $kitoku_only = true;
    }
}

//============================================================
// �X�V����ꍇ�A�O�����Ĉꊇ������_�E�����[�h (�vpecl_http)
//============================================================

if (empty($_REQUEST['norefresh']) && !(empty($_REQUEST['refresh']) && isset($_REQUEST['word']))) {
    if ($_conf['expack.use_pecl_http'] == 1) {
        require_once P2_LIB_DIR . '/P2HttpExt.php';
        switch ($spmode) {
        case 'fav':
            P2HttpRequestPool::fetchSubjectTxt($_conf['favlist_idx']);
            $GLOBALS['expack.subject.multi-threaded-download.done'] = true;
            break;
        case 'recent':
            P2HttpRequestPool::fetchSubjectTxt($_conf['recent_idx']);
            $GLOBALS['expack.subject.multi-threaded-download.done'] = true;
            break;
        case 'res_hist':
            P2HttpRequestPool::fetchSubjectTxt($_conf['res_hist_idx']);
            $GLOBALS['expack.subject.multi-threaded-download.done'] = true;
            break;
        case 'merge_favita':
            P2HttpRequestPool::fetchSubjectTxt($favitas);
            $GLOBALS['expack.subject.multi-threaded-download.done'] = true;
            break;
        }
    } elseif ($_conf['expack.use_pecl_http'] == 2) {
        require_once P2_CLI_DIR . '/P2CommandRunner.php';
        if (P2CommandRunner::fetchSubjectTxt($spmode, $_conf)) {
            $GLOBALS['expack.subject.multi-threaded-download.done'] = true;
        }
    }
}

//============================================================
// ���C��
//============================================================

$aThreadList = new ThreadList();

// {{{ �ƃ��[�h�̃Z�b�g

$spmode_without_palace_or_favita = false;
$ta_keys = array();
$ta_num = 0;

if ($spmode) {
    if ($spmode == 'taborn' or $spmode == 'soko') {
        $aThreadList->setIta($host, $bbs, P2Util::getItaName($host, $bbs));
    }

    if ($spmode != 'palace' && $spmode != 'merge_favita') {
        $spmode_without_palace_or_favita = true;
    }

    $aThreadList->setSpMode($spmode);
} else {
    // if(!$p2_setting['itaj']){$p2_setting['itaj'] = P2Util::getItaName($host, $bbs);}
    $aThreadList->setIta($host, $bbs, $p2_setting['itaj']);

    // �X���b�h���ځ[�񃊃X�g�Ǎ�
    $taborn_file = $aThreadList->getIdxDir() . 'p2_threads_aborn.idx';
    if ($tabornlines = FileCtl::file_read_lines($taborn_file, FILE_IGNORE_NEW_LINES)) {
        $ta_num = sizeof($tabornlines);
        foreach ($tabornlines as $l) {
            $data = explode('<>', $l);
            $ta_keys[ $data[1] ] = true;
        }
    }
}

// }}}

// �\�[�X���X�g�Ǎ�
$lines = $aThreadList->readList();

// {{{ ���C�ɃX�����X�g �Ǎ�
if ($favlines = FileCtl::file_read_lines($_conf['favlist_idx'], FILE_IGNORE_NEW_LINES)) {
    foreach ($favlines as $l) {
        $data = explode('<>', $l);
        $fav_keys[ $data[1] ] = $data[11];
    }
}
// }}}

//$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('HEAD');

//============================================================
// ���ꂼ��̍s���
//============================================================
//$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('FORLOOP');

$linesize = sizeof($lines);
$subject_txts = array();

for ($x = 0; $x < $linesize; $x++) {
    $aThread = new Thread();

    if ($aThreadList->spmode == 'merge_favita') {
        $l = $lines[$x];
    } else {
        $l = rtrim($lines[$x]);
        if ($aThreadList->spmode != 'soko' && $aThreadList->spmode != 'taborn') {
            $aThread->torder = $x + 1;
        }
    }

    // �f�[�^�ǂݍ���
    // spmode
    if ($aThreadList->spmode) {
        switch ($aThreadList->spmode) {
        case "recent":  // ����
            $aThread->getThreadInfoFromExtIdxLine($l);
            $aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
            if (!$aThread->itaj) {$aThread->itaj = $aThread->bbs;}
            break;
        case "res_hist":    // �������ݗ���
            $aThread->getThreadInfoFromExtIdxLine($l);
            $aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
            if (!$aThread->itaj) {$aThread->itaj= $aThread->bbs;}
            break;
        case "fav":     // ���C��
            $aThread->getThreadInfoFromExtIdxLine($l);
            $aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
            if (!$aThread->itaj) {$aThread->itaj = $aThread->bbs;}
            break;
        case "taborn":  // �X���b�h���ځ[��
            $la = explode('<>', $l);
            $aThread->key = $la[1];
            $aThread->host = $aThreadList->host;
            $aThread->bbs = $aThreadList->bbs;
            break;
        case "soko":    // dat�q��
            $la = explode('<>', $l);
            $aThread->key = $la[1];
            $aThread->host = $aThreadList->host;
            $aThread->bbs = $aThreadList->bbs;
            break;
        case "palace":  // �X���̓a��
            $aThread->getThreadInfoFromExtIdxLine($l);
            $aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
            if (!$aThread->itaj) {$aThread->itaj = $aThread->bbs;}
            break;
        case "merge_favita": // ���C�ɔ��}�[�W
            $aThread->isonline = true;
            $aThread->key = $l['key'];
            $aThread->setTtitle($l['ttitle']);
            $aThread->rescount = $l['rescount'];
            $aThread->host = $l['host'];
            $aThread->bbs = $l['bbs'];
            $aThread->torder = $l['torder'];

            $aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
            if (!$aThread->itaj) {$aThread->itaj = $aThread->bbs;}
            break;
        }

    // subject (not spmode �܂蕁�ʂ̔�)
    } else {
        $aThread->getThreadInfoFromSubjectTxtLine($l);

        $aThread->host = $aThreadList->host;
        $aThread->bbs = $aThreadList->bbs;
    }

    // �������ߖ�i����merge_favita�j�̂���
    $lines[$x] = null;

    // host��bbs��key���s���Ȃ�X�L�b�v
    if (!($aThread->host && $aThread->bbs && $aThread->key)) {
        unset($aThread);
        continue;
    }

    $subject_id = $aThread->host . '/' . $aThread->bbs;

    // �����ň�U�X���b�h���X�g�ɂ܂Ƃ߂āA�L���b�V���������悤���Ǝv�������A����������(750K��2M)�������������̂ł�߂Ă������B


    // {{{ �V�������ǂ���(for subject)

    if (!$aThreadList->spmode) {
        if (!isset($pre_subject_keys[$aThread->key])) {
            $aThread->new = true;
        }
        $subject_keys[$aThread->key] = true;
    } elseif ($aThreadList->spmode == 'merge_favita') {
        if (!isset($pre_subject_keys[$subject_id][$aThread->key])) {
            $aThread->new = true;
        }
        $subject_keys[$subject_id][$aThread->key] = true;
    }

    // }}}
    // {{{ �����[�h�t�B���^(for subject)

    //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('word_filter_for_sb');
    if ($do_filtering && !$spmode_without_palace_or_favita) {

        $aThread->setThreadPathInfo($aThread->host, $aThread->bbs, $aThread->key);

        // �}�b�`���Ȃ���΃X�L�b�v
        if (!matchSbFilter($aThread)) {
            unset($aThread);
            //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('word_filter_for_sb');
            continue;

        // �}�b�`������
        } else {
            $GLOBALS['sb_mikke_num']++;
            if ($_conf['ktai']) {
                if (is_string($_conf['k_filter_marker'])) {
                    $aThread->ttitle_ht = StrCtl::filterMarking($GLOBALS['word_fm'], $aThread->ttitle_hd, $_conf['k_filter_marker']);
                } else {
                    $aThread->ttitle_ht = $aThread->ttitle_hd;
                }
            } else {
                $aThread->ttitle_ht = StrCtl::filterMarking($GLOBALS['word_fm'], $aThread->ttitle_hd);
            }
        }
    } elseif (!$aThreadList->spmode && !empty($GLOBALS['wakati_words'])) {
        // �ގ��X������
        if (!setSbSimilarity($aThread) || $aThread->similarity < $_conf['expack.min_similarity']) {
            unset($aThread);
            //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('word_filter_for_sb');
            continue;
        }
        if ($_conf['ktai']) {
            if (is_string($_conf['k_filter_marker'])) {
                $aThread->ttitle_ht = StrCtl::filterMarking($GLOBALS['wakati_hl_regex'], $aThread->ttitle_ht, $_conf['k_filter_marker']);
            }
        } else {
            $aThread->ttitle_ht = StrCtl::filterMarking($GLOBALS['wakati_hl_regex'], $aThread->ttitle_ht);
        }
    }
    //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('word_filter_for_sb');

    // }}}
    // {{{ ���X���b�h���ځ[��`�F�b�N

    //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('taborn_check_continue');
    if ($aThreadList->spmode != "taborn" && !empty($ta_keys[$aThread->key])) {
        unset($ta_keys[$aThread->key]);
        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('taborn_check_continue');
        continue; // ���ځ[��X���̓X�L�b�v
    }
    //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('taborn_check_continue');

    // }}}

    $aThread->setThreadPathInfo($aThread->host, $aThread->bbs, $aThread->key);

    // �����X���b�h�f�[�^��idx����擾
    $aThread->getThreadInfoFromIdx();

    // {{{ ��+Wiki:NG�X���b�h�`�F�b�N
    if (isset($ngaborns)) if ($ngaborns->check($aThread)) continue;
    // }}}

    if ($kitoku_only && !$aThread->isKitoku()) {
        unset($aThread);
        if ($do_filtering) {
            $GLOBALS['sb_mikke_num']--;
        }
        continue;
    }

    // {{{ �� favlist�`�F�b�N

    //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('favlist_check');
    // if ($x <= $threads_num) {
        if ($aThreadList->spmode != 'taborn' and isset($fav_keys[$aThread->key]) && $fav_keys[$aThread->key] == $aThread->bbs) {
            $aThread->fav = 1;
            unset($fav_keys[$aThread->key]);
        }
    // }
    //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('favlist_check');

    // }}}

    //  spmode(�a������Amerge_favita������)�Ȃ� ====================================
    if ($spmode_without_palace_or_favita && $sb_view != 'edit') {

        //  subject.txt ����DL�Ȃ痎�Ƃ��ăf�[�^��z��Ɋi�[
        if (!isset($subject_txts[$subject_id])) {
            $subject_txts[$subject_id] = array();

            require_once P2_LIB_DIR . '/SubjectTxt.php';
            $aSubjectTxt = new SubjectTxt($aThread->host, $aThread->bbs);

            //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('subthre_read');
            if ($aThreadList->spmode == 'soko' or $aThreadList->spmode == 'taborn') {

                if (is_array($aSubjectTxt->subject_lines)) {
                    $it = 1;
                    foreach ($aSubjectTxt->subject_lines as $asbl) {
                        if (preg_match("/^([0-9]+)\.(?:dat|cgi)(?:,|<>)(.+) ?(?:\(|�i)([0-9]+)(?:\)|�j)/", $asbl, $matches)) {
                            $akey = $matches[1];
                            $subject_txts[$subject_id][$akey] = array(
                                'ttitle' => rtrim($matches[2]),
                                'rescount' => (int)$matches[3],
                                'torder' => $it,
                            );
                        }
                        $it++;
                    }
                }

            } else {
                $subject_txts[$subject_id] = $aSubjectTxt->subject_lines;

            }
            //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('subthre_read');
        }

        //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('subthre_check');
        // �X�����擾 =============================
        if ($aThreadList->spmode == "soko" or $aThreadList->spmode == "taborn") {

            if (isset($subject_txts[$subject_id][$aThread->key])) {

                // �q�ɂ̓I�����C�����܂܂Ȃ�
                if ($aThreadList->spmode == "soko") {
                    unset($aThread);
                    continue;
                } elseif ($aThreadList->spmode == "taborn") {
                    // $aThread->getThreadInfoFromSubjectTxtLine($l); // subject.txt ����X�����擾
                    $aThread->isonline = true;
                    $ttitle = $subject_txts[$subject_id][$aThread->key]['ttitle'];
                    $aThread->setTtitle($ttitle);
                    $aThread->rescount = $subject_txts[$subject_id][$aThread->key]['rescount'];
                    if ($aThread->readnum) {
                        $aThread->unum = $aThread->rescount - $aThread->readnum;
                        // machi bbs ��sage��subject�̍X�V���s���Ȃ������Ȃ̂Œ������Ă���
                        if ($aThread->unum < 0) { $aThread->unum = 0; }
                        $aThread->nunum = $aThread->unum;
                    }
                    $aThread->torder = $subject_txts[$subject_id][$aThread->key]['torder'];
                }

            }

        } else {

            if (isset($subject_txts[$subject_id])) {
                $it = 1;
                $thread_key = (string)$aThread->key;
                $thread_key_len = strlen($thread_key);
                foreach ($subject_txts[$subject_id] as $l) {
                    if (strncmp($l, $thread_key, $thread_key_len) == 0) {
                        // subject.txt ����X�����擾
                        $aThread->getThreadInfoFromSubjectTxtLine($l);
                        break;
                    }
                    $it++;
                }
            }

        }
        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('subthre_check');

        if ($aThreadList->spmode == "taborn") {
            if (!$aThread->torder) { $aThread->torder = '-'; }
        }


        // {{{ ���V���̂�(for spmode)

        if ($sb_view == 'shinchaku' and !isset($_REQUEST['word'])) {
            if ($aThread->unum < 1) {
                unset($aThread);
                continue;
            }
        }

        // }}}
        // {{{ �����[�h�t�B���^(for spmode)

        if ($do_filtering) {

            // �}�b�`���Ȃ���΃X�L�b�v
            if (!matchSbFilter($aThread)) {
                unset($aThread);
                continue;

            // �}�b�`������
            } else {
                $GLOBALS['sb_mikke_num']++;
                if ($_conf['ktai']) {
                    $aThread->ttitle_ht = $aThread->ttitle_hd;
                } else {
                    $aThread->ttitle_ht = StrCtl::filterMarking($GLOBALS['word_fm'], $aThread->ttitle_hd);
                }
            }
        }

        // }}}
    }

    //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('FORLOOP_HIP');

    // subjexct����rescount�����Ȃ������ꍇ�́Agotnum�𗘗p����B
    if ((!$aThread->rescount) and $aThread->gotnum) {
        $aThread->rescount = $aThread->gotnum;
    }

    // �}�[�L���O���̏��������Ȃ��ꍇ ttitle_hc, ttitle_hd, ttitle_ht ��JIT�Őݒ肳���
    //if (!$aThread->ttitle_ht) { $aThread->ttitle_ht = $aThread->ttitle_hd; }

    // �V������
    if ($aThread->unum > 0) {
        $shinchaku_attayo = true;
        $shinchaku_num = $shinchaku_num + $aThread->unum; // �V����set

    /*
    // ���C�ɃX��
    } elseif ($aThread->fav) {
        ;

    // �V�K�X��
    } elseif ($aThread->new) {
        ;
    */

    }

    // {{{ �V���\�[�g�̕֋X�� �i���擾�X���b�h�́junum ���Z�b�g����

    if (!isset($aThread->unum)) {
        if ($aThreadList->spmode == "recent" or $aThreadList->spmode == "res_hist" or $aThreadList->spmode == "taborn") {
            $aThread->unum = -0.1;
        } else {
            $aThread->unum = $_conf['sort_zero_adjust'];
        }
    }

    // }}}

    // �����̃Z�b�g
    $aThread->setDayRes($nowtime);

    // ������set
    if ($aThread->isonline) { $online_num++; }

    // ���X�g�ɒǉ�
    $aThreadList->addThread($aThread);

    unset($aThread);

    //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('FORLOOP_HIP');
}

unset($lines);

//$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('FORLOOP');

//$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('FOOT');

// ����dat�������Ă���X���͎����I�ɂ��ځ[�����������
autoTAbornOff($aThreadList, $ta_keys);

// �\�[�g
if (!empty($GLOBALS['wakati_words'])) {
    $now_sort = 'title';
    $sort_mode = 'similarity';
} else {
    $sort_mode = $now_sort;
}
$aThreadList->sort($sort_mode, !empty($_REQUEST['rsort']));

// �\�[�g��A���C�ɔ̊����X��idx���쐬 (�V���܂Ƃߓǂ݂̌�����ǂ����邽�߂̃L���b�V��)
if ($spmode == 'merge_favita') {
    if ($_conf['expack.misc.multi_favs'] && !empty($_conf['m_favita_set'])) {
        $merged_faivta_read_idx = $_conf['pref_dir'] . '/p2_favita' . $_conf['m_favita_set'] . '_read.idx';
    } else {
        $merged_faivta_read_idx = $_conf['pref_dir'] . '/p2_favita_read.idx';
    }

    FileCtl::make_datafile($merged_faivta_read_idx, $_conf['p2_perm']);
    $fp = fopen($merged_faivta_read_idx, 'wb');
    if (!$fp || !flock($fp, LOCK_EX)) {
        p2die("cannot write file {$merged_faivta_read_idx}.");
    }

    foreach ($aThreadList->threads as $aThread) {
        if ($aThread->isKitoku()) {
            fwrite($fp,
                   sprintf("%s<>%d<><><><>%d<><><><>%d<>%s<>%s\n",
                           $aThread->ttitle,
                           $aThread->key,
                           $aThread->readnum,
                           $aThread->readnum + 1, // newline ���݊��̂���
                           $aThread->host,
                           $aThread->bbs
                           )
                   );
        }
    }

    flock($fp, LOCK_UN);
    fclose($fp);
}

//===============================================================
// �v�����g
//===============================================================
// �g��
if ($_conf['ktai']) {

    // {{{ �q�ɂ�torder�t�^

    if ($aThreadList->spmode == "soko") {
        if ($aThreadList->threads) {
            $soko_torder = 1;
            foreach ($aThreadList->threads as $at) {
                $at->torder = $soko_torder++;
            }
        }
    }

    // }}}
    // {{{ �\��������

    // �O�̂��߁A�␳���Ă���
    $aThreadList->num = count($aThreadList->threads);
    $sb_disp_all_num = $aThreadList->num;

    $disp_navi = P2Util::getListNaviRange($sb_disp_from , $_conf['mobile.sb_disp_range'], $sb_disp_all_num);
    if ($aThreadList->threads) {
        $aThreadList->threads = array_slice($aThreadList->threads, $disp_navi['offset'], $disp_navi['limit']);
    }
    $aThreadList->num = sizeof($aThreadList->threads);

    // }}}

    // �w�b�_�v�����g
    require_once P2_LIB_DIR . '/sb_header_k.inc.php';

    // ���C���v�����g
    require_once P2_LIB_DIR . '/sb_print_k.inc.php';
    sb_print_k($aThreadList);

    // �t�b�^�v�����g
    require_once P2_LIB_DIR . '/sb_footer_k.inc.php';

// PC
} else {
    // {{{ �\��������

    // �O�̂��߁A�␳���Ă���
    $aThreadList->num = count($aThreadList->threads);
    $threads_num = max(1, (int)$threads_num);

    if ($_conf['viewall_kitoku']) {
        if (!$kitoku_only) {
            $read_threads = array();

            while ($aThreadList->num > $threads_num) {
                $x = --$aThreadList->num;
                if ($aThreadList->threads[$x]->isKitoku()) {
                    $read_threads[] = $aThreadList->threads[$x];
                }
                unset($aThreadList->threads[$x]);
            }

            foreach ($read_threads as $aThread) {
                $aThreadList->threads[] = $aThread;
            }

            unset($read_threads);
        }
    } else {
        while ($aThreadList->num > $threads_num) {
            unset($aThreadList->threads[--$aThreadList->num]);
        }
    }

    // }}}

    // �w�b�_HTML��\��
    //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('sb_header');
    require_once P2_LIB_DIR . '/sb_header.inc.php';
    flush();
    //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('sb_header');

    // �X���b�h�T�u�W�F�N�g���C������HTML�\��
    require_once P2_LIB_DIR . '/sb_print.inc.php';
    sb_print($aThreadList);

    // �t�b�^HTML�\��
    //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('sb_footer');
    require_once P2_LIB_DIR . '/sb_footer.inc.php';
    //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('sb_footer');
}

//==============================================================
// �㏈��
//==============================================================

// p2_setting�isb�ݒ�j �L�^
saveSbSetting($p2_setting_txt, $p2_setting, $pre_setting);

// $subject_keys ���V���A���C�Y���ĕۑ�����
if (!$spmode) {
    saveSubjectKeys($subject_keys, $sb_keys_txt, $sb_keys_b_txt);
} elseif ($spmode == 'merge_favita') {
    foreach ($sb_key_txts as $id => $txts) {
        saveSubjectKeys($subject_keys[$id], $txts[0], $txts[1]);
    }
}

//$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('FOOT');

// +Wiki:NG�X���b�h
//if (isset($ngaborns)) $ngaborns->save();

// �����܂�
exit;

// {{{ �֐�
// {{{ autoTAbornOff()

/**
 * ����dat�������Ă���X���͎����I�ɂ��ځ[�����������
 * $ta_keys �͂��ځ[�񃊃X�g�ɓ����Ă�������ǁA���ځ[�񂳂ꂸ�Ɏc�����X������
 */
function autoTAbornOff($aThreadList, $ta_keys)
{
    global $_info_msg_ht;

    //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('abornoff');

    if (!$aThreadList->spmode && !empty($GLOBALS['word']) && !empty($GLOBALS['wakati_word']) && $aThreadList->threads && $ta_keys) {
        require_once P2_LIB_DIR . '/settaborn_off.inc.php';
        // echo sizeof($ta_keys)."*<br>";
        $ta_vkeys = array_keys($ta_keys);
        settaborn_off($aThreadList->host, $aThreadList->bbs, $ta_vkeys);
        foreach ($ta_vkeys as $k) {
            $ta_num--;
            if ($k) {
                $ks .= "key:$k ";
            }
        }
        $ks && $_info_msg_ht .= "<div class=\"info\">�@p2 info: DAT���������X���b�h���ځ[��������������܂��� - $ks</div>";
    }

    //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('abornoff');

    return true;
}

// }}}
// {{{ saveSbSetting()

/**
 * p2_setting �L�^����
 */
function saveSbSetting($p2_setting_txt, $p2_setting, $pre_setting)
{
    global $_conf;

    //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('save_p2_setting');
    if ($pre_setting['viewnum'] != $p2_setting['viewnum'] or $pre_setting['sort'] != $GLOBALS['now_sort'] or $pre_setting['itaj'] != $p2_setting['itaj']) {
        if (!empty($_POST['sort'])) {
            $p2_setting['sort'] = $_POST['sort'];
        } elseif (!empty($_GET['sort'])) {
            $p2_setting['sort'] = $_GET['sort'];
        }
        FileCtl::make_datafile($p2_setting_txt, $_conf['p2_perm']);
        if ($p2_setting) {
            if ($p2_setting_cont = serialize($p2_setting)) {
                if (FileCtl::file_write_contents($p2_setting_txt, $p2_setting_cont) === false) {
                    p2die('cannot write file.');
                }
            }
        }
    }
    //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('save_p2_setting');

    return true;
}

// }}}
// {{{ getSubjectKeys()

/**
 * $subject_keys ���擾����
 */
function getSubjectKeys($sb_keys_txt, $sb_keys_b_txt)
{
    // �X�V���Ȃ��ꍇ�́A2�O�̂ƂP�O�̂��ׂāA�V�K�X���𒲂ׂ�
    if (!empty($_REQUEST['norefresh']) || (empty($_REQUEST['refresh']) && isset($_REQUEST['word']))) {
        $file = $sb_keys_b_txt;
    } else {
        $file = $sb_keys_txt;
    }

    if (file_exists($file) && $cont = FileCtl::file_read_contents($file)) {
        if (is_array($subject_keys = @unserialize($cont))) {
            return $subject_keys;
        }
    }
    return array();
}

// }}}
// {{{ saveSubjectKeys()

/**
 * $subject_keys ���V���A���C�Y���ĕۑ�����
 */
function saveSubjectKeys($subject_keys, $sb_keys_txt, $sb_keys_b_txt)
{
    global $_conf;

    //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('saveSubjectKeys()');
    //if (file_exists($sb_keys_b_txt)) { unlink($sb_keys_b_txt); }
    if (empty($_REQUEST['norefresh']) && !empty($subject_keys)) {
        if (file_exists($sb_keys_txt)) {
            FileCtl::make_datafile($sb_keys_b_txt, $_conf['p2_perm']);
            copy($sb_keys_txt, $sb_keys_b_txt);
        } else {
            FileCtl::make_datafile($sb_keys_txt, $_conf['p2_perm']);
        }
        if ($sb_keys_cont = serialize($subject_keys)) {
            if (FileCtl::file_write_contents($sb_keys_txt, $sb_keys_cont) === false) {
                p2die('cannot write file.');
            }
        }
    }
    //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('saveSubjectKeys()');

    return true;
}

// }}}
// {{{ matchSbFilter()

/**
 * �X���^�C�i�Ɩ{���j�Ń}�b�`������true��Ԃ�
 */
function matchSbFilter(Thread $aThread)
{
    // �S��������dat������΁A���e��ǂݍ���
    if (!empty($_REQUEST['find_cont'])) {
        if (file_exists($aThread->keydat)) {
            $subject = file_get_contents($aThread->keydat);
            // be.2ch.net ��EUC
            if (P2Util::isHostBe2chNet($aThread->host)) {
                $subject = mb_convert_encoding($subject, 'CP932', 'CP51932');
            }
        } else {
            return false;
        }
    } else {
        $subject = $aThread->ttitle;
    }

    if ($GLOBALS['sb_filter']['method'] == 'and') {
        foreach ($GLOBALS['words_fm'] as $word) {
            if (!StrCtl::filterMatch($word, $subject)) {
                return false;
            }
        }
    } else {
        if (!StrCtl::filterMatch($GLOBALS['word_fm'], $subject)) {
            return false;
        }
    }

    return true;
}

// }}}
// {{{ getSbScore()

/**
 * �X���b�h�^�C�g���̃X�R�A���v�Z���ĕԂ�
 */
function getSbScore($words, $length)
{
    static $bracket_regex = null;
    if (!$bracket_regex) {
        $bracket_regex = mb_convert_encoding('/[\\[\\]{}()�i�j�u�v�y�z]/u', 'UTF-8', 'CP932');
    }
    $score = 0.0;
    if ($length) {
        foreach ($words as $word) {
            $chars = mb_strlen($word, 'UTF-8');
            if ($chars == 1 && preg_match($bracket_regex, $word)) {
                $score += 0.1 / $length;
            } elseif ($word == 'part') {
                $score += 1.0 / $length;
            } else {
                $revision = strlen($word) / mb_strwidth($word, 'UTF-8');
                //$score += pow($chars * $revision, 2) / $length;
                $score += $chars * $chars * $revision / $length;
                //$score += $chars * $chars / $length;
            }
        }
        if ($length > $GLOBALS['wakati_length']) {
            $score *= $GLOBALS['wakati_length'] / $length;
        } else {
            $score *= $length / $GLOBALS['wakati_length'];
        }
    }
    return $score;
}

// }}}
// {{{ setSbSimilarity()

/**
 * �X���b�h�^�C�g���̗ގ������v�Z���ĕԂ�
 */
function setSbSimilarity($aThread)
{
    $common_words = array_intersect(p2_wakati($aThread->ttitle_hc), $GLOBALS['wakati_words']);
    if (!$common_words) {
        $aThread->similarity = 0.0;
        return false;
    }
    $score = getSbScore($common_words, mb_strlen($aThread->ttitle_hc, 'CP932'));
    $aThread->similarity = $score / $GLOBALS['wakati_score'];
    // debug (title ����)
    //$aThread->ttitle_hd = mb_convert_encoding(htmlspecialchars(implode(' ', $common_words)), 'CP932', 'UTF-8');
    return true;
}

// }}}
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
