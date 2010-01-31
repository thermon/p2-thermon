<?php

// {{{ Login

/**
 * rep2 - ���O�C���F�؂������N���X
 *
 * @create  2005/6/14
 * @author aki
 */
class Login
{
    // {{{ properties

    public $user;   // ���[�U���i�����I�Ȃ��́j
    public $user_u; // ���[�U���i���[�U�ƒ��ڐG��镔���j
    public $pass_x; // �Í������ꂽ�p�X���[�h

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     */
    public function __construct()
    {
        $login_user = $this->setdownLoginUser();

        // ���[�U�����w�肳��Ă��Ȃ����
        if ($login_user == NULL) {

            // ���O�C�����s
            require_once P2_LIB_DIR . '/login_first.inc.php';
            printLoginFirst($this);
            exit;
        }

        $this->setUser($login_user);
        $this->pass_x = NULL;
    }

    // }}}
    // {{{ setUser()

    /**
     * ���[�U�����Z�b�g����
     */
    public function setUser($user)
    {
        $this->user_u = $user;
        $this->user = $user;
    }

    // }}}
    // {{{ setdownLoginUser()

    /**
     * ���O�C�����[�U���̎w��𓾂�
     */
    public function setdownLoginUser()
    {
        $login_user = NULL;

        // ���[�U������̗D�揇�ʂɉ�����

        // ���O�C���t�H�[������̎w��
        if (!empty($GLOBALS['brazil'])) {
            $add_mail = '.,@-';
        } else {
            $add_mail = '';
        }
        if (isset($_REQUEST['form_login_id']) && preg_match("/^[0-9A-Za-z_{$add_mail}]+\$/", $_REQUEST['form_login_id'])) {
            $login_user = $this->setdownLoginUserWithRequest();

        // GET�����ł̎w��
        } elseif (isset($_REQUEST['user']) && preg_match("/^[0-9A-Za-z_{$add_mail}]+\$/", $_REQUEST['user'])) {
            $login_user = $_REQUEST['user'];

        // Cookie�Ŏw��
        } elseif (isset($_COOKIE['cid']) && ($user = $this->getUserFromCid($_COOKIE['cid'])) !== false) {
            if (preg_match("/^[0-9A-Za-z_{$add_mail}]+\$/", $user)) {
                $login_user = $user;
            }

        // Session�Ŏw��
        } elseif (isset($_SESSION['login_user']) && preg_match("/^[0-9A-Za-z_{$add_mail}]+\$/", $_SESSION['login_user'])) {
            $login_user = $_SESSION['login_user'];

        /*
        // Basic�F�؂Ŏw��
        } elseif (!empty($_REQUEST['basic'])) {

            if (isset($_SERVER['PHP_AUTH_USER']) && (preg_match("/^[0-9A-Za-z_{$add_mail}]+\$/", $_SERVER['PHP_AUTH_USER']))) {
                $login_user = $_SERVER['PHP_AUTH_USER'];

            } else {
                header('WWW-Authenticate: Basic realm="zone"');
                header('HTTP/1.0 401 Unauthorized');
                echo 'Login Failed. ���[�U�F�؂Ɏ��s���܂����B';
                exit;
            }
        */

        }

        return $login_user;
    }

    // }}}
    // {{{ setdownLoginUserWithRequest()

    /**
     * REQUEST���烍�O�C�����[�U���̎w��𓾂�
     */
    public function setdownLoginUserWithRequest()
    {
        return $_REQUEST['form_login_id'];
    }

    // }}}
    // {{{ authorize()

    /**
     * �F�؂��s��
     */
    public function authorize()
    {
        global $_conf, $_p2session;

        // {{{ �F�؃`�F�b�N

        if (!$this->_authCheck()) {
            // ���O�C�����s
            if (!function_exists('printLoginFirst')) {
                include P2_LIB_DIR . '/login_first.inc.php';
            }
            printLoginFirst($this);
            exit;
        }

        // }}}

        // �����O�C��OK�Ȃ�

        // {{{ ���O�A�E�g�̎w�肪�����

        if (!empty($_REQUEST['logout'])) {

            // �Z�b�V�������N���A�i�A�N�e�B�u�A��A�N�e�B�u���킸�j
            Session::unSession();

            // �⏕�F�؂��N���A
            $this->clearCookieAuth();

            $mobile = Net_UserAgent_Mobile::singleton();

            if ($mobile->isEZweb()) {
                $this->_registAuthOff($_conf['auth_ez_file']);

            } elseif ($mobile->isSoftBank()) {
                $this->_registAuthOff($_conf['auth_jp_file']);

            /* docomo�̓��O�C����ʂ��\�������̂ŁA�⏕�F�؏��������j�����Ȃ�
            } elseif ($mobile->isDoCoMo()) {
                $this->_registAuthOff($_conf['auth_imodeid_file']);
                $this->_registAuthOff($_conf['auth_docomo_file']);
            */
            }

            // $user_u_q = $_conf['ktai'] ? "?user={$this->user_u}" : '';

            $url = rtrim(dirname(P2Util::getMyUrl()), '/') . '/'; // . $user_u_q;

            header('Location: '.$url);
            exit;
        }

        // }}}
        // {{{ �Z�b�V���������p����Ă���Ȃ�A�Z�b�V�����ϐ��̍X�V

        if (isset($_p2session)) {

            // ���[�U���ƃp�XX���X�V
            $_SESSION['login_user']   = $this->user_u;
            $_SESSION['login_pass_x'] = $this->pass_x;
            if (!array_key_exists('login_microtime', $_SESSION)) {
                $_SESSION['login_microtime'] = microtime();
            }
        }

        // }}}
        // {{{ �v��������΁A�⏕�F�؂�o�^

        $this->registCookie();
        $this->registKtaiId();

        // }}}

        // �Z�b�V������F�؈ȊO�Ɏg��Ȃ��ꍇ�͕���
        if (P2_SESSION_CLOSE_AFTER_AUTHENTICATION) {
            session_write_close();
        }

        return true;
    }

    // }}}
    // {{{ checkAuthUserFile()

    /**
     * �F�؃��[�U�ݒ�̃t�@�C���𒲂ׂāA�����ȃf�[�^�Ȃ�̂ĂĂ��܂�
     */
    public function checkAuthUserFile()
    {
        global $_conf;

        if (@include($_conf['auth_user_file'])) {
            // ���[�U��񂪂Ȃ�������A�t�@�C�����̂ĂĔ�����
            if (empty($rec_login_user_u) || empty($rec_login_pass_x)) {
                unlink($_conf['auth_user_file']);
            }
        }

        return true;
    }

    // }}}
    // {{{ _authCheck()

    /**
     * �F�؂̃`�F�b�N���s��
     *
     * @return bool
     */
    private function _authCheck()
    {
        global $_info_msg_ht, $_conf;
        global $_login_failed_flag;
        global $_p2session;

        $this->checkAuthUserFile();

        // �F�؃��[�U�ݒ�i�t�@�C���j��ǂݍ��݂ł�����
        if (file_exists($_conf['auth_user_file'])) {
            include $_conf['auth_user_file'];

            // ���[�U�����������A�F�؎��s�Ŕ�����
            if ($this->user_u != $rec_login_user_u) {
                $_info_msg_ht .= '<p class="infomsg">p2 error: ���O�C���G���[</p>';

                // ���O�C�����s���O���L�^����
                if (!empty($_conf['login_log_rec'])) {
                    $recnum = isset($_conf['login_log_rec_num']) ? intval($_conf['login_log_rec_num']) : 100;
                    P2Util::recAccessLog($_conf['login_failed_log_file'], $recnum);
                }

                return false;
            }

            // �p�X���[�h�ݒ肪����΁A�Z�b�g����
            if (isset($rec_login_pass_x) && strlen($rec_login_pass_x) > 0) {
                $this->pass_x = $rec_login_pass_x;
            }
        }

        // �F�ؐݒ� or �p�X���[�h�L�^���Ȃ������ꍇ�͂����܂�
        if (!$this->pass_x) {

            // �V�K�o�^�łȂ���΃G���[�\��
            if (empty($_POST['submit_new'])) {
                $_info_msg_ht .= '<p class="infomsg">p2 error: ���O�C���G���[</p>';
            }

            return false;
        }

        // {{{ �N�b�L�[�F�؃p�X�X���[

        if (isset($_COOKIE['cid'])) {

            if ($this->checkUserPwWithCid($_COOKIE['cid'])) {
                return true;

            // Cookie�F�؂��ʂ�Ȃ����
            } else {
                // �Â��N�b�L�[���N���A���Ă���
                $this->clearCookieAuth();
            }
        }

        // }}}
        // {{{ ���łɃZ�b�V�������o�^����Ă�����A�Z�b�V�����ŔF��

        if (isset($_SESSION['login_user']) && isset($_SESSION['login_pass_x'])) {

            // �Z�b�V���������p����Ă���Ȃ�A�Z�b�V�����̑Ó����`�F�b�N
            if (isset($_p2session)) {
                if ($msg = $_p2session->checkSessionError()) {
                    $GLOBALS['_info_msg_ht'] .= '<p>p2 error: ' . htmlspecialchars($msg) . '</p>';
                    //Session::unSession();
                    // ���O�C�����s
                    return false;
                }
            }

            if ($this->user_u == $_SESSION['login_user']) {
                if ($_SESSION['login_pass_x'] != $this->pass_x) {
                    Session::unSession();
                    return false;

                } else {
                    return true;
                }
            }
        }

        // }}}

        $mobile = Net_UserAgent_Mobile::singleton();

        // {{{ docomo i���[�hID�F��

        /**
         * @link http://www.nttdocomo.co.jp/service/imode/make/content/ip/index.html#imodeid
         */
        if (!isset($_GET['auth_type']) || $_GET['auth_type'] == 'imodeid') {
            if ($mobile->isDoCoMo() && ($UID = $mobile->getUID()) !== null) {
                if (file_exists($_conf['auth_imodeid_file'])) {
                    include $_conf['auth_imodeid_file'];
                    if (isset($registed_imodeid) && $registed_imodeid == $UID) {
                        if (!$this->_checkIp('docomo')) {
                            p2die('�[��ID�F�؃G���[',
                                  "UA��docomo�[���ł����Ai���[�h��IP�A�h���X�ш�ƃ}�b�`���܂���B({$_SERVER['REMOTE_ADDR']})");
                        }
                        return true;
                    }
                }
            }
        }

        // }}}
        // {{{ docomo �[�������ԍ��F��

        /**
         * ���O�C���t�H�[�����͂���͗��p�����A��p�F�؃����N����̂ݗ��p
         * @link http://www.nttdocomo.co.jp/service/imode/make/content/html/tag/utn.html
         */
        if (isset($_GET['auth_type']) && $_GET['auth_type'] == 'utn') {
            if ($mobile->isDoCoMo() && ($SN = $mobile->getSerialNumber()) !== null) {
                if (file_exists($_conf['auth_docomo_file'])) {
                    include $_conf['auth_docomo_file'];
                    if (isset($registed_docomo) && $registed_docomo == $SN) {
                        if (!$this->_checkIp('docomo')) {
                            p2die('�[��ID�F�؃G���[',
                                  "UA��docomo�[���ł����Ai���[�h��IP�A�h���X�ш�ƃ}�b�`���܂���B({$_SERVER['REMOTE_ADDR']})");
                        }
                        return true;
                    }
                }
            }
        }

        // }}}
        // {{{ EZweb �T�u�X�N���C�oID�F��

        /**
         * @link http://www.au.kddi.com/ezfactory/tec/spec/4_4.html
         */
        if ($mobile->isEZweb() && ($UID = $mobile->getUID()) !== null) {
            if (file_exists($_conf['auth_ez_file'])) {
                include $_conf['auth_ez_file'];
                if (isset($registed_ez) && $registed_ez == $UID) {
                    if (!$this->_checkIp('au')) {
                        p2die('�[��ID�F�؃G���[',
                              "UA��au�[���ł����AEZweb��IP�A�h���X�ш�ƃ}�b�`���܂���B({$_SERVER['REMOTE_ADDR']})");
                    }
                    return true;
                }
            }
        }

        // }}}
        // {{{ SoftBank �[���V���A���ԍ��F��

        /**
         * �p�P�b�g�Ή��@ �v���[�UID�ʒmON�̐ݒ�
         * @link http://creation.mb.softbank.jp/web/web_ua_about.html
         */
        if ($mobile->isSoftBank() && ($SN = $mobile->getSerialNumber()) !== null) {
            if (file_exists($_conf['auth_jp_file'])) {
                include $_conf['auth_jp_file'];
                if (isset($registed_jp) && $registed_jp == $SN) {
                    if (!$this->_checkIp('softbank')) {
                        p2die('�[��ID�F�؃G���[',
                              "UA��SoftBank�[���ł����ASoftBank Mobile��IP�A�h���X�ш�ƃ}�b�`���܂���B({$_SERVER['REMOTE_ADDR']})");
                    }
                    return true;
                }
            }
        }

        // }}}
        // {{{ �t�H�[�����烍�O�C��������

        if (!empty($_POST['submit_member'])) {

            // �t�H�[�����O�C�������Ȃ�
            if ($_POST['form_login_id'] == $this->user_u and sha1($_POST['form_login_pass']) == $this->pass_x) {

                // �Â��N�b�L�[���N���A���Ă���
                $this->clearCookieAuth();

                // ���O�C�����O���L�^����
                $this->logLoginSuccess();

                return true;

            // �t�H�[�����O�C�����s�Ȃ�
            } else {
                $_info_msg_ht .= '<p class="infomsg">p2 info: ���O�C���ł��܂���ł����B<br>���[�U�����p�X���[�h���Ⴂ�܂��B</p>';
                $_login_failed_flag = true;

                // ���O�C�����s���O���L�^����
                $this->logLoginFailed();

                return false;
            }
        }

        // }}}
        // {{{ Basic�F�� (disabled)

        /*
        if (!empty($_REQUEST['basic'])) {
            if (isset($_SERVER['PHP_AUTH_USER']) and ($_SERVER['PHP_AUTH_USER'] == $this->user_u) && (sha1($_SERVER['PHP_AUTH_PW']) == $this->pass_x)) {

                // �Â��N�b�L�[���N���A���Ă���
                $this->clearCookieAuth();

                // ���O�C�����O���L�^����
                $this->logLoginSuccess();

                return true;

            } else {

                header('WWW-Authenticate: Basic realm="zone"');
                header('HTTP/1.0 401 Unauthorized');
                echo 'Login Failed. ���[�U�F�؂Ɏ��s���܂����B';

                // ���O�C�����s���O���L�^����
                $this->logLoginFailed();

                exit;
            }
        }
        */

        // }}}

        return false;
    }

    // }}}
    // {{{ logLoginSuccess()

    /**
     * ���O�C�����O���L�^����
     */
    public function logLoginSuccess()
    {
        global $_conf;

        if (!empty($_conf['login_log_rec'])) {
            $recnum = isset($_conf['login_log_rec_num']) ? intval($_conf['login_log_rec_num']) : 100;
            P2Util::recAccessLog($_conf['login_log_file'], $recnum);
        }

        return true;
    }

    // }}}
    // {{{ logLoginFailed()

    /**
     * ���O�C�����s���O���L�^����
     */
    public function logLoginFailed()
    {
        global $_conf;

        if (!empty($_conf['login_log_rec'])) {
            $recnum = isset($_conf['login_log_rec_num']) ? intval($_conf['login_log_rec_num']) : 100;
            P2Util::recAccessLog($_conf['login_failed_log_file'], $recnum, 'txt');
        }

        return true;
    }

    // }}}
    // {{{ registKtaiId()

    /**
     * �g�їp�[��ID�̔F�ؓo�^���Z�b�g����
     */
    public function registKtaiId()
    {
        global $_conf, $_info_msg_ht;

        $mobile = Net_UserAgent_Mobile::singleton();

        // {{{ �F�ؓo�^���� docomo i���[�hID & �[�������ԍ�

        if (!empty($_REQUEST['ctl_regist_imodeid']) || !empty($_REQUEST['ctl_regist_docomo'])) {
            // {{{ i���[�hID

            if (!empty($_REQUEST['ctl_regist_imodeid'])) {
                if (isset($_REQUEST['regist_imodeid']) && $_REQUEST['regist_imodeid'] == '1') {
                    if (!$mobile->isDoCoMo() || !$this->_checkIp('docomo')) {
                        p2die('�[��ID�o�^�G���[',
                              "UA��docomo�[���łȂ����Ai���[�h��IP�A�h���X�ш�ƃ}�b�`���܂���B({$_SERVER['REMOTE_ADDR']})");
                    }
                    if (($UID = $mobile->getUID()) !== null) {
                        $this->_registAuth('registed_imodeid', $UID, $_conf['auth_imodeid_file']);
                    } else {
                        $_info_msg_ht .= '<p class="infomsg">�~docomo i���[�hID�ł̔F�ؓo�^�͂ł��܂���ł���</p>'."\n";
                    }
                } else {
                    $this->_registAuthOff($_conf['auth_imodeid_file']);
                }
            }

            // }}}
            // {{{ �[�������ԍ�

            if (!empty($_REQUEST['ctl_regist_docomo'])) {
                if (isset($_REQUEST['regist_docomo']) && $_REQUEST['regist_docomo'] == '1') {
                    if (!$mobile->isDoCoMo() || !$this->_checkIp('docomo')) {
                        p2die('�[��ID�o�^�G���[',
                              "UA��docomo�[���łȂ����Ai���[�h��IP�A�h���X�ш�ƃ}�b�`���܂���B({$_SERVER['REMOTE_ADDR']})");
                    }
                    if (($SN = $mobile->getSerialNumber()) !== null) {
                        $this->_registAuth('registed_docomo', $SN, $_conf['auth_docomo_file']);
                    } else {
                        $_info_msg_ht .= '<p class="infomsg">�~docomo �[�������ԍ��ł̔F�ؓo�^�͂ł��܂���ł���</p>'."\n";
                    }
                } else {
                    $this->_registAuthOff($_conf['auth_docomo_file']);
                }
            }

            // }}}
            return;
        }

        // }}}
        // {{{ �F�ؓo�^���� EZweb �T�u�X�N���C�oID

        if (!empty($_REQUEST['ctl_regist_ez'])) {
            if (isset($_REQUEST['regist_ez']) && $_REQUEST['regist_ez'] == '1') {
                if (!$mobile->isEZweb() || !$this->_checkIp('au')) {
                    p2die('�[��ID�o�^�G���[',
                          "UA��au�[���łȂ����AEZweb��IP�A�h���X�ш�ƃ}�b�`���܂���B({$_SERVER['REMOTE_ADDR']})");
                }
                if (($UID = $mobile->getUID()) !== null) {
                    $this->_registAuth('registed_ez', $UID, $_conf['auth_ez_file']);
                } else {
                    $_info_msg_ht .= '<p class="infomsg">�~EZweb �T�u�X�N���C�oID�ł̔F�ؓo�^�͂ł��܂���ł���</p>'."\n";
                }
            } else {
                $this->_registAuthOff($_conf['auth_ez_file']);
            }
            return;
        }

        // }}}
        // {{{ �F�ؓo�^���� SoftBank �[���V���A���ԍ�

        if (!empty($_REQUEST['ctl_regist_jp'])) {
            if (isset($_REQUEST['regist_jp']) && $_REQUEST['regist_jp'] == '1') {
                if (!$mobile->isSoftBank() || !$this->_checkIp('softbank')) {
                    p2die('�[��ID�o�^�G���[',
                          "UA��SoftBank�[���łȂ����ASoftBank Mobile��IP�A�h���X�ш�ƃ}�b�`���܂���B({$_SERVER['REMOTE_ADDR']})");
                }
                if (($SN = $mobile->getSerialNumber()) !== null) {
                    $this->_registAuth('registed_jp', $SN, $_conf['auth_jp_file']);
                } else {
                    $_info_msg_ht .= '<p class="infomsg">�~SoftBank �[���V���A���ԍ��ł̔F�ؓo�^�͂ł��܂���ł���</p>'."\n";
                }
            } else {
                $this->_registAuthOff($_conf['auth_jp_file']);
            }
            return;
        }

        // }}}
    }

    // }}}
    // {{{ _registAuth()

    /**
     * �[��ID��F�؃t�@�C���o�^����
     */
    private function _registAuth($key, $sub_id, $auth_file)
    {
        global $_conf, $_info_msg_ht;

        $cont = <<<EOP
<?php
\${$key}='{$sub_id}';
?>
EOP;
        FileCtl::make_datafile($auth_file, $_conf['pass_perm']);
        $fp = fopen($auth_file, 'wb');
        if (!$fp) {
            $_info_msg_ht .= "<p>Error: �f�[�^��ۑ��ł��܂���ł����B�F�ؓo�^���s�B</p>";
            return false;
        }
        flock($fp, LOCK_EX);
        fwrite($fp, $cont);
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }

    // }}}
    // {{{ _registAuthOff()

    /**
     * �[��ID�̔F�؃t�@�C���o�^���O��
     */
    private function _registAuthOff($auth_file)
    {
        if (file_exists($auth_file)) {
            unlink($auth_file);
        }
    }

    // }}}
    // {{{ makeUser()

    /**
     * �V�K���[�U���쐬����
     */
    public function makeUser($user_u, $pass)
    {
        global $_conf;

        $crypted_login_pass = sha1($pass);
        $auth_user_cont = <<<EOP
<?php
\$rec_login_user_u = '{$user_u}';
\$rec_login_pass_x = '{$crypted_login_pass}';
?>
EOP;
        FileCtl::make_datafile($_conf['auth_user_file'], $_conf['pass_perm']); // �t�@�C�����Ȃ���ΐ���
        if (FileCtl::file_write_contents($_conf['auth_user_file'], $auth_user_cont) === false) {
            p2die("{$_conf['auth_user_file']} ��ۑ��ł��܂���ł����B�F��{$p_str['user']}�o�^���s�B");
        }

        return true;
    }

    // }}}
    // {{{ registCookie()

    /**
     * cookie�F�؂�o�^/��������
     *
     * @param void
     * @return boolean
     */
    public function registCookie()
    {
        $r = true;

        if (!empty($_REQUEST['ctl_regist_cookie'])) {
            if ($_REQUEST['regist_cookie'] == '1') {
                $ignore_cip = false;
                if (!empty($_POST['ignore_cip'])) {
                    $ignore_cip = true;
                }
                $r = $this->setCookieCid($this->user_u, $this->pass_x, $ignore_cip);
            } else {
                // �N�b�L�[���N���A
                $r = $this->clearCookieAuth();
            }
        }

        return $r;
    }

    // }}}
    // {{{ clearCookieAuth()

    /**
     * Cookie�F�؂��N���A����
     */
    public function clearCookieAuth()
    {
        setcookie('cid', '', time() - 3600);
        /*
        setcookie('p2_user', '', time() - 3600);    //  �p�~�v�f 2005/6/13
        setcookie('p2_pass', '', time() - 3600);    //  �p�~�v�f 2005/6/13
        setcookie('p2_pass_x', '', time() - 3600);  //  �p�~�v�f 2005/6/13
        */
        $_COOKIE = array();

        return true;
    }

    // }}}
    // {{{ setCookieCid()

    /**
     * CID��cookie�ɃZ�b�g����
     *
     * @param string $user_u
     * @param string $pass_x
     * @param boolean|null $ignore_cip
     * @return boolean
     */
    protected function setCookieCid($user_u, $pass_x, $ignore_cip = null)
    {
        global $_conf;

        $time = time() + 60*60*24 * $_conf['cid_expire_day'];

        if (!is_null($ignore_cip)) {
            if ($ignore_cip) {
                P2Util::setCookie('ignore_cip', '1', $time);
                $_COOKIE['ignore_cip'] = '1';
            } else {
                P2Util::unsetCookie('ignore_cip');
                // �O�̂��߃h���C���w��Ȃ���
                setcookie('ignore_cip', '', time() - 3600);
            }
        }

        if ($cid = $this->makeCid($user_u, $pass_x)) {
            return P2Util::setCookie('cid', $cid, $time);
        }
        return false;
    }

    // }}}
    // {{{ makeCid()

    /**
     * ID��PASS�Ǝ��Ԃ�����߂ĈÍ�������Cookie���iCID�j�𐶐��擾����
     *
     * @return mixed
     */
    public function makeCid($user_u, $pass_x)
    {
        if (is_null($user_u) || is_null($pass_x)) {
            return false;
        }

        $user_time  = $user_u . ':' . time() . ':';
        $md5_utpx = md5($user_time . $pass_x);
        $cid_src  = $user_time . $md5_utpx;
        return $cid = MD5Crypt::encrypt($cid_src, self::getMd5CryptPassForCid());
    }

    // }}}
    // {{{ getCidInfo()

    /**
     * Cookie�iCID�j���烆�[�U���𓾂�
     *
     * @return array|false ��������Δz��A���s�Ȃ� false ��Ԃ�
     */
    public function getCidInfo($cid)
    {
        global $_conf;

        $dec = MD5Crypt::decrypt($cid, self::getMd5CryptPassForCid());

        $user = $time = $md5_utpx = null;
        list($user, $time, $md5_utpx) = explode(':', $dec, 3);
        if (!strlen($user) || !$time || !$md5_utpx) {
            return false;
        }

        // �L������ ����
        if (time() > $time + (60*60*24 * $_conf['cid_expire_day'])) {
            return false; // �����؂�
        }
        return array($user, $time, $md5_utpx);
    }

    // }}}
    // {{{ getUserFromCid()

    /**
     * Cookie���iCID�j����user�𓾂�
     *
     * @return mixed
     */
    public function getUserFromCid($cid)
    {
        if (!$ar = $this->getCidInfo($cid)) {
            return false;
        }

        return $user = $ar[0];
    }

    // }}}
    // {{{ checkUserPwWithCid()

    /**
     * Cookie���iCID�j��user, pass���ƍ�����
     *
     * @return boolean
     */
    public function checkUserPwWithCid($cid)
    {
        global $_conf;

        if (is_null($this->user_u) || is_null($this->pass_x) || is_null($cid)) {
            return false;
        }

        if (!$ar = $this->getCidInfo($cid)) {
            return false;
        }

        $time = $ar[1];
        $pw_enc = $ar[2];

        // PW���ƍ�
        if ($pw_enc == md5($this->user_u . ':' . $time . ':' . $this->pass_x)) {
            return true;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ getMd5CryptPassForCid()

    /**
     * MD5Crypt::encrypt, MD5Crypt::decrypt �̂��߂� password(salt) �𓾂�
     * �i�N�b�L�[��cid�̐����ɗ��p���Ă���j
     *
     * @param   void
     * @access  private
     * @return  string
     */
    static private function getMd5CryptPassForCid()
    {
        static $pass = null;

        if ($pass !== null) {
            return $pass;
        }

        $seed = $_SERVER['SERVER_SOFTWARE'];

        // IP�`�F�b�N�Ȃ��̏ꍇ��
        if (!empty($_COOKIE['ignore_cip'])) {
            ;
        // �g�є��肳�ꂽ�ꍇ�́A IP�`�F�b�N�Ȃ�
        } elseif (
            //!$_conf['cid_seed_ip'] or
            UA::isK(geti($_SERVER['HTTP_USER_AGENT']))
            || HostCheck::isAddressMobile()
        ) {
            ;
        } else {
            $now_ips = explode('.', $_SERVER['REMOTE_ADDR']);
            $seed .= $now_ips[0];
        }

        $pass = md5($seed, true);

        return $pass;
    }

    // }}}
    // {{{ _checkIp()

    /**
     * IP�A�h���X�ш�̌��؂�����
     *
     * @param string $type
     * @return bool
     */
    private function _checkIp($type)
    {
        $method = 'isAddress' . ucfirst(strtolower($type));
        if (method_exists('HostCheck', $method)) {
            return HostCheck::$method();
        }

        // �����ɗ��Ȃ��悤�Ɉ������L�q���邱��
        p2die('Login::_checkIp() Failure', "Invalid argument ({$type}).");
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
