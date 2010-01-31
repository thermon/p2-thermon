<?php

// {{{ BrdCtl

/**
 * rep2 - BrdCtl -- ���X�g�R���g���[���N���X for menu.php
 *
 * @static
 */
class BrdCtl
{
    // {{{ read_brds()

    /**
     * board��S�ēǂݍ���
     */
    static public function read_brds()
    {
        $brd_menus_dir = BrdCtl::read_brd_dir();
        $brd_menus_online = BrdCtl::read_brd_online();
        $brd_menus = array_merge($brd_menus_dir, $brd_menus_online);
        return $brd_menus;
    }

    // }}}
    // {{{ read_brd_dir()

    /**
     * board�f�B���N�g���𑖍����ēǂݍ���
     */
    static public function read_brd_dir()
    {
        global $_info_msg_ht;

        $brd_menus = array();
        $brd_dir = './board';

        if ($cdir = @dir($brd_dir)) {
            // �f�B���N�g������
            while ($entry = $cdir->read()) {
                if ($entry[0] == '.') {
                    continue;
                }
                $filepath = $brd_dir.'/'.$entry;
                if ($data = FileCtl::file_read_lines($filepath)) {
                    $aBrdMenu = new BrdMenu();    // �N���X BrdMenu �̃I�u�W�F�N�g�𐶐�
                    $aBrdMenu->setBrdMatch($filepath);    // �p�^�[���}�b�`�`����o�^
                    $aBrdMenu->setBrdList($data);    // �J�e�S���[�Ɣ��Z�b�g
                    $brd_menus[] = $aBrdMenu;

                } else {
                    $_info_msg_ht .= "<p>p2 error: ���X�g {$entry} ���ǂݍ��߂܂���ł����B</p>\n";
                }
            }
            $cdir->close();
        }

        return $brd_menus;
    }

    // }}}
    // {{{ read_brd_online()

    /**
    * �I�����C�����X�g��Ǎ���
    */
    static public function read_brd_online()
    {
        global $_conf, $_info_msg_ht;

        $brd_menus = array();
        $isNewDL = false;

        if ($_conf['brdfile_online']) {
            $cachefile = P2Util::cacheFileForDL($_conf['brdfile_online']);
            $noDL = false;
            $read_html_flag = false;

            // �L���b�V��������ꍇ
            if (file_exists($cachefile.'.p2.brd')) {
                // norefresh�Ȃ�DL���Ȃ�
                if (!empty($_GET['nr'])) {
                    $noDL = true;
                // �L���b�V���̍X�V���w�莞�Ԉȓ��Ȃ�DL���Ȃ�
                } elseif (@filemtime($cachefile.'.p2.brd') > time() - 60 * 60 * $_conf['menu_dl_interval']) {
                    $noDL = true;
                }
            }

            // DL���Ȃ�
            if ($noDL) {
                ;
            // DL����
            } else {
                //echo "DL!<br>";//
                $brdfile_online_res = P2Util::fileDownload($_conf['brdfile_online'], $cachefile);
                if ($brdfile_online_res->isSuccess() && $brdfile_online_res->code != 304) {
                    $isNewDL = true;
                }
            }

            // html�`���Ȃ�
            if (preg_match('/html?$/', $_conf['brdfile_online'])) {

                // �X�V����Ă�����V�K�L���b�V���쐬
                if ($isNewDL) {
                    // �������ʂ��L���b�V�������̂����
                    if (isset($GLOBALS['word']) && strlen($GLOBALS['word']) > 0) {
                        $_tmp = array($GLOBALS['word'], $GLOBALS['word_fm'], $GLOBALS['words_fm']);
                        $GLOBALS['word'] = null;
                        $GLOBALS['word_fm'] = null;
                        $GLOBALS['words_fm'] = null;
                    } else {
                        $_tmp = null;
                    }

                    //echo "NEW!<br>"; //
                    $aBrdMenu = new BrdMenu(); // �N���X BrdMenu �̃I�u�W�F�N�g�𐶐�
                    $aBrdMenu->makeBrdFile($cachefile); // .p2.brd�t�@�C���𐶐�
                    $brd_menus[] = $aBrdMenu;
                    unset($aBrdMenu);

                    if ($_tmp) {
                        list($GLOBALS['word'], $GLOBALS['word_fm'], $GLOBALS['words_fm']) = $_tmp;
                        $brd_menus = array();
                    } else {
                        $read_html_flag = true;
                    }
                }

                if (file_exists($cachefile.'.p2.brd')) {
                    $cache_brd = $cachefile.'.p2.brd';
                } else {
                    $cache_brd = $cachefile;
                }

            } else {
                $cache_brd = $cachefile;
            }

            if (!$read_html_flag) {
                if ($data = FileCtl::file_read_lines($cache_brd)) {
                    $aBrdMenu = new BrdMenu(); // �N���X BrdMenu �̃I�u�W�F�N�g�𐶐�
                    $aBrdMenu->setBrdMatch($cache_brd); // �p�^�[���}�b�`�`����o�^
                    $aBrdMenu->setBrdList($data); // �J�e�S���[�Ɣ��Z�b�g
                    if ($aBrdMenu->num) {
                        $brd_menus[] = $aBrdMenu;
                    } else {
                        $_info_msg_ht .=  "<p>p2 �G���[: {$cache_brd} ������j���[�𐶐����邱�Ƃ͂ł��܂���ł����B</p>\n";
                    }
                    unset($data, $aBrdMenu);
                } else {
                    $_info_msg_ht .=  "<p>p2 �G���[: {$cachefile} �͓ǂݍ��߂܂���ł����B</p>\n";
                }
            }
        }

        return $brd_menus;
    }

    // }}}
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
