<?php
// {{{ ExpackLoader

/**
 * �g���p�b�N�������N���X
 *
 * @static
 */
class ExpackLoader
{
    // {{{ loadFunction()

    /**
     * �֐������[�h����
     *
     * @param   string  $funcName   �֐���
     * @param   string  $fileName   �֐�����`����Ă���t�@�C����
     * @return  void
     */
    static public function loadFunction($funcName, $fileName)
    {
        if (!function_exists($funcName)) {
            include P2EX_LIB_DIR . '/' . $fileName;
        }
    }

    // }}}
    // {{{ loadClass()

    /**
     * �N���X�����[�h����
     *
     * @param   string  $className  �N���X��
     * @param   string  $fileName   �N���X����`����Ă���t�@�C����
     * @return  void
     */
    static public function loadClass($className, $fileName)
    {
        if (!class_exists($className, false)) {
            include P2EX_LIB_DIR . '/' . $fileName;
        }
    }

    // }}}
    // {{{ loadActiveMona()

    /**
     * �A�N�e�B�u���i�[�̏���������
     */
    static public function loadActiveMona()
    {
        global $_conf;

        if (defined('P2_ACTIVEMONA_AVAILABLE')) {
            return;
        }

        if ((!$_conf['ktai'] && $_conf['expack.am.enabled']) ||
            ($_conf['ktai'] && $_conf['expack.am.enabled'] && $_conf['expack.am.autong_k'])
        ) {
            self::loadClass('ActiveMona', 'ActiveMona.php');
            define('P2_ACTIVEMONA_AVAILABLE', 1);
        } else {
            define('P2_ACTIVEMONA_AVAILABLE', 0);
        }
    }

    // }}}
    // {{{ initActiveMona()

    /**
     * �X���b�h�\���I�u�W�F�N�g�ɃA�N�e�B�u���i�[�Ŏg���ϐ����A�T�C������
     */
    static public function initActiveMona($aShowThread)
    {
        global $_conf;

        $aShowThread->activeMona = ActiveMona::singleton();
        $aShowThread->am_enabled = true;

        if (!$_conf['ktai']) {
            if ($_conf['expack.am.autodetect']) {
                $aShowThread->am_autodetect = true;
            }
            if ($_conf['expack.am.display'] == 0) {
                $aShowThread->am_side_of_id = true;
            } elseif ($_conf['expack.am.display'] == 1) {
                $aShowThread->am_on_spm = true;
            } elseif ($_conf['expack.am.display'] == 2) {
                $aShowThread->am_side_of_id = true;
                $aShowThread->am_on_spm = true;
            }
        } elseif ($_conf['expack.am.autong_k']) {
            $aShowThread->am_autong = true;
        }
    }

    // }}}
    // {{{ loadImageCache()

    /**
     * ImageCache2�̏���������
     */
    static public function loadImageCache()
    {
        global $_conf;

        if (defined('P2_IMAGECACHE_AVAILABLE')) {
            return;
        }

        if ((!$_conf['ktai'] && $_conf['expack.ic2.enabled'] % 2 == 1) ||
            ($_conf['ktai'] && $_conf['expack.ic2.enabled'] >= 2))
        {
            self::loadFunction('ic2_loadconfig',        'ic2/loadconfig.inc.php');
            self::loadClass('IC2_DataObject_Images',    'ic2/DataObject/Images.php');
            self::loadClass('IC2_DataObject_BlackList', 'ic2/DataObject/BlackList.php');
            self::loadClass('IC2_DataObject_Errors',    'ic2/DataObject/Errors.php');
            self::loadClass('IC2_Thumbnailer',          'ic2/Thumbnailer.php');
            define('P2_IMAGECACHE_AVAILABLE', 2);
        } else {
            define('P2_IMAGECACHE_AVAILABLE', 0);
        }
    }

    // }}}
    // {{{ loadAAS()

    /**
     * AAS�̏���������
     */
    static public function loadAAS()
    {
        global $_conf;

        if (defined('P2_AAS_AVAILABLE')) {
            return;
        }

        if ($_conf['expack.aas.enabled']) {
            if ($_conf['expack.aas.inline_enabled']) {
                define('P2_AAS_AVAILABLE', 2);
            } else {
                define('P2_AAS_AVAILABLE', 1);
            }
        } else {
            define('P2_AAS_AVAILABLE', 0);
        }
    }

    // }}}
    // {{{ initImageCache()

    /**
     * �X���b�h�\���I�u�W�F�N�g��ImageCache2�Ŏg���ϐ����A�T�C������
     */
    static public function initImageCache($aShowThread)
    {
        global $_conf;

        if (!$_conf['ktai']) {
            $aShowThread->thumb_id_suffix = '-' . strtr(microtime(), '. ', '--');
            $aShowThread->thumbnailer = new IC2_Thumbnailer(IC2_Thumbnailer::SIZE_PC);
        } else {
            $aShowThread->inline_prvw = new IC2_Thumbnailer(IC2_Thumbnailer::SIZE_PC);
            $aShowThread->thumbnailer = new IC2_Thumbnailer(IC2_Thumbnailer::SIZE_MOBILE);
        }

        if ($aShowThread->thumbnailer->ini['General']['automemo']) {
            $aShowThread->img_memo = IC2_DataObject_Images::staticUniform($aShowThread->thread->ttitle, 'CP932');
            $aShowThread->img_memo_query = '&amp;memo=' . rawurlencode($aShowThread->img_memo);
            $aShowThread->img_memo_query .= '&amp;' . $_conf['detect_hint_q_utf8'];
        } else {
            $aShowThread->img_memo = null;
            $aShowThread->img_memo_query = '';
        }

        self::loadClass('IC2_Switch', 'ic2/Switch.php');
        if (!IC2_Switch::get($_conf['ktai'])) {
            $GLOBALS['pre_thumb_limit'] = 0;
            $GLOBALS['pre_thumb_limit_k'] = 0;
            $GLOBALS['pre_thumb_unlimited'] = false;
            $GLOBALS['pre_thumb_ignore_limit'] = false;
            $_conf['expack.ic2.newres_ignore_limit'] = false;
            $_conf['expack.ic2.newres_ignore_limit_k'] = false;
        }
    }

    // }}}
    // {{{ initAAS()

    /**
     * �X���b�h�\���I�u�W�F�N�g��AAS�Ŏg���ϐ����A�T�C������
     */
    static public function initAAS($aShowThread)
    {
        global $_conf;

        if ($_conf['iphone']) {
            $aShowThread->aas_rotate = '&#x21BB;';
        } elseif ($_conf['ktai']) {
            $mobile = &Net_UserAgent_Mobile::singleton();
            /**
             * @link http://www.nttdocomo.co.jp/service/imode/make/content/pictograph/
             * @link http://www.au.kddi.com/ezfactory/tec/spec/3.html
             * @link http://mb.softbank.jp/mb/service/3G/mail/pictogram/
             */
            if ($mobile->isDoCoMo()) {
                $aShowThread->aas_rotate = '&#xF9DA;';      // ���T�C�N��, �g42
            } elseif ($mobile->isEZweb()) {
                $aShowThread->aas_rotate = '&#xF47D;';      // �z���, 807
            } elseif ($mobile->isSoftBank()) {
                $aShowThread->aas_rotate = "\x1b\$Pc\x0f";  // �Q��, 414
            }
        } else {
            //
        }
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
