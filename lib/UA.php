<?php
// �Ⴆ�΁A�N�G���[�� b=k �Ȃ� isK() ��true�ƂȂ�̂ŁA�g�ь����\���ɂ����肷��

// {{{ ���̃N���X�ł̂ݗ��p����O���[�o���ϐ��i_UA_*�j
// over PHP5�Ɍ���ł���Ȃ�v���C�x�[�g�ȃN���X�ϐ��ɂ������Ƃ���̂���

// @see getQueryKey()
$GLOBALS['_UA_query_key'] = 'b';

// @see setPCQuery() // b=pc
$GLOBALS['_UA_PC_query'] = 'pc';

// @see setMobileQuery() // b=k
$GLOBALS['_UA_mobile_query'] = 'k';

// @see setIPhoneGroupQuery() // b=i
$GLOBALS['_UA_iphonegroup_query'] = 'i';

$GLOBALS['_UA_force_mode'] = null;

// }}}
// {{{ UA

// [todo] enableJS() �� enableAjax() ���~��������

/**
 * static���\�b�h�ŗ��p����
 */
class UA
{
    // {{{ setForceMode()

    /**
     * �����I�Ƀ��[�h�ipc, k�j���w�肷��
     * �i�N�G���[���Z�b�g����킯�ł͂Ȃ��j
     */
    static public function setForceMode($v)
    {
        $GLOBALS['_UA_force_mode'] = $v;
    }

    // }}}
    // {{{ isPC()

    /**
     * UA��PC�i�񃂃o�C���j�Ȃ�true��Ԃ�
     * iPhone���܂�ł��邪�A������܂܂Ȃ��Ȃ�\�������邱�Ƃɒ��ӁB
     * ���݁AiPhone��setForceMode()��isMobileByQuery()�������Ă���B�i���͎�߂Łj
     *
     * @return  boolean
     */
    static public function isPC($ua = null)
    {
        return !self::isMobile($ua);
    }

    // }}}
    // {{{ isK()

    /**
     * isMobile() �̃G�C���A�X�ɂȂ��Ă���
     *
     * [plan] �g��isK()�ƁA���o�C��isMobile()�́A�ʂ̂��̂Ƃ��ċ�ʂ��������������ȁB�iisMobile()��isK()���܂ނ��̂Ƃ��āj
     * �g�сF��ʂ��������B�y�[�W�̕\���e�ʂɐ���������B�����̃A�N�Z�X�L�[���g���B
     * ���o�C���F�g�тƓ�������ʂ������߂����A�t���u���E�U�ŁAJavaScript���g����B
     */
    static public function isK($ua = null)
    {
        return self::isMobile($ua);
    }

    // }}}
    // {{{ isMobile()

    /**
     * UA���g�ѕ\���ΏۂȂ�true��Ԃ�
     * isK()�ƈӖ�����ʂ���\�肪����̂ŁA����܂ł̊Ԃ͎g��Ȃ��ł����i�����_�A�g���Ă��Ȃ��j
     * �iisMobileByQuery()�Ȃǂ͎g���Ă��邪�j
     * isM()�ɂ������C���B
     *
     * @params  string  $ua  UA���w�肷��Ȃ�
     * @return  boolean
     */
    static public function isMobile($ua = null)
    {
        static $cache_ = null;

        // �����w�肪�����
        if (isset($GLOBALS['_UA_force_mode'])) {
            // �����̓L���b�V�����Ȃ�
            return ($GLOBALS['_UA_force_mode'] == $GLOBALS['_UA_mobile_query']);
        }

        // ������UA�����w��Ȃ�A�N�G���[�w����Q��
        if (is_null($ua)) {
            if (self::getQueryValue()) {
                return self::isMobileByQuery();
            }
        }

        // ������UA�����w��Ȃ�A�L���b�V���L��
        if (is_null($ua) and !is_null($cache_)) {
            return $cache_;
        }

        $isMobile = false;
        if ($nuam = self::getNet_UserAgent_Mobile($ua)) {
            if (!$nuam->isNonMobile()) {
                $isMobile = true;
            }
        }

        /*
        // NetFront�n�i�܂�PSP�j�����o�C����
        if (!$isMobile) {
            $isMobile = self::isNetFront($ua);
        }

        // Nintendo DS�����o�C����
        if (!$isMobile) {
            $isMobile = self::isNintendoDS($ua);
        }
        */

        // ������UA�����w��Ȃ�A�L���b�V���ۑ�
        if (is_null($ua)) {
            $cache_ = $isMobile;
        }

        return $isMobile;
    }

    // }}}
    // {{{ isIPhoneGroup()

    /**
     * UA��iPhone, iPod touch�Ȃ�true��Ԃ��B
     *
     * @param   string   $aua  UA���w�肷��Ȃ�
     * @return  boolean
     */
    static public function isIPhoneGroup($aua = null)
    {
        static $cache_ = null;

        // �����w�肪����΃`�F�b�N
        if (isset($GLOBALS['_UA_force_mode'])) {
            // �ڍs�̕֋X��A���͂���߂Ă���
            // return ($GLOBALS['_UA_force_mode'] == $GLOBALS['_UA_iphonegroup_query']);
            if ($GLOBALS['_UA_force_mode'] == $GLOBALS['_UA_iphonegroup_query']) {
                return true;
            }
        }

        $ua = $aua;

        // UA�̈��������w��Ȃ�A
        if (is_null($aua)) {
            // �N�G���[�w����Q��
            if (self::getQueryValue()) {
                //// ����݊���Ab=k�ł�iPhone�Ƃ݂Ȃ����Ƃ������B
                //if (!self::isMobileByQuery()) {
                    return self::isIPhoneGroupByQuery();
                //}
            }

            // �i�L���b�V������قǂł͂Ȃ������j
            // ������UA�����w��Ȃ�A�L���b�V���L��
            if (!is_null($cache_)) {
                return $cache_;
            }

            // �N���C�A���g��UA�Ŕ���
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $ua = $_SERVER['HTTP_USER_AGENT'];
            }
        }

        $isiPhoneGroup = false;

        // iPhone
        // Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A543a Safari/419.3

        // iPod touch
        // Mozilla/5.0 (iPod; U; CPU like Mac OS X; ja-jp) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/3A110a Safari/419.3
        if (preg_match('/(iPhone|iPod)/', $ua) || self::isAndroidWebKit($ua)) {
            $isiPhoneGroup = true;
        }

        // UA�̈��������w��Ȃ�A�L���b�V���ۑ�
        if (is_null($aua)) {
            $cache_ = $isiPhoneGroup;
        }
        return $isiPhoneGroup;
    }

    // }}}
    // {{{ isPCByQuery()

    /**
     * �N�G���[��PC���w�肵�Ă���Ȃ�true��Ԃ�
     *
     * @return  boolean
     */
    static private function isPCByQuery()
    {
        $qv = self::getQueryValue();
        if (isset($qv) && $qv == self::getPCQuery()) {
            return true;
        }
        return false;
    }

    // }}}
    // {{{ isMobileByQuery()

    /**
     * �N�G���[���g�т��w�肵�Ă���Ȃ�true��Ԃ�
     *
     * @return  boolean
     */
    static private function isMobileByQuery()
    {
        $qv = self::getQueryValue();
        if (isset($qv) && $qv == self::getMobileQuery()) {
            return true;
        }
        return false;
    }

    /**
     * �N�G���[��IPhoneGroup���w�肵�Ă���Ȃ�true��Ԃ�
     *
     * @return  boolean
     */
    static private function isIPhoneGroupByQuery()
    {
        $qv = self::getQueryValue();
        if (isset($qv) && $qv == self::getIPhoneGroupQuery()) {
            return true;
        }
        return false;
    }

    // }}}
    // {{{ getQueryValue()

    /**
     * �\�����[�h�w��p�̃N�G���[�l���擾����
     *
     * @return  string|null
     */
    static public function getQueryValue($key = null)
    {
        if (is_null($key)) {
            if (!$key = self::getQueryKey()) {
                return null;
            }
        }

        $r = null;
        if (isset($_REQUEST[$key])) {
            if (preg_match('/^\\w+$/', $_REQUEST[$key])) {
                $r = $_REQUEST[$key];
            }
        }
        return $r;
    }

    // }}}
    // {{{ getQueryKey()

    /**
     * @return  string
     */
    static public function getQueryKey()
    {
        return $GLOBALS['_UA_query_key'];
    }

    // }}}
    // {{{ setPCQuery()

    /**
     * @param   string  $pc  default is 'pc'
     * @return  void
     */
    static public function setPCQuery($pc)
    {
        $GLOBALS['_UA_PC_query'] = $pc;
    }

    // }}}
    // {{{ getPCQuery()

    /**
     * @return  string
     */
    static public function getPCQuery()
    {
        return $GLOBALS['_UA_PC_query'];
    }

    // }}}
    // {{{ setMobileQuery()

    /**
     * @param   string  $k  default is 'k'
     * @return  void
     */
    static public function setMobileQuery($k)
    {
        $GLOBALS['_UA_mobile_query'] = $k;
    }

    // }}}
    // {{{ getMobileQuery()

    /**
     * @return  string
     */
    static public function getMobileQuery()
    {
        return $GLOBALS['_UA_mobile_query'];
    }

    // }}}
    // {{{ setIPhoneGroupQuery()

    /**
     * @param   string  $i  default is 'i'
     * @return  void
     */
    static public function setIPhoneGroupQuery($i)
    {
        $GLOBALS['_UA_iphonegroup_query'] = $i;
    }

    // }}}
    // {{{ getIPhoneGroupQuery()

    /**
     * @return  string
     */
    static public function getIPhoneGroupQuery()
    {
        return $GLOBALS['_UA_iphonegroup_query'];
    }

    // }}}
    // {{{ getNet_UserAgent_Mobile()

    /**
     * Net_UserAgent_Mobile::singleton() �̌��ʂ��擾����B
     * REAR Error �� false �ɕϊ������B
     *
     * @param   string  $ua
     * @return  Net_UserAgent_Mobile|false
     */
    static public function getNet_UserAgent_Mobile($ua = null)
    {
        static $cache_ = null;

        if (is_null($ua) and !is_null($cache_)) {
            return $cache_;
        }

        if (!is_null($ua)) {
            $nuam = Net_UserAgent_Mobile::factory($ua);
        } else {
            $nuam = Net_UserAgent_Mobile::singleton();
        }

        if (PEAR::isError($nuam)) {
            trigger_error($nuam->toString, E_USER_WARNING);
            $return = false;

        } elseif (!$nuam) {
            $return = false; // null

        } else {
            $return = $nuam;
        }

        if (is_null($ua)) {
            $cache_ = $return;
        }

        return $return;
    }

    // }}}
    // {{{ isNetFront()

    /**
     * UA��NetFront�i�g�сAPDA�APSP�j�Ȃ�true��Ԃ�
     *
     * @param   string   $ua  UA���w�肷��Ȃ�
     * @return  boolean
     */
    static public function isNetFront($ua = null)
    {
        if (is_null($ua) and isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = $_SERVER['HTTP_USER_AGENT'];
        }

        if (preg_match('/(NetFront|AVEFront\/|AVE-Front\/)/', $ua)) {
            return true;
        }
        if (self::isPSP()) {
            return true;
        }
        return false;
    }

    // }}}
    // {{{ isPSP()

    /**
     * UA��PSP�Ȃ�true��Ԃ��BNetFront�n�炵���B
     *
     * @param   string   $ua  UA���w�肷��Ȃ�
     * @return  boolean
     */
    static public function isPSP($ua = null)
    {
        if (is_null($ua) and isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = $_SERVER['HTTP_USER_AGENT'];
        }

        // Mozilla/4.0 (PSP (PlayStation Portable); 2.00)
        if (false !== strpos($ua, 'PlayStation Portable')) {
            return true;
        }
        return false;
    }

    // }}}
    // {{{ isNintendoDS()

    /**
     * UA��Nintendo DS�Ȃ�true��Ԃ��B
     *
     * @param   string   $ua  UA���w�肷��Ȃ�
     * @return  boolean
     */
    static public function isNintendoDS($ua = null)
    {
        if (is_null($ua) and isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = $_SERVER['HTTP_USER_AGENT'];
        }

        // Mozilla/4.0 (compatible; MSIE 6.0; Nitro) Opera 8.5 [ja]
        if (false !== strpos($ua, ' Nitro')) {
            return true;
        }
        return false;
    }

    // }}}
    // {{{ isAndroidWebKit()

    /**
     * UA��Android�i��Webkit�j�Ȃ�true��Ԃ��B
     *
     * @param   string   $ua  UA���w�肷��Ȃ�
     * @return  boolean
     */
    static public function isAndroidWebKit($ua = null)
    {
        if (is_null($ua) and isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = $_SERVER['HTTP_USER_AGENT'];
        }
        if (!$ua) {
            return false;
        }
        // �V�~�����[�^
        // Mozilla/5.0 (Linux; U; Android 1.0; en-us; generic) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2
        // T-mobile G1
        // Mozilla/5.0 (Linux; U; Android 1.0; en-us; dream) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2
        // generic��dream���قȂ�
        if (false !== strpos('Android', $ua) && false !== strpos('WebKit', $ua)) {
            return true;
        }
        return false;
    }

    // }}}
    // {{{ isSafariGroup()

    /**
     * UA��Safari�n�Ȃ� true ��Ԃ�
     *
     * @param   string   $ua  UA���w�肷��Ȃ�
     * @return  boolean
     */
    static public function isSafariGroup($ua = null)
    {
        if (is_null($ua) and isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = $_SERVER['HTTP_USER_AGENT'];
        }

        return (boolean)preg_match('/Safari|AppleWebKit|Konqueror/', $ua);
    }

    // }}}
    // {{{ isIModeBrowser2()

    /**
     * UA��i���[�h�u���E�U2.x�Ȃ� true ��Ԃ�
     *
     * @param   string   $ua  UA���w�肷��Ȃ�
     * @return  boolean
     */
    static public function isIModeBrowser2($ua = null)
    {
        if (is_null($ua) and isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = $_SERVER['HTTP_USER_AGENT'];
        }

        if (preg_match('!^DoCoMo/2\\.\\d \\w+\\(c(\\d+)!', $ua, $matches)) {
            // �L���b�V��500KB�ȏ�Ȃ�i���[�h�u���E�U2.x�Ƃ݂Ȃ�
            if (500 <= (int)$matches[1]) {
                return true;
            }
        }

        return false;
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
