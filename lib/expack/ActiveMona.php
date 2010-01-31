<?php
// {{{ ActiveMona

/**
 *  rep2expack - �A�N�e�B�u���i�[
 */
class ActiveMona
{
    // {{{ constants

    /**
     * AA�p�t�H���g�ؑփX�C�b�`�̃t�H�[�}�b�g
     */
    const MONA = '<img src="img/aa.png" width="19" height="12" alt="" class="aMonaSW" onclick="activeMona(\'%s\')">';
    //const MONA = '<img src="img/mona.png" width="39" height="12" alt="�i�L�́M�j class="aMonaSW" onclick="activeMona(\'%s\')"">';

    /**
     * AA����p�^�[��
     *
     * �r��
     * [\\u2500-\\u257F] [\\x{849F}-\\x{84BE}]
     *
     * �����
     *
     * Latin-1,�S�p�X�y�[�X�Ƌ�Ǔ_,�Ђ炪��,�J�^�J�i,
     * ���p�E�S�p�` �ȊO�̓���������3�A������p�^�[��
     *
     * Unicode ��
     * [^\x00-\x7F\x{2010}-\x{203B}\x{3000}-\x{3002}\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{FF00}-\x{FFEF}]
     * ���x�[�X�� SJIS �ɍ�蒼���Ă��邪�A�኱�̈Ⴂ������B
     */
    const REGEX = '(?:[��-��]{5}|([^\\x00-\\x7F\\xA1-\\xDF�@�A�B�C�D�F�G�O-���[�`�E�c���I�H�����������{�^��])\\1\\1)';

    // }}}
    // {{{ properties

    /**
     * �C���X�^���X
     *
     * @var array(ActiveMona)
     */
    static private $_am = array();

    /**
     * �s������Ɏg�����s����
     *
     * @var string
     */
    private $_lb;

    /**
     * ���K�\���Ŕ��肷��s���̉���-1
     *
     * @var int
     */
    private $_ln;

    // }}}
    // {{{ singleton()

    /**
     * �V���O���g��
     *
     * @param string $linebreaks
     * @return ActiveMona
     */
    static public function singleton($linebreaks = '<br>')
    {
        if (!array_key_exists($linebreaks, self::$_am)) {
            self::$_am[$linebreaks] = new ActiveMona($linebreaks);
        }
        return self::$_am[$linebreaks];
    }

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     *
     * @param string $linebreaks
     */
    public function __construct($linebreaks = '<br>')
    {
        global $_conf;

        $this->_lb = $linebreaks;
        $this->_ln = $_conf['expack.am.lines_limit'] - 1;
    }

    // }}}
    // {{{ getMona()

    /**
     * AA�p�t�H���g�ؑփX�C�b�`�𐶐�
     *
     * @param string $id
     * @return string
     */
    function getMona($id)
    {
        return sprintf(self::MONA, $id);
    }

    // }}}
    // {{{ detectAA()

    /**
     * AA����
     *
     * @param string $msg
     * @return bool
     */
    function detectAA($msg)
    {
        if (substr_count($msg, $this->_lb) < $this->_ln) {
            return false;
        } elseif (substr_count($msg, '�@�@') > 5) {
            return true;
        } elseif (!mb_ereg_search_init($msg, self::REGEX)) {
            return false;
        } else {
            $i = 0;
            while ($i < 3 && mb_ereg_search()) {
                $i++;
            }
            return ($i == 3);
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
