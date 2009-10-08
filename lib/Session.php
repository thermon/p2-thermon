<?php
// {{{ GLOBALS

$GLOBALS['_SESS_VERSION'] = 1; // �Z�b�V�����̃o�[�W�����i�S�Ẳғ��r���Z�b�V�����������j��������������UP�����肷��j

// }}}
// {{{ Session

/**
 * Session Class
 *
 * IR, UA, �A�N�Z�X���Ԃ̃`�F�b�N�𔺂��A���Z�L���A�ȃZ�b�V�����Ǘ��N���X
 * �قƂ�ǎ����œ����̂ł��܂�C�ɂ����A�ʏ�ʂ� $_SESSION �̒l����舵���΂悢�B
 * �������A$_SESSION[$this->sess_array]�i$_SESSION['_sess_array']�j �͗\���ƂȂ��Ă���B
 *
 * ���p��
 * $_session = new Session(); // �����̎��_��PHP�W���Z�b�V�������X�^�[�g����
 * if ($msg = $_session->checkSessionError()) { // ���Z�L���A�ȃZ�b�V�����`�F�b�N
 *     p2die($msg);
 * }
 *
 * $_SESSION�ւ̃A�N�Z�X���I������́Asession_write_close()���Ă����Ƃ悢���낤�B
 *
 * ���d�v��
 * php.ini �� session.auto_start = 0 (PHP�̃f�t�H���g�̂܂�) �ɂȂ��Ă��邱�ƁB
 * �����Ȃ��ƂقƂ�ǂ̃Z�b�V�����֘A�̃p�����[�^���X�N���v�g���ŕύX�ł��Ȃ��B
 * .htaccess�ŕύX��������Ă���Ȃ�
 *
 * <IfModule mod_php4.c>
 *    php_flag session.auto_start Off
 * </IfModule>
 *
 * �ł�OK�B
 *
 * @author aki
 */
class Session
{
    // {{{ properties

    public $sess_array = '_sess_array';

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     *
     * ������PHP�̕W���Z�b�V�������X�^�[�g����
     */
    public function __construct($session_name = NULL, $session_id = NULL)
    {
        session_cache_limiter('none'); // �L���b�V������Ȃ�

        if ($session_name) { session_name($session_name); }
        if ($session_id)   { session_id($session_id); }
        session_start();

        /*
        Expires: Thu, 19 Nov 1981 08:52:00 GMT
        Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0
        Pragma: no-cache
        */
    }

    // }}}
    // {{{ _autoBegin()

    /**
     * ���Z�L���A�ȃZ�b�V�����Ǘ����J�n����
     * @return bool
     */
    private function _autoBegin()
    {
        // �܂������Z�b�V�������n�܂��Ă��Ȃ�������
        if (!isset($_SESSION[$this->sess_array]['actime'])) {

            // �Z�b�V�����ϐ�($this->sess_array)�������Z�b�g
            $this->_initSess();

            // �Z�b�V�����ϐ��̓o�^�Ɏ��s������A�G���[
            if (!isset($_SESSION[$this->sess_array]['actime'])) {
                trigger_error('Session::_autoBegin() �Z�b�V�����ϐ���o�^�ł��܂���ł����B', E_USER_WARNING);
                p2die('Session');
                return false;
            }
        }

        return true;
    }

    // }}}
    // {{{ _initSess()

    /**
     * �Z�b�V�����n�߂ɕϐ����Z�b�g����
     *
     * @return void
     */
    private function _initSess()
    {
        // ������
        $_SESSION[$this->sess_array] = array();

        $_SESSION[$this->sess_array]['actime']     = time();
        $_SESSION[$this->sess_array]['ip']         = $_SERVER['REMOTE_ADDR'];
        $_SESSION[$this->sess_array]['ua']         = $_SERVER['HTTP_USER_AGENT'];
        // $_SESSION[$this->sess_array]['referer'] = $_SERVER['HTTP_REFERER'];
        $_SESSION[$this->sess_array]['version']    = $GLOBALS['_SESS_VERSION'];
    }

    // }}}
    // {{{ checkSessionError()

    /**
     * �Z�b�V�����̑Ó������`�F�b�N���āA�G���[������΃��b�Z�[�W�𓾂�B�A�N�Z�X���Ԃ̍X�V�������ŁB
     *
     * @return false|string �G���[������΁A�iunSession()���āj�G���[���b�Z�[�W��Ԃ��B�Ȃ����false��Ԃ��B
     */
    public function checkSessionError()
    {
        // �����Z�b�V����
        $this->_autoBegin();

        $error_msg = '';

        if (!isset($_SESSION[$this->sess_array]['actime'])) {
            $error_msg = '�Z�b�V�������@�\���Ă��܂���B';

        } else {

            if (!$this->_checkAcTime()) {
                $error_msg = '�Z�b�V�����̎��Ԑ؂�ł��B�ēx���O�C���������Ă��������B';
            }

            if (!$this->_checkVersion()) {
                $error_msg = '�Z�b�V�����̃o�[�W����������������܂���B'
                    .'�i����̓V�X�e���̃o�[�W�����A�b�v�ɂ���āA�ꎞ�I�ɋN���邱�Ƃ̂��錻�ۂł��j';
            }

            if (!$this->_checkIP()) {
                $error_msg = '�Z�b�V������IP������������܂���B';
            }

            if (!$this->_checkUA()) {
                $error_msg = '�Z�b�V������UA������������܂���B';
            }
        }

        // �G���[������΁A�iunSession()���āj�G���[���b�Z�[�W��Ԃ��B
        if ($error_msg) {
            self::unSession();
            return $error_msg;
        }

        // ���Ȃ���΁A�A�N�Z�X���Ԃ��X�V����
        $_SESSION[$this->sess_array]['actime'] = time();

        // �N�G���[��SID��t������ꍇ�́A���� session_regenerate_id() ����A�A�Ə����s��
        // �ߋ��A�N�Z�X5���ȑO�𖳌��ɂ���Ƃ����ł����������A
        /*
        $sname = session_name();
        if (!$_COOKIE[$sname]) {
            $oldID = session_id();
            session_regenerate_id();
            unlink(session_save_path() . "/sess_$oldID");
        }
        */

        return false;
    }

    // }}}
    // {{{ _checkAcTime()

    /**
     * �Z�b�V�����̃A�N�Z�X���Ԃ��`�F�b�N����
     *
     * @return bool
     */
    private function _checkAcTime($minutes = 60)
    {
        // �ŏI�A�N�Z�X���Ԃ���A��莞�Ԉȏオ�o�߂��Ă����Expire
        if ($_SESSION[$this->sess_array]['actime'] + $minutes * 60 < time()) {
            return false;
        } else {
            return true;
        }
    }

    // }}}
    // {{{ _checkVersion()

    /**
     * �Z�b�V�����̃o�[�W�������`�F�b�N����
     *
     * @return bool
     */
    private function _checkVersion()
    {
        if ($_SESSION[$this->sess_array]['version'] == $GLOBALS['_SESS_VERSION']) {
            return true;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ _checkIP()

    /**
     * IP�A�h���X�Ó����`�F�b�N����
     *
     * @return bool
     */
    private function _checkIP()
    {
        $check_level = 1; // 0�`4 docomo���l������ƁA1�܂�

        $ses_ips = explode('.', $_SESSION[$this->sess_array]['ip']);
        $now_ips = explode('.', $_SERVER['REMOTE_ADDR']);

        for ($i = 0; $i++; $i < $check_level) {
            if ($ses_ips[$i] != $now_ips[$i]) {
                return false;
            }
        }
        return true;
    }

    // }}}
    // {{{ _checkUA()

    /**
     * UA�ŃZ�b�V�����̑Ó������`�F�b�N����
     *
     * @return bool
     */
    private function _checkUA()
    {
        // {{{ docomo��UTN����UA�㕔���ς��̂ŋ@�햼�Ō��؂���

        $mobile = Net_UserAgent_Mobile::singleton();
        if ($mobile->isDoCoMo()) {
            $mobile_b = Net_UserAgent_Mobile::factory($_SESSION[$this->sess_array]['ua']);
            if ($mobile_b->getModel() == $mobile->getModel()) {
                return true;
            }
        }

        // }}}

        // $offset = 12;
        if (empty($offset)) {
            $offset = strlen($_SERVER['HTTP_USER_AGENT']);
        }
        if (substr($_SERVER['HTTP_USER_AGENT'], 0, $offset) == substr($_SESSION[$this->sess_array]['ua'], 0, $offset)) {
            return true;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ unSession()

    /**
     * $_SESSION�ŃZ�b�V������j������
     *
     * �Z�b�V�������Ȃ��A�������͐������Ȃ��ꍇ�Ȃǂ�
     * http://jp.php.net/manual/ja/function.session-destroy.php
     *
     * @return void
     */
    static public function unSession()
    {
        global $_conf;

        // �Z�b�V�����̏�����
        // session_name("something")���g�p���Ă���ꍇ�͓��ɂ����Y��Ȃ��悤��!
        session_start();

        // �Z�b�V�����ϐ���S�ĉ�������
        $_SESSION = array();

        // �Z�b�V������ؒf����ɂ̓Z�b�V�����N�b�L�[���폜����B
        // Note: �Z�b�V������񂾂��łȂ��Z�b�V������j�󂷂�B
        if (isset($_COOKIE[session_name()])) {
           unset($_COOKIE[session_name()]);
           setcookie(session_name(), '', time() - 42000);
        }

        // �ŏI�I�ɁA�Z�b�V������j�󂷂�
        if (isset($_conf['session_dir'])) {
            $session_file = $_conf['session_dir'] . '/sess_' . session_id();

        } else {
            $session_file = session_save_path() . '/sess_' . session_id();
        }

        session_destroy();
        if (file_exists($session_file)) {
            unlink($session_file);
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
