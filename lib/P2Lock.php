<?php
/**
 * rep2expack - flock() �x�[�X�̔ėp���b�N
 */

// {{{ P2Lock

/**
 * �ȈՃ��b�N�N���X
 */
class P2Lock
{
    // {{{ properties

    /**
     * ���b�N�t�@�C���̃p�X
     *
     * @var string
     */
    protected $_filename;

    /**
     * ���b�N�t�@�C���̃n���h��
     *
     * @var resource
     */
    protected $_fh;

    /**
     * ���b�N�t�@�C���������ō폜���邩�ǂ���
     *
     * @var bool
     */
    protected $_remove;

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     *
     * @param  string $name     ���b�N���i���r�������������t�@�C�����j
     * @param  bool   $remove   ���b�N�t�@�C���������ō폜���邩�ǂ���
     * @param  string $suffix   ���b�N�t�@�C�����̐ڔ���
     */
    public function __construct($name, $remove = true, $suffix = '.lck')
    {
        $this->_filename = p2_realpath($name . $suffix);
        $this->_remove = $remove;

        FileCtl::mkdirFor($this->_filename);

        $this->_fh = fopen($this->_filename, 'wb');
        if (!$this->_fh) {
            p2die("cannot create lockfile ({$this->_filename}).");
        }
        if (!flock($this->_fh, LOCK_EX)) {
            p2die("cannot get lock ({$this->_filename}).");
        }
    }

    // }}}
    // {{{ destructor

    /**
     * �f�X�g���N�^
     */
    public function __destruct()
    {
        if (is_resource($this->_fh)) {
            flock($this->_fh, LOCK_UN);
            fclose($this->_fh);
            $this->_fh = null;
        }

        if ($this->_remove && file_exists($this->_filename)) {
            unlink($this->_filename);
        }
    }

    // }}}
    // {{{ free()

    /**
     * �����I�Ƀ��b�N���J������
     */
    public function free()
    {
        $this->__destruct();
    }

    // }}}
    // {{{ remove()

    /**
     * �����I�Ƀ��b�N���J�����A���b�N�t�@�C���������폜����
     *
     * unlink()��stat()�̃L���b�V���������I�ɃN���A����̂�
     * clearstatcache()����K�v�͂Ȃ�
     */
    public function remove()
    {
        $this->__destruct();
        if (file_exists($this->_filename)) {
            unlink($this->_filename);
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
