<?php
require_once 'HTTP/Client.php';

// {{{ P2Client

/**
 * p2.2ch.net �N���C�A���g
 */
class P2Client
{
    // {{{ constants

    /**
     * Cookie��ۑ�����SQLite3�f�[�^�x�[�X�̃t�@�C����
     */
    const COOKIE_STORE_NAME = 'p2_2ch_net_cookies.sqlite3';

    /**
     * ����p2��URI�Ɗe�G���g���|�C���g
     */
    const P2_ROOT_URI = 'http://p2.2ch.net/p2/';
    const SCRIPT_NAME_READ = 'read.php';
    const SCRIPT_NAME_POST = 'post.php';
    const SCRIPT_NAME_INFO = 'info.php';
    const SCRIPT_NAME_DAT  = 'dat.php';

    /**
     * User-Agent
     */
    const HTTP_USER_AGENT = 'PHP P2Client class';

    /**
     * HTTP���N�G�X�g�̃p�����[�^��
     */
    const REQUEST_PARAMETER_LOGIN_ID    = 'form_login_id';
    const REQUEST_PARAMETER_LOGIN_PASS  = 'form_login_pass';
    const REQUEST_PARAMETER_LOGIN_REGIST_COOKIE = 'regist_cookie';
    const REQUEST_PARAMETER_LOGIN_IGNORE_COOKIE_ADDR = 'ignore_cip';
    const REQUEST_PARAMETER_HOST    = 'host';
    const REQUEST_PARAMETER_BBS     = 'bbs';
    const REQUEST_PARAMETER_KEY     = 'key';
    const REQUEST_PARAMETER_LS      = 'ls';
    const REQUEST_PARAMETER_NAME    = 'FROM';
    const REQUEST_PARAMETER_MAIL    = 'mail';
    const REQUEST_PARAMETER_MESSAGE = 'MESSAGE';
    const REQUEST_PARAMETER_POPUP   = 'popup';
    const REQUEST_PARAMETER_BERES   = 'submit_beres';
    const REQUEST_PARAMETER_CHARACTER_SET_DETECTION_HINT = 'detect_hint';

    /**
     * HTTP���N�G�X�g�̌Œ�p�����[�^
     */
    const REQUEST_DATA_CHARACTER_SET_DETECTION_HINT = '����';
    const REQUEST_DATA_LS_LAST1_NO_FIRST = 'l1n';

    /**
     * �ǂݍ��ݐ��۔���̂��߂̕�����
     */
    const NEEDLE_READ_NO_THREAD = '<b>p2 info - �T�[�o����ŐV�̃X���b�h�����擾�ł��܂���ł����B</b>';
    const NEEDLE_DAT_NO_DAT = '<h4>p2 error: ���w���DAT�͂���܂���ł���</h4>';

    /**
     * �������ݐ��۔���̂��߂̐��K�\��
     */
    const REGEX_POST_SUCCESS = '{<title>.*(?:����(?:��|��)�݂܂���|�������ݏI�� - SubAll BBS).*</title>}is';
    const REGEX_POST_COOKIE  = '{<!-- 2ch_X:cookie -->|<title>�� �������݊m�F ��</title>|>�������݊m�F�B<}';

    // }}}
    // {{{ properties

    /**
     * p2.2ch.net/�����^�| ���O�C��ID (���[���A�h���X)
     *
     * @var string
     */
    private $_loginId;

    /**
     * p2.2ch.net/�����^�| ���O�C���p�X���[�h
     *
     * @var string
     */
    private $_loginPass;

    /**
     * p2.2ch.net Cookie�F�؎���IP�A�h���X�̓��ꐫ���`�F�b�N���Ȃ�
     *
     * @var bool
     */
    private $_ignoreCookieAddr;

    /**
     * Cookie��ۑ�����Key-Value Store�I�u�W�F�N�g
     *
     * @var P2KeyValueStore_Serializing
     */
    private $_cookieStore;

    /**
     * Cookie���Ǘ�����I�u�W�F�N�g
     *
     * @var HTTP_Client_CookieManager
     */
    private $_cookieManager;

    /**
     * HTTP�N���C�A���g�I�u�W�F�N�g
     *
     * @var HTTP_Client
     */
    private $_httpClient;

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     *
     * @param string $loginId
     * @param string $loginPass
     * @param string $cookieSaveDir
     * @param bool $ignoreCookieAddr
     * @throws P2Exception
     */
    public function __construct($loginId, $loginPass, $cookieSaveDir, $ignoreCookieAddr = false)
    {
        try {
            $cookieSavePath = $cookieSaveDir . DIRECTORY_SEPARATOR . self::COOKIE_STORE_NAME;
            $cookieStore = P2KeyValueStore::getStore($cookieSavePath,
                                                     P2KeyValueStore::CODEC_SERIALIZING);
        } catch (Exception $e) {
            throw new P2Exception(get_class($e) . ': ' . $e->getMessage());
        }

        if ($cookieManager = $cookieStore->get($loginId)) {
            if (!($cookieManager instanceof HTTP_Client_CookieManager)) {
                $cookieStore->delete($loginId);
                throw new Exception('Cannot restore the cookie manager.');
            }
        } else {
            $cookieManager = new HTTP_Client_CookieManager;
        }

        $this->_loginId = $loginId;
        $this->_loginPass = $loginPass;
        $this->_cookieStore = $cookieStore;
        $this->_cookieManager = $cookieManager;
        $this->_ignoreCookieAddr = $ignoreCookieAddr;

        $defaultHeaders = array(
            'User-Agent' => self::HTTP_USER_AGENT,
        );
        $this->_httpClient = new HTTP_Client(null, $defaultHeaders, $cookieManager);
    }

    // }}}
    // {{{ destructor

    /**
     * �f�[�^�x�[�X��Cookie��ۑ�����
     *
     * @param void
     */
    public function __destruct()
    {
        $this->_cookieStore->set($this->_loginId, $this->_cookieManager);
    }

    // }}}
    // {{{ login()

    /**
     * ����p2�Ƀ��O�C������
     *
     * @param string $uri
     * @param array $data
     * @param P2DOM $dom
     * @param DOMElement $form
     * @param mixed &$response
     * @return bool
     * @throws P2Exception
     */
    public function login($uri = null, array $data = array(),
                          P2DOM $dom = null, DOMElement $form = null,
                          &$response = null)
    {
        if ($uri === null) {
            $uri = self::P2_ROOT_URI;
        }

        if ($dom === null) {
            $response = $this->httpGet($uri);
            $dom = new P2DOM($response['body']);
            $form = null;
        }

        if ($form === null) {
            $form = $this->getLoginForm($dom);
            if ($form === null) {
                throw new P2Exception('Login form not found.');
            }
        }

        $postData = array();
        foreach ($data as $name => $value) {
            $postData[$name] = rawurlencode($value);
        }
        $postData = $this->getFormValues($dom, $form, $postData);
        $postData[self::REQUEST_PARAMETER_LOGIN_ID] = rawurlencode($this->_loginId);
        $postData[self::REQUEST_PARAMETER_LOGIN_PASS] = rawurlencode($this->_loginPass);
        $postData[self::REQUEST_PARAMETER_LOGIN_REGIST_COOKIE] = '1';
        if ($this->_ignoreCookieAddr) {
            $postData[self::REQUEST_PARAMETER_LOGIN_IGNORE_COOKIE_ADDR] = '1';
        } elseif (array_key_exists(self::REQUEST_PARAMETER_LOGIN_IGNORE_COOKIE_ADDR, $postData)) {
            unset($postData[self::REQUEST_PARAMETER_LOGIN_IGNORE_COOKIE_ADDR]);
        }

        $response = $this->httpPost($uri, $postData, true);

        return $this->getLoginForm(new P2DOM($response['body'])) === null;
    }

    // }}}
    // {{{ readThread()

    /**
     * �X���b�h��ǂ�
     *
     * @param string $host
     * @param string $bbs
     * @param string $key
     * @param string $ls
     * @param mixed &$response
     * @return string HTTP���X�|���X�{�f�B
     * @throws P2Exception
     */
    public function readThread($host, $bbs, $key, $ls = '1', &$response = null)
    {
        $getData = $this->setupGetData($host, $bbs, $key, $ls);
        $uri = self::P2_ROOT_URI . self::SCRIPT_NAME_READ;
        $response = $this->httpGet($uri, $getData, true);
        $dom = new P2DOM($response['body']);

        if ($form = $this->getLoginForm($dom)) {
            if (!$this->login($uri, $getData, $dom, $form, $response)) {
                throw new P2Exception('Login failed.');
            }
        }

        if (strpos($response['body'], self::NEEDLE_READ_NO_THREAD) !== false) {
            return null;
        }

        return $response['body'];
    }

    // }}}
    // {{{ downloadDat()

    /**
     * dat����荞��
     *
     * dat�擾�����������ꍇ�͎����Ń����^�|�������dat���擾����B
     * ���s���Ă������Ȃ��B
     *
     * @param string $host
     * @param string $bbs
     * @param string $key
     * @param mixed &$response
     * @return string ��dat
     * @throws P2Exception
     */
    public function downloadDat($host, $bbs, $key, &$response = null)
    {
        // �X���b�h�̗L�����m���߂邽�߁A�܂� read.php ��@���B
        // dat������Ƀz�X�g���ړ]�����ꍇ�A�ړ]��̃z�X�g���ŃA�N�Z�X���Ă�
        // �X���b�h�����擾�ł��Ȃ������Ƃ̃��b�Z�[�W���\�������B
        $html = $this->readThread($host, $bbs, $key,
                                  self::REQUEST_DATA_LS_LAST1_NO_FIRST,
                                  $response);
        if ($html === null) {
            return null;
        }

        // �u�����^�|��p2�Ɏ�荞�ށv�����N�̗L���𒲂ׂ�B
        // �����ꍇ��dat�擾������������̂Ƃ���B
        // dat�擾�������Ȃ��ꍇ�⃂���^�|�ʒ��̎c��������Ȃ��ꍇ�̏����͒[�܂�B
        $dom = new P2DOM($html);
        $expression = './/a[contains(@href, "' . self::SCRIPT_NAME_READ . '?")'
                    . ' and contains(@href, "&moritapodat=")]';
        $result = $dom->query($expression);
        if (($result instanceof DOMNodeList) && $result->length > 0) {
            $anchor = $result->item(0);
            $uri = self::P2_ROOT_URI
                 . strstr($anchor->getAttribute('href'), self::SCRIPT_NAME_READ);
            $response = $this->httpGet($uri);
        }

        // dat���擾����B
        $getData = $this->setupGetData($host, $bbs, $key);
        $uri = self::P2_ROOT_URI . self::SCRIPT_NAME_DAT;
        $response = $this->httpGet($uri, $getData, true);

        if (strpos($response['body'], self::NEEDLE_DAT_NO_DAT) !== false) {
            return null;
        }

        return $response['body'];
    }

    // }}}
    // {{{ post()

    /**
     * �X���b�h�ɏ�������
     *
     * @param string $host
     * @param string $bbs
     * @param string $key
     * @param string $name
     * @param string $mail
     * @param string $message
     * @param bool $beRes
     * @param mixed &$response
     * @return bool
     * @throws P2Exception
     */
    public function post($host, $bbs, $key, $name, $mail, $message,
                         $beRes = false, &$response = null)
    {
        // csrfId���擾���A������p2�̊��ǂ��ŐV�̏�Ԃɂ��邽�߁A�܂� read.php ��@���B
        // �ʐM�ʂ�ߖ�ł���悤�� ls=l1n �Ƃ��Ă���B
        // popup=1 �͏������݌�̃y�[�W�Ƀ��_�C���N�g�����Ȃ����߁B
        $html = $this->readThread($host, $bbs, $key,
                                  self::REQUEST_DATA_LS_LAST1_NO_FIRST,
                                  $response);
        if ($html === null) {
            return false;
        }

        $dom = new P2DOM($html);
        $form = $this->getPostForm($dom);
        if ($form === null) {
            throw new P2Exception('Post form not found.');
        }

        // POST����f�[�^��p�ӁB
        $postData = $this->setupPostData($dom, $form, $name, $mail, $message);
        $postData[self::REQUEST_PARAMETER_POPUP] = '1';
        if ($beRes) {
            $postData[self::REQUEST_PARAMETER_BERES] = '1';
        } elseif (array_key_exists(self::REQUEST_PARAMETER_BERES, $postData)) {
            unset($postData[self::REQUEST_PARAMETER_BERES]);
        }

        // POST���s�B
        $uri = self::P2_ROOT_URI . self::SCRIPT_NAME_POST;
        $response = $this->httpPost($uri, $postData, true);

        // Cookie�m�F�̏ꍇ�͍�POST�B
        if (preg_match(self::REGEX_POST_COOKIE, $response['body'])) {
            $html = str_replace('<META http-equiv="Content-Type" content="text/html; charset=x-sjis">',
                                '<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">',
                                $response['body']);
            $dom = new P2DOM($html);
            $expression = './/form[contains(@action, "' . self::SCRIPT_NAME_POST . '")]';
            $result = $dom->query($expression);
            if (($result instanceof DOMNodeList) && $result->length > 0) {
                $postData = $this->setupPostData($dom, $result->item(0), $name, $mail, $message);
                $response = $this->httpPost($uri, $postData, true);
            } else {
                return false;
            }
        }

        return (bool)preg_match(self::REGEX_POST_SUCCESS, $response['body']);
    }

    // }}}
    // {{{ httpGet()

    /**
     * HTTP_Client::get() �̃��b�p�[���\�b�h
     *
     * @param string $uri
     * @param mixed $data
     * @param bool $preEncoded
     * @param array $headers
     * @return array HTTP���X�|���X
     * @throws P2Exception
     */
    protected function httpGet($uri, $data = null, $preEncoded = false,
                               $headers = array())
    {
        $code = $this->_httpClient->get($uri, $data, $preEncoded, $headers);
        P2Exception::pearErrorToP2Exception($code);
        if ($code != 200) {
            throw new P2Exception('HTTP '. $code);
        }
        return $this->_httpClient->currentResponse();
    }

    // }}}
    // {{{ httpPost()

    /**
     * HTTP_Client::post() �̃��b�p�[���\�b�h
     *
     * @param string $uri
     * @param mixed $data
     * @param bool $preEncoded
     * @param array $files
     * @param array $headers
     * @return array HTTP���X�|���X
     * @throws P2Exception
     */
    protected function httpPost($uri, $data, $preEncoded = false,
                                $files = array(), $headers = array())
    {
        $code = $this->_httpClient->post($uri, $data, $preEncoded, $files, $headers);
        P2Exception::pearErrorToP2Exception($code);
        if ($code != 200) {
            throw new P2Exception('HTTP '. $code);
        }
        return $this->_httpClient->currentResponse();
    }

    // }}}
    // {{{ getLoginForm()

    /**
     * ���O�C���t�H�[���𒊏o����
     *
     * @paramP2DOM $dom
     * @return DOMElement|null
     */
    protected function getLoginForm(P2DOM $dom)
    {
        $result = $dom->query('.//form[@action and @id="login"]');
        if (($result instanceof DOMNodeList) && $result->length > 0) {
            return $result->item(0);
        }
        return null;
    }

    // }}}
    // {{{ getPostForm()

    /**
     * read.php/post_form.php �̏o�͂��珑�����݃t�H�[���𒊏o����
     *
     * @paramP2DOM $dom
     * @return DOMElement|null
     */
    protected function getPostForm(P2DOM $dom)
    {
        $result = $dom->query('.//form[@action and @id="resform"]');
        if (($result instanceof DOMNodeList) && $result->length > 0) {
            return $result->item(0);
        }
        return null;
    }

    // }}}
    // {{{ getFormValues()

    /**
     * �t�H�[������input�v�f�𒊏o���A�A�z�z��𐶐�����
     *
     * select�v�f��textarea�v�f�͖�������B
     * �܂��A<input type="checkbox" name="foo[]" value="bar"> �̂悤��
     * name�����Ŕz����w�����Ă�����̂͐����������Ȃ��B
     * (���̃N���X���̂������������v�f�������K�v�̂���ꍇ���l�����Ă��Ȃ�)
     *
     * @param P2DOM $dom
     * @param DOMElement $form
     * @param array $data
     * @param bool $raw
     * @return array
     */
    protected function getFormValues(P2DOM $dom, DOMElement $form,
                                     array $data = array(), $raw = false)
    {
        $fields = $dom->query('.//input[@name and @value]', $form);
        foreach ($fields as $field) {
            $name = $field->getAttribute('name');
            $value = $field->getAttribute('value');
            if (!$raw) {
                $value = rawurlencode(mb_convert_encoding($value, 'SJIS-win', 'UTF-8'));
            }
            $data[$name] = $value;
        }

        return $data;
    }

    // }}}
    // {{{ setupGetData()

    /**
     * �X���b�h��ǂނ��߂̋��ʃp�����[�^�̔z��𐶐�����
     *
     * @param string $host
     * @param string $bbs
     * @param string $key
     * @return array
     */
    protected function setupGetData($host, $bbs, $key, $ls = null)
    {
        $data = array(
            self::REQUEST_PARAMETER_HOST => rawurlencode($host),
            self::REQUEST_PARAMETER_BBS => rawurlencode($bbs),
            self::REQUEST_PARAMETER_KEY => rawurlencode($key),
        );
        if ($ls !== null) {
            $data[self::REQUEST_PARAMETER_LS] = rawurlencode($ls);
        }

        return $data;
    }

    // }}}
    // {{{ setupPostData()

    /**
     * �X���b�h�ɏ������ނ��߂̋��ʃp�����[�^�̔z��𐶐�����
     *
     * @param P2DOM $dom
     * @param DOMElement $form
     * @param string $key
     * @param string $name
     * @param string $mail
     * @param string $message
     * @return array
     */
    protected function setupPostData(P2DOM $dom, DOMElement $form,
                                     $name, $mail, $message)
    {
        $data = $this->getFormValues($dom, $form);
        $data[self::REQUEST_PARAMETER_CHARACTER_SET_DETECTION_HINT] =
            rawurlencode(self::REQUEST_DATA_CHARACTER_SET_DETECTION_HINT);
        $data[self::REQUEST_PARAMETER_NAME] = rawurlencode($name);
        $data[self::REQUEST_PARAMETER_MAIL] = rawurlencode($mail);
        $data[self::REQUEST_PARAMETER_MESSAGE] = rawurlencode($message);

        return $data;
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
