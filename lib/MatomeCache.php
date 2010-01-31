<?php

// {{{ MatomeCache

/**
 * �܂Ƃߓǂ݃L���b�V���f�[�^�N���X
 */
class MatomeCache
{
    // {{{ properties

    /**
     * �܂Ƃߓǂ݂̓��e (HTML)
     *
     * @var string
     */
    private $_content;

    /**
     * �܂Ƃߓǂ݂̃��^�f�[�^
     *
     * @var array
     */
    private $_metaData;

    /**
     * �܂Ƃߓǂ݂����k�ۑ�����t�@�C����
     *
     * @var string
     */
    private $_tempFile;

    /**
     * �܂Ƃߓǂ݂����k�ۑ�����X�g���[��
     *
     * @var stream
     */
    private $_stream;

    /**
     * �܂Ƃߓǂ݃L���b�V�����c����
     *
     * @var int
     */
    private $_maxNumEntries;

    /**
     * �܂Ƃߓǂ݃L���b�V�����L�����ǂ���
     *
     * @var bool
     */
    private $_enabled;

    // }}}
    // {{{ __construct()

    /**
     * �R���X�g���N�^
     *
     * �v���p�e�B�����������A�ꎞ�t�@�C�����쐬����B
     *
     * @param string $title
     * @param int $maxNumEntries
     */
    public function __construct($title, $maxNumEntries = -1)
    {
        global $_conf;

        if ($maxNumEntries == 0) {
            $this->_enabled = false;
            return;
        }

        // �v���p�e�B�̏�����
        $this->_content = '';
        $this->_metaData = array(
            'time' => time(),
            'title' => $title,
            'threads' => array(),
            'size' => 0,
        );
        $this->_tempFile = null;
        $this->_stream = null;
        $this->_maxNumEntries = $maxNumEntries;
        $this->_enabled = true;

        // �ꎞ�t�@�C�����쐬����
        /*
         * PHP�� tmpnam() �֐����Ă΂��ƁAC���ꃌ�x���ł�
         *  PHP_FUNCTION(tempnam) -> php_open_temporary_fd() ->
         *  php_do_open_temporary_file() -> virtual_file_ex()
         * �Ƃ�������ňꎞ�t�@�C���p�f�B���N�g���̉������s����B
         * ���̍ہAvirtual_file_ex() �� use_realpath ������
         * CWD_REALPATH ���w�肳��Ă���̂� tempnam() �̌��ʂ�
         * realpath() �ɂ�����K�v���Ȃ��B
        */
        $tempFile = tempnam($_conf['tmp_dir'], 'matome_');
        if (!$tempFile) {
            return;
        }

        // �ꎞ�t�@�C�����J���A�X�g���[���t�B���^��t������
        $fp = fopen($tempFile, 'wb');
        if ($fp) {
            stream_filter_append($fp, 'zlib.deflate');
            stream_filter_append($fp, 'convert.base64-encode');
            $this->_tempFile = $tempFile;
            $this->_stream = $fp;
        } else {
            unlink($tempfile);
        }
    }

    // }}}
    // {{{ __destruct()

    /**
     * �f�X�g���N�^
     *
     * ���e��ۑ����A�Â��L���b�V�����폜����B
     * �X���b�h��񂪋�̏ꍇ�͕ۑ����Ȃ��B
     *
     * @param void
     */
    public function __destruct()
    {
        if (!$this->_enabled) {
            return;
        }

        // �X�g���[�������
        if ($this->_stream) {
            fclose($this->_stream);
        }

        // ���X������Ȃ�
        if (count($this->_metaData['threads'])) {
            // ���e��ۑ�����
            if ($this->_tempFile) {
                MatomeCacheList::add($this->_tempFile, $this->_metaData, true);
            } else {
                MatomeCacheList::add($this->_content, $this->_metaData, false);
            }
            // �Â��L���b�V�����폜����B
            if ($this->_maxNumEntries > 0) {
                MatomeCacheList::trim($this->_maxNumEntries);
            }
        }

        // �ꎞ�t�@�C�����폜����
        if ($this->_tempFile) {
            unlink($this->_tempFile);
        }
    }

    // }}}
    // {{{ concat()

    /**
     * ���e��ǉ�����
     *
     * @param string $content
     * @return void
     */
    public function concat($content)
    {
        if ($this->_enabled) {
            if ($this->_stream) {
                fwrite($this->_stream, $content);
            } else {
                $this->_content .= $content;
            }
            $this->_metaData['size'] += strlen($content);
        }
    }

    // }}}
    // {{{ addReadThread()

    /**
     * �܂Ƃߓǂ݂Ɋ܂܂��X���b�h����ǉ�����
     *
     * @param ThreadRead $aThread
     * @return void
     */
    public function addReadThread(ThreadRead $aThread)
    {
        if ($this->_enabled) {
            $this->_metaData['threads'][] = array(
                'title' => $aThread->ttitle_hd,
                'host'=> $aThread->host,
                'bbs'=> $aThread->bbs,
                'key'=> $aThread->key,
                'ls' => sprintf('%d-%dn',
                                $aThread->resrange['start'],
                                $aThread->resrange['to']),
            );
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
