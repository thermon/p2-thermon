<?php
/**
 * rep2 - ���j���[�ŐV������m�邽�߂Ɏg�p
 * $shinchaku_num, $_newthre_num ���Z�b�g
 *
 * subject.php �ƌZ��Ȃ̂ňꏏ�ɖʓ|���݂�
 */

$_newthre_num = 0;
$shinchaku_num = 0;
$ta_num = 0;
$ta_keys = array();
$nowtime = time();

if (!isset($spmode)) {
    $spmode = false;
}

// {{{ sb_keys �ݒ�

if (!$spmode) {
    $sb_keys_txt = P2Util::idxDirOfHostBbs($host, $bbs) . 'p2_sb_keys.txt';

    if ($pre_sb_cont = FileCtl::file_read_contents($sb_keys_txt)) {
        $pre_subject_keys = @unserialize($pre_sb_cont);
        if (!is_array($pre_subject_keys)) {
            $pre_subject_keys = array();
        }
        unset($pre_sb_cont);
    } else {
        $pre_subject_keys = array();
    }
} else {
    $pre_subject_keys = array();
}

// }}}

//============================================================
// ���C��
//============================================================

$aThreadList = new ThreadList();

// �ƃ��[�h�̃Z�b�g ===================================
if ($spmode) {
    if ($spmode == "taborn" or $spmode == "soko") {
        $aThreadList->setIta($host, $bbs, P2Util::getItaName($host, $bbs));
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

// �\�[�X���X�g�Ǎ�
$lines = $aThreadList->readList();

//============================================================
// ���ꂼ��̍s���
//============================================================

$linesize = sizeof($lines);
$subject_txts = array();

for ($x = 0; $x < $linesize ; $x++) {
    $aThread = new Thread();

    $l = rtrim($lines[$x]);

    // �f�[�^�ǂݍ���
    if ($aThreadList->spmode) {
        switch ($aThreadList->spmode) {
        case "recent": // ����
            $aThread->getThreadInfoFromExtIdxLine($l);
            break;
        case "res_hist": // �������ݗ���
            $aThread->getThreadInfoFromExtIdxLine($l);
            break;
        case "fav": // ���C��
            $aThread->getThreadInfoFromExtIdxLine($l);
            break;
        case "taborn":    // �X���b�h���ځ[��
            $la = explode("<>", $l);
            $aThread->key = $la[1];
            $aThread->host = $aThreadList->host;
            $aThread->bbs = $aThreadList->bbs;
            break;
        case "soko":    // dat�q��
            $la = explode("<>", $l);
            $aThread->key = $la[1];
            $aThread->host = $aThreadList->host;
            $aThread->bbs = $aThreadList->bbs;
            break;
        case "palace":    // �X���̓a��
            $aThread->getThreadInfoFromExtIdxLine($l);
            break;
        }
    // subject (not spmode)
    } else {
        $aThread->getThreadInfoFromSubjectTxtLine($l);
        $aThread->host = $aThreadList->host;
        $aThread->bbs = $aThreadList->bbs;
    }

    // �������ߖ�̂���
    $lines[$x] = null;

    // host��bbs��key���s���Ȃ�X�L�b�v
    if (!($aThread->host && $aThread->bbs && $aThread->key)) {
        unset($aThread);
        continue;
    }

    $subject_id = $aThread->host . '/' . $aThread->bbs;

    // {{{ �V�������ǂ���(for subject)

    if (!$aThreadList->spmode) {
        if (!isset($pre_subject_keys[$aThread->key])) {
            $aThread->new = true;
        }
    }

    // }}}
    // {{{ �X���b�h���ځ[��`�F�b�N

    if ($aThreadList->spmode != 'taborn' && isset($ta_keys[$aThread->key])) {
        unset($ta_keys[$aThread->key]);
        continue; //���ځ[��X���̓X�L�b�v
    }

    // }}}

    $aThread->setThreadPathInfo($aThread->host, $aThread->bbs, $aThread->key);
    $aThread->getThreadInfoFromIdx(); // �����X���b�h�f�[�^��idx����擾

    // {{{ spmode(�a�����������)�Ȃ�

    if ($aThreadList->spmode && $aThreadList->spmode != 'palace') {

        //  subject.txt����DL�Ȃ痎�Ƃ��ăf�[�^��z��Ɋi�[
        if (!isset($subject_txts[$subject_id])) {
            $subject_txts[$subject_id] = array();
            $aSubjectTxt = new SubjectTxt($aThread->host, $aThread->bbs);

            //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('subthre_read'); //
            if ($aThreadList->spmode == "soko" or $aThreadList->spmode == "taborn") {

                if (is_array($aSubjectTxt->subject_lines)) {
                    $it = 1;
                    foreach ($aSubjectTxt->subject_lines as $asbl) {
                        if (preg_match("/^([0-9]+)\.(?:dat|cgi)(?:,|<>)(.+) ?(?:\(|�i)([0-9]+)(?:\)|�j)/", $asbl, $matches)) {
                            $akey = $matches[1];
                            $subject_txts[$subject_id][$akey] = array(
                                //'ttitle' => rtrim($matches[2]),
                                'rescount' => (int)$matches[3],
                                //'torder' => $it,
                            );
                        }
                        $it++;
                    }
                }

            } else {
                $subject_txts[$subject_id] = $aSubjectTxt->subject_lines;

            }
            //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('subthre_read');//
        }

        //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('subthre_check');//
        // �X�����擾 =============================
        if ($aThreadList->spmode == "soko" or $aThreadList->spmode == "taborn") {

            if (isset($subject_txts[$subject_id][$aThread->key])) {

                // �q�ɂ̓I�����C�����܂܂Ȃ�
                if ($aThreadList->spmode == "soko") {
                    //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('subthre_check'); //
                    unset($aThread);
                    continue;
                } elseif ($aThreadList->spmode == "taborn") {
                    // subject.txt ����X�����擾
                    // $aThread->getThreadInfoFromSubjectTxtLine($l);
                    //$aThread->isonline = true;
                    //$ttitle = $subject_txts[$subject_id][$aThread->key]['ttitle'];
                    //$aThread->setTtitle($ttitle);
                    $aThread->rescount = $subject_txts[$subject_id][$aThread->key]['rescount'];
                    if ($aThread->readnum) {
                        $aThread->unum = $aThread->rescount - $aThread->readnum;
                        // machi bbs ��sage��subject�̍X�V���s���Ȃ������Ȃ̂Œ������Ă���
                        if ($aThread->unum < 0) { $aThread->unum = 0; }
                        $aThread->nunum = $aThread->unum;
                    }
                    //$aThread->torder = $subject_txts[$subject_id][$aThread->key]['torder'];
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
        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('subthre_check'); //
    }

    // }}} spmode

    // �V������
    if ($aThread->unum > 0) {
        $shinchaku_attayo = true;
        $shinchaku_num = $shinchaku_num + $aThread->unum; // �V����set

    // �V�K�X��
    } elseif ($aThread->new) {
        $_newthre_num++; // ��ShowBrdMenuPc.php
    }

}

unset($aThread, $aThreadList, $lines, $pre_subject_keys, $subject_txts, $ta_keys);

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
