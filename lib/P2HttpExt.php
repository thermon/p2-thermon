<?php
/**
 * rep2expack feat. pecl_http
 */

// {{{ P2HttpExt

/**
 * �_�~�[�N���X
 */
class P2HttpExt
{
    // {{{ constants

    const DEBUG = 0;

    // }}}
    // {{{ activate()

    /**
     * ���̃t�@�C���Ɋ܂܂��N���X�𗘗p����Ƃ���
     * �܂� P2HttpExt::activate() ���R�[������
     * �I�[�g���[�_�[�ɂ�肱�̃t�@�C����ǂݍ��ށB
     *
     * @param void
     * @return void
     */
    static public function activate()
    {
        // nothing to do
    }

    // }}}
}

// }}}
// {{{ P2HttpCallback

/**
 * �R�[���o�b�N�p���ۃN���X
 */
abstract class P2HttpCallback
{
    // {{{ invoke()

    /**
     * �R�[���o�b�N���\�b�h
     *
     * @param P2HttpGet $req
     * @return void
     */
    abstract public function invoke(P2HttpGet $req);

    // }}}
    // {{{ __invoke()

    /**
     * PHP version >= 5.3 �p���\�b�h
     *
     * $instance($req); �Ƃ��ăC���X�^���X���֐��̂悤�Ɏg����
     *
     * @param P2HttpGet $req
     * @return void
     */
    final public function __invoke(P2HttpGet $req)
    {
        $this->invoke($req);
    }

    // }}}
}

// }}}
// {{{ P2HttpCallback_SaveEucjpAsSjis

/**
 * EUC-JP�̃��X�|���X�{�f�B��Shift_JIS�ɕϊ����ăt�@�C���ɕۑ�����
 */
class P2HttpCallback_SaveEucjpAsSjis extends P2HttpCallback
{
    // {{{ invoke()

    /**
     * EUC-JP�̃��X�|���X�{�f�B��Shift_JIS�ɕϊ����ăt�@�C���ɕۑ�����
     *
     * CP51932�̃T�|�[�g��PHP 5.2.1����
     *
     * @param P2HttpGet $req
     * @return void
     */
    public function invoke(P2HttpGet $req)
    {
        $destination = $req->getFileDestination();
        file_put_contents($destination,
                          mb_convert_encoding($req->getResponseBody(), 'CP932', 'CP51932'),
                          LOCK_EX
                          );
        chmod($destination, $req->getFilePermission());
    }

    // }}}
}

// }}}
// {{{ P2HttpCallback_SaveUtf8AsSjis

/**
 * UTF-8�̃��X�|���X�{�f�B��Shift_JIS�ɕϊ����ăt�@�C���ɕۑ�����
 */
class P2HttpCallback_SaveUtf8AsSjis extends P2HttpCallback
{
    // {{{ invoke()

    /**
     * UTF-8�̃��X�|���X�{�f�B��Shift_JIS�ɕϊ����ăt�@�C���ɕۑ�����
     *
     * @param P2HttpGet $req
     * @return void
     */
    public function invoke(P2HttpGet $req)
    {
        $destination = $req->getFileDestination();
        file_put_contents($destination,
                          mb_convert_encoding($req->getResponseBody(), 'CP932', 'UTF-8'),
                          LOCK_EX
                          );
        chmod($destination, $req->getFilePermission());
    }

    // }}}
}

// }}}
// {{{ P2HttpGet

/**
 * HTTP GET
 */
class P2HttpGet extends HttpRequest
{
    // {{{ constants

    /**
     * �G���[�R�[�h�F�f�o�b�O
     */
    const E_DEBUG = -1;

    /**
     * �G���[�R�[�h�F�G���[�Ȃ�
     */
    const E_NONE = 0;

    /**
     * �G���[�R�[�h�FHTTP�G���[
     */
    const E_HTTP = 1;

    /**
     * �G���[�R�[�h�F�ڑ����s
     */
    const E_CONNECTION = 2;

    /**
     * �G���[�R�[�h�F��O����
     */
    const E_EXCEPTION = 3;

    // }}}
    // {{{ properties

    /**
     * �_�E�����[�h�����f�[�^��ۑ�����p�X
     *
     * @var string
     */
    private $_destination;

    /**
     * �_�E�����[�h�����f�[�^��ۑ�����ۂ̃p�[�~�b�V����
     *
     * @var int
     */
    private $_permission;

    /**
     * �G���[�R�[�h
     *
     * @var int
     */
    private $_errorCode;

    /**
     * �G���[���
     *
     * @var string
     */
    private $_errorInfo;

    /**
     * ���X�|���X�R�[�h��200�̎��̃R�[���o�b�N�I�u�W�F�N�g
     *
     * @var P2HttpCallback
     */
    private $_onSuccess;

    /**
     * ���X�|���X�R�[�h��200�ȊO�̎��̃R�[���o�b�N�I�u�W�F�N�g
     *
     * @var P2HttpCallback
     */
    private $_onFailure;

    /**
     * ���Ɏ��s���郊�N�G�X�g
     *
     * @var P2HttpGet
     */
    private $_next;

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     *
     * @param string $url
     * @param string $destination
     * @param array $options
     * @param P2HttpCallback $onSuccess
     * @param P2HttpCallback $onFailure
     */
    public function __construct($url,
                                $destination,
                                array $options = null,
                                P2HttpCallback $onSuccess = null,
                                P2HttpCallback $onFailure = null
                                )
    {
        global $_conf;

        if ($options === null) {
            $options = array();
        }

        if (!isset($options['connecttimeout'])) {
            $options['connecttimeout'] = $_conf['fsockopen_time_limit'];
        }

        if (!isset($options['timeout'])) {
            $options['timeout'] = $_conf['fsockopen_time_limit'] * 2;
        }

        if (!isset($options['compress'])) {
            $options['compress'] = true;
        }

        if (!isset($options['useragent'])) {
            $options['useragent'] = "Monazilla/1.00 ({$_conf['p2ua']})";
        }

        if ($_conf['proxy_use'] && !isset($options['proxyhost']) && !empty($_conf['proxy_host'])) {
            $options['proxyhost'] = $_conf['proxy_host'];
            if (!empty($_conf['proxy_port']) && is_numeric($_conf['proxy_port'])) {
                $options['proxyport'] = (int)$_conf['proxy_port'];
            } elseif (strpos($_conf['proxy_host'], ':') === false) {
                $options['proxyport'] = 80;
            }
            /*
            $options['proxytype'] = HTTP_PROXY_HTTP;
            if (isset($_conf['proxy_type'])) {
                switch ($_conf['proxy_type']) {
                case 'http':   $options['proxytype'] = HTTP_PROXY_HTTP;   break;
                case 'socks4': $options['proxytype'] = HTTP_PROXY_SOCKS4; break;
                case 'socks5': $options['proxytype'] = HTTP_PROXY_SOCKS5; break;
                default:
                    if (is_numeric($options['proxytype'])) {
                        $options['proxytype'] = (int)$_conf['proxy_type'];
                    }
                }
            }

            if (!empty($_conf['proxy_auth'])) {
                $options['proxy_auth'] = $_conf['proxy_auth'];
                $options['proxyauthtype'] = HTTP_AUTH_BASIC;
                if (isset($_conf['proxy_auth_type'])) {
                    switch ($_conf['proxy_auth_type']) {
                    case 'basic':  $options['proxyauthtype'] = HTTP_AUTH_BASIC;  break;
                    case 'digest': $options['proxyauthtype'] = HTTP_AUTH_DIGEST; break;
                    case 'ntlm':   $options['proxyauthtype'] = HTTP_AUTH_NTLM;   break;
                    case 'gssneg': $options['proxyauthtype'] = HTTP_AUTH_GSSNEG; break;
                    case 'any':    $options['proxyauthtype'] = HTTP_AUTH_ANY;    break;
                    default:
                        if (is_numeric($options['proxytype'])) {
                            $options['proxyauthtype'] = (int)$_conf['proxy_auth_type'];
                        }
                    }
                }
            }
            */
        }

        if (!isset($options['lastmodified']) && file_exists($destination)) {
            $options['lastmodified'] = filemtime($destination);
        } else {
            FileCtl::mkdir_for($destination);
        }

        $this->_destination = $destination;
        $this->_permission = !empty($_conf['dl_perm']) ? $_conf['dl_perm'] : 0666;
        $this->_errorCode = self::E_NONE;
        $this->_errorInfo = '';
        $this->_onSuccess = $onSuccess;
        $this->_onFailure = $onFailure;
        $this->_next = null;

        parent::__construct($url, HttpRequest::METH_GET, $options);
    }

    // }}}
    // {{{ __toString()

    /**
     * �I�u�W�F�N�g�̕�����\�L���擾����
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s: %s => %s', get_class($this), $this->getUrl(), $this->_destination);
    }

    // }}}
    // {{{ send()

    /**
     * ���N�G�X�g�𑗐M����
     *
     * @return HttpMessage
     */
    public function send()
    {
        try {
            return parent::send();
        } catch (HttpException $e) {
            if ($this->getResponseCode() == 0) {
                $this->onFinish(false);
            } else {
                $this->setError(sprintf('%s (%d) %s',
                                        get_class($e),
                                        $e->getCode(),
                                        htmlspecialchars($e->getMessage(), ENT_QUOTES)
                                        ),
                                self::E_EXCEPTION
                                );
            }
            return false;
        }
    }

    // }}}
    // {{{ onFinish()

    /**
     * ���N�G�X�g�I�����Ɏ����ŌĂяo�����R�[���o�b�N���\�b�h
     *
     * @param bool $success
     * @return void
     */
    public function onFinish($success)
    {
        if ($success) {
            if (($code = $this->getResponseCode()) == 200) {
                if ($this->_onSuccess) {
                    //$this->_onSuccess($this); // PHP >= 5.3
                    $this->_onSuccess->invoke($this);
                } else {
                    file_put_contents($this->_destination, $this->getResponseBody(), LOCK_EX);
                    chmod($this->_destination, $this->_permission);
                }
            } else {
                if ($this->_onFailure) {
                    //$this->_onFailure($this); // PHP >= 5.3
                    $this->_onFailure->invoke($this);
                } elseif ($code == 304) {
                    //touch($this->_destination);
                } else {
                    $this->setError(sprintf('HTTP %d %s', $code, $this->getResponseStatus()),
                                    self::E_HTTP
                                    );
                }
            }
            if (P2HttpExt::DEBUG && !$this->hasError()) {
                $this->setError(sprintf('HTTP %d %s', $code, $this->getResponseStatus()),
                                self::E_DEBUG
                                );
            }
        } else {
            $this->setError('HTTP Connection Error!', self::E_CONNECTION);
            $this->setNext(null);
        }
    }

    // }}}
    // {{{ getFileDestination()

    /**
     * �_�E�����[�h�����f�[�^��ۑ�����ۂ̃p�X���擾����
     *
     * @return string
     */
    public function getFileDestination()
    {
        return $this->_destination;
    }

    // }}}
    // {{{ getFilePermission()

    /**
     * �_�E�����[�h�����f�[�^��ۑ�����ۂ̃p�[�~�b�V�������擾����
     *
     * @return int
     */
    public function getFilePermission()
    {
        return $this->_permission;
    }

    // }}}
    // {{{ setFileDestination()

    /**
     * �_�E�����[�h�����f�[�^��ۑ�����ۂ̃p�X��ݒ肷��
     *
     * @param string $destination
     * @return void
     */
    public function setFileDestination($destination)
    {
        $this->_destination = $destination;
    }

    // }}}
    // {{{ setFilePermission()

    /**
     * �_�E�����[�h�����f�[�^��ۑ�����ۂ̃p�[�~�b�V������ݒ肷��
     *
     * @param int $permission
     * @return void
     */
    public function setFilePermission($permission)
    {
        $this->_permission = $permission;
    }

    // }}}
    // {{{ getErrorCode()

    /**
     * �G���[�R�[�h���擾����
     *
     * @return int
     */
    public function getErrorCode()
    {
        return $this->_errorCode;
    }

    // }}}
    // {{{ getErrorInfo()

    /**
     * �G���[�����擾����
     *
     * @return string
     */
    public function getErrorInfo()
    {
        return $this->_errorInfo;
    }

    // }}}
    // {{{ setError()

    /**
     * �G���[����ݒ肷��
     *
     * @param string $info
     * @param int $code
     * @return void
     */
    public function setError($info, $code)
    {
        $this->_errorCode = $code;
        $this->_errorInfo = $info;
    }

    // }}}
    // {{{ hasError()

    /**
     * �G���[�̗L�����`�F�b�N����
     *
     * @return bool
     */
    public function hasError()
    {
        return ($this->_errorCode != self::E_NONE);
    }

    // }}}
    // {{{ getNext()

    /**
     * ���̃��N�G�X�g���擾����
     *
     * @return P2HttpGet
     */
    public function getNext()
    {
        return $this->_next;
    }

    // }}}
    // {{{ setNext()

    /**
     * ���̃��N�G�X�g��ݒ肷��
     *
     * @param P2HttpGet $next
     * @return void
     */
    public function setNext(P2HttpGet $next = null)
    {
        $this->_next = $next;
    }

    // }}}
    // {{{ hasNext()

    /**
     * ���̃��N�G�X�g�̗L�����`�F�b�N����
     *
     * @return bool
     */
    public function hasNext()
    {
        return !is_null($this->_next);
    }

    // }}}
    // {{{ fetch()

    /**
     * �ÓI�Ăяo���p���\�b�h
     *
     * @param string $url
     * @param string $destination
     * @param array $options
     * @param P2HttpCallback $onSuccess
     * @param P2HttpCallback $onFailure
     * @return array(P2HttpGet, HttpMessage)
     */
    static public function fetch($url,
                                 $destination,
                                 array $options = null,
                                 P2HttpCallback $onSuccess = null,
                                 P2HttpCallback $onFailure = null
                                 )
    {
        $req = new P2HttpGet($url, $destination, $options, $onSuccess, $onFailure);
        $res = $req->send();
        return array($req, $res);
    }

    // }}}
}

// }}}
// {{{ P2HttpRequestQueue

/**
 * HttpRequest�p�̃L���[
 */
class P2HttpRequestQueue implements Iterator, Countable
{
    // {{{ properties

    /**
     * HttpRequest�̔z��
     *
     * @var array
     */
    protected $_queue;

    /**
     * ���݂̗v�f
     *
     * @var HttpRequest
     */
    private $_current;

    /**
     * ���݂̃L�[
     *
     * @var int
     */
    private $_key;

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     *
     * @param HttpRequest ...
     */
    public function __construct()
    {
        $this->_queue = array();

        $argc = func_num_args();
        if ($argc > 0) {
            $argv = func_get_args();
            foreach ($argv as $req) {
                $this->push($req);
            }
        }
    }

    // }}}
    // {{{ push()

    /**
     * �L���[��HttpRequest��ǉ�����
     *
     * @param HttpRequest $req
     * @return void
     */
    public function push(HttpRequest $req)
    {
        $this->_queue[] = $req;
    }

    // }}}
    // {{{ pop()

    /**
     * �L���[����HttpRequest�����o��
     *
     * @return HttpRequest|null
     */
    public function pop()
    {
        return array_shift($this->_queue);
    }

    // }}}
    // {{{ count()

    /**
     * �L���[�ɓo�^����Ă���HttpRequest�̐����擾����
     * (Countable)
     *
     * @return int
     */
    public function count()
    {
        return count($this->_queue);
    }

    // }}}
    // {{{ current()

    /**
     * ���݂̗v�f���擾����
     * (Iterator)
     *
     * @return HttpRequest
     */
    public function current()
    {
        return $this->_current;
    }

    // }}}
    // {{{ key()

    /**
     * ���݂̃L�[���擾����
     * (Iterator)
     *
     * @return int
     */
    public function key()
    {
        return $this->_key;
    }

    // }}}
    // {{{ next()

    /**
     * �C�e���[�^��O���Ɉړ�����
     * (Iterator)
     *
     * @return void
     */
    public function next()
    {
        $this->_current = next($this->_queue);
        $this->_key = key($this->_queue);
    }

    // }}}
    // {{{ rewind()

    /**
     * �C�e���[�^�������߂�
     * (Iterator)
     *
     * @return void
     */
    public function rewind()
    {
        $this->_current = reset($this->_queue);
        $this->_key = key($this->_queue);
    }

    // }}}
    // {{{ valid()

    /**
     * ���݂̗v�f���L�����ǂ������`�F�b�N����
     * (Iterator)
     *
     * @return bool
     */
    public function valid()
    {
        return ($this->_current !== false);
    }

    // }}}
}

// }}}
// {{{ P2HttpRequestStack

/**
 * HttpRequest�p�̃X�^�b�N
 */
class P2HttpRequestStack extends P2HttpRequestQueue
{
    // {{{ push()

    /**
     * �X�^�b�N��HttpRequest��ǉ�����
     *
     * @param HttpRequest $req
     * @return void
     */
    public function push(HttpRequest $req)
    {
        array_unshift($this->_queue, $req);
    }

    // }}}
}

// }}}
// {{{ P2HttpRequestPool

/**
 * HttpRequestPool���g��������_�E�����[�h�N���X
 *
 * @static
 */
class P2HttpRequestPool
{
    // {{{ constants

    /**
     * ����Ɏ��s���郊�N�G�X�g���̏��
     */
    const MAX_REQUESTS = 10;

    /**
     * ����z�X�g�ɑ΂��ĕ���Ɏ��s���郊�N�G�X�g���̏��
     */
    const MAX_REQUESTS_PER_HOST = 2;

    // }}}
    // {{{ send()

    /**
     * �v�[���ɃA�^�b�`����Ă��郊�N�G�X�g�𑗐M����
     *
     * @param HttpRequestPool $pool
     * @param P2HttpRequestQueue $queue
     * @return void
     */
    static public function send(HttpRequestPool $pool, P2HttpRequestQueue $queue = null)
    {
        $err = '';

        try {
            // �L���[����v�[���ɒǉ�
            if ($queue && ($c = count($pool)) < self::MAX_REQUESTS) {
                while ($c < self::MAX_REQUESTS && ($req = $queue->pop())) {
                    $pool->attach($req);
                    $c++;
                }
            }

            // ���N�G�X�g�𑗐M
            while ($c = count($pool)) {
                $pool->send();

                // �I���������N�G�X�g�̏���
                foreach ($pool->getFinishedRequests() as $req) {
                    $pool->detach($req);
                    $c--;

                    if ($req instanceof P2HttpGet) {
                        if ($req->hasError()) {
                            $err .= sprintf('<li><em>%s</em>: %s</li>',
                                            htmlspecialchars($req->getUrl(), ENT_QUOTES),
                                            htmlspecialchars($req->getErrorInfo(), ENT_QUOTES)
                                            );
                        }

                        if ($req->hasNext()) {
                            $pool->attach($req->getNext());
                            $c++;
                        }
                    }
                }

                // �L���[����v�[���ɒǉ�
                if ($queue) {
                    while ($c < self::MAX_REQUESTS && ($req = $queue->pop())) {
                        $pool->attach($req);
                        $c++;
                    }
                }
            }
        } catch (HttpException $e) {
            $err .= sprintf('<li>%s (%d) %s</li>',
                            get_class($e),
                            $e->getCode(),
                            htmlspecialchars($e->getMessage(), ENT_QUOTES)
                            );
        }

        if ($err !== '') {
            $GLOBALS['_info_msg_ht'] .= "<ul class=\"errors\">{$err}</ul>\n";
        }

        if (P2HttpExt::DEBUG) {
            if ($ph = http_persistent_handles_count()) {
                $ph_dump = str_replace('  ', ' ', print_r($ph, true));
                $ph_dump = preg_replace('/[\\r\\n]+/', "\n", $ph_dump);
                $ph_dump = preg_replace('/(Array|Object)\\n *\(/', '$1(', $ph_dump);
                $GLOBALS['_info_msg_ht'] .= "<pre>Persistent Handles:\n";
                $GLOBALS['_info_msg_ht'] .= htmlspecialchars($ph_dump, ENT_QUOTES);
                $GLOBALS['_info_msg_ht'] .= "</pre>\n";
            }
        }
    }

    // }}}
    // {{{ fetchSubjectTxt()

    /**
     * subject.txt���ꊇ�_�E�����[�h&�ۑ�����
     *
     * @param array|string $subjects
     * @param bool $force
     * @return void
     */
    static public function fetchSubjectTxt($subjects, $force = false)
    {
        global $_conf;

        // {{{ �_�E�����[�h�Ώۂ�ݒ�

        // ���C�ɔ���.idx�`���̃t�@�C�����p�[�X
        if (is_string($subjects)) {
            $lines = FileCtl::file_read_lines($subjects, FILE_IGNORE_NEW_LINES);
            if (!$lines) {
                return;
            }

            $subjects = array();

            foreach ($lines as $l) {
                $la = explode('<>', $l);
                if (count($la) < 12) {
                    continue;
                }

                $host = $la[10];
                $bbs = $la[11];
                if ($host === '' || $bbs === '') {
                    continue;
                }

                $id = $host . '<>' . $bbs;
                if (isset($subjects[$id])) {
                    continue;
                }

                $subjects[$id] = array($host, $bbs);
            }

        // [host, bbs] �̘A�z�z�������
        } elseif (is_array($subjects)) {
            $originals = $subjects;
            $subjects = array();

            foreach ($originals as $s) {
                if (!is_array($s) || !isset($s['host']) || !isset($s['bbs'])) {
                    continue;
                }

                $id = $s['host'] . '<>' . $s['bbs'];
                if (isset($subjects[$id])) {
                    continue;
                }

                $subjects[$id] = array($s['host'], $s['bbs']);
            }

        // ��L�ȊO
        } else {
            return;
        }

        if (!count($subjects)) {
            return;
        }

        // }}}
        // {{{ �L���[���Z�b�g�A�b�v

        // �L���[����т��̑��̕ϐ���������
        $queue = new P2HttpRequestQueue;
        $hosts = array();
        $time = time() - $_conf['sb_dl_interval'];
        $eucjp2sjis = null;

        // �esubject.txt�ւ̃��N�G�X�g���L���[�ɒǉ�
        foreach ($subjects as $subject) {
            list($host, $bbs) = $subject;

            $file = P2Util::datDirOfHostBbs($host, $bbs) . 'subject.txt';
            if (!$force && file_exists($file) && filemtime($file) > $time) {
                continue;
            }

            $url = 'http://' . $host . '/' . $bbs . '/subject.txt';

            if (P2Util::isHostJbbsShitaraba($host) || P2Util::isHostBe2chNet($host)) {
                if ($eucjp2sjis === null) {
                    $eucjp2sjis = new P2HttpCallback_SaveEucjpAsSjis;
                }
                $req = new P2HttpGet($url, $file, null, $eucjp2sjis);
            } else {
                $req = new P2HttpGet($url, $file);
            }

            // ����z�X�g�ɑ΂��Ă̓����ڑ��� MAX_REQUESTS_PER_HOST �܂�
            if (!isset($hosts[$host])) {
                $hosts[$host] = new P2HttpRequestQueue;
                $queue->push($req);
            } elseif (count($hosts[$host]) < self::MAX_REQUESTS_PER_HOST) {
                $queue->push($req);
            } else {
                $hosts[$host]->pop()->setNext($req);
            }
            $hosts[$host]->push($req);
        }

        // }}}

        // ���N�G�X�g�𑗐M
        if (count($queue)) {
            self::send(new HttpRequestPool, $queue);
            clearstatcache();
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
