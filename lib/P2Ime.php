<?php

// {{{ P2Ime

/**
 * rep2 - URL�Q�[�g�E�F�C�ϊ��N���X
 */
class P2Ime
{
    // {{{ properties

    /**
     * througth() ����Ăяo�����A���ۂ�URL�ϊ����\�b�h��
     *
     * @var string
     */
    protected $_method;

    /**
     * �����]�����Ȃ���O�g���q�̃��X�g
     *
     * @var array
     */
    protected $_exceptions;

    /**
     * �����]�����Ȃ���O�g���q�̍ő�̒���
     *
     * @var int
     */
    protected $_maxExceptionLength;

    /**
     * http�v���g�R���̃����N�̓Q�[�g��ʂ��Ȃ�
     *
     * @var bool
     */
    protected $_ignoreHttp;

    /**
     * �Q�[�g��URL
     *
     * @var string
     */
    protected $_gateUrl;

    /**
     * �����]���̑҂����� (�b)
     * �����̏ꍇ�͎蓮�]��
     *
     * @var int
     */
    protected $_delay;

    // }}}
    // {{{ __construct()

    /**
     * �R���X�g���N�^
     *
     * @param string $type
     * @param array $exceptions
     * @param bool $ignoreHttp
     */
    public function __construct($type = null, array $exceptions = null, $ignoreHttp = null)
    {
        global $_conf;

        // {{{ �p�����[�^�̏�����

        // �Q�[�g�E�F�C�^�C�v
        if ($type === null) {
            $type = $_conf['through_ime'];
            // Cookie������ (URI�ɃZ�b�V����ID���܂�) �̂Ƃ��͋���
            if (!$type && !$_conf['use_cookies']) {
                $type = 'ex';
            }
        }

        // p�̂ݎ蓮�]��
        if ($type == 'p2pm') {
            $type = 'p2';
        } elseif ($type == 'expm') {
            $type = 'ex';
        }

        // �����]�����Ȃ��g���q
        if ($exceptions === null) {
            if ($_conf['ime_manual_ext']) {
                $this->_exceptions = explode(',', strtolower(trim($_conf['ime_manual_ext'])));
            } else {
                $this->_exceptions = array();
            }
        } else {
            $this->_exceptions = array_map('strtolower', $exceptions);
        }
        if ($this->_exceptions) {
            $this->_maxExceptionLength = max(array_map('strlen', $this->_exceptions));
        } else {
            $this->_maxExceptionLength = 0;
        }

        // http�̃����N�͒ʂ��Ȃ�
        if ($ignoreHttp === null) {
            // $_conf['through_ime_http_only'] �� 1 �ŁA
            // �Z�L���A�Ȑڑ��ŁACookie���L�� (URI�ɃZ�b�V����ID���܂܂Ȃ�) �̂Ƃ��A
            // http�v���g�R���̃����N�̓Q�[�g��ʂ��Ȃ��B
            if ($_conf['through_ime_http_only'] && P2_HTTPS_CONNECTION && $_conf['use_cookies']) {
                $this->_ignoreHttp = true;
            } else {
                $this->_ignoreHttp = false;
            }
        } else {
            $this->_ignoreHttp = (bool)$ignoreHttp;
        }

        // �����]���̑҂����Ԃ̊���l
        $this->_delay = -1;

        // }}}
        // {{{ �Q�[�g�E�F�C����

        switch ($type) {
        // {{{ p2ime
        case 'p2':   // �����]��
        case 'p2m':  // �蓮�]��
            $this->_method = '_throughP2Ime';
            if ($type == 'p2m') {
                $this->_delay = -1;
            } else {
                $this->_delay = 0;
            }
            $this->_gateUrl = $_conf['p2ime_url'];
            break;
        // }}}
        // {{{ gate.php
        case 'ex':   // �����]��1�b
        case 'exq':  // �����]��0�b
        case 'exm':  // �蓮�]��
            $this->_method = '_throughGatePhp';
            if ($type == 'exm') {
                $this->_delay = -1;
            } elseif ($type == 'exq') {
                $this->_delay = 0;
            } else {
                $this->_delay = 1;
            }
            $this->_gateUrl = $_conf['expack.gate_php'];
            break;
        // }}}
        // {{{ Google
        case 'google':
            $this->_method = '_throughGoogleGateway';
            if ($_conf['ktai'] && !$_conf['iphone']) {
                $this->_gateUrl = 'http://www.google.co.jp/gwt/x?u=';
            } else {
                $this->_gateUrl = 'http://www.google.co.jp/url?q=';
            }
            break;
        // }}}
        default:
            $this->_method = '_passThrough';
            $this->_gateUrl = null;
        }

        // }}}
    }

    // }}}
    // {{{ through()

    /**
     * URL��ϊ�����
     *
     * @param string $url
     * @param int $delay
     * @param bool $escape
     * @return string
     */
    public function through($url, $delay = null, $escape = true)
    {
        if ($delay === null) {
            if ($this->_isExceptionUrl($url)) {
                $delay = -1;
            } else {
                $delay = $this->_delay;
            }
        }

        if (!($this->_ignoreHttp && preg_match('!^http://!', $url))) {
            $url = $this->{$this->_method}($url, $delay);
        }
        if ($escape) {
            return htmlspecialchars($url, ENT_QUOTES, 'Shift_JIS', false);
        } else {
            return $url;
        }
    }

    // }}}
    // {{{ _throughP2Ime()

    /**
     * p2ime��ʂ��悤��URL��ϊ�����
     *
     * p2ime�́Aenc, m, url �̈����������Œ肳��Ă���̂Œ���
     *
     * @param string $url
     * @param int $delay
     * @return string
     */
    protected function _throughP2Ime($url, $delay)
    {
        if ($delay < 0) {
            return $this->_gateUrl . '?enc=1&url=' . rawurlencode($url);
        } else {
            return $this->_gateUrl . '?enc=1&m=1&url=' . rawurlencode($url);
        }
    }

    // }}}
    // {{{ _throughGatePhp()

    /**
     * gate.php��ʂ��悤��URL��ϊ�����
     *
     * @param string $url
     * @param int $delay
     * @return string
     */
    protected function _throughGatePhp($url, $delay)
    {
        return sprintf('%s?u=%s&d=%d', $this->_gateUrl, rawurlencode($url), $delay);
    }

    // }}}
    // {{{ _throughGoogleGateway()

    /**
     * Google��URL�Q�[�g�E�F�C��ʂ��悤��URL��ϊ�����
     *
     * @param string $url
     * @param int $delay (unused)
     * @return string
     */
    protected function _throughGoogleGateway($url, $delay)
    {
        return $this->_gateUrl . rawurlencode($url);
    }

    // }}}
    // {{{ _passThrough()

    /**
     * URL�����̂܂ܕԂ�
     *
     * @param string $url
     * @param int $delay (unused)
     * @return string
     */
    protected function _passThrough($url, $delay)
    {
        return $url;
    }

    // }}}
    // {{{ _isExceptionUrl()

    /**
     * �����]���̗�OURL����
     *
     * @param string $url
     * @return bool
     */
    protected function _isExceptionUrl($url)
    {
        if ($this->_exceptions) {
            if (false !== ($pos = strrpos($url, '.'))) {
                $pos++;
                if (strlen($url) - $pos <= $this->_maxExceptionLength) {
                    $extension = strtolower(substr($url, $pos));
                    if (in_array($extension, $this->_exceptions)) {
                        return false;
                    }
                }
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
