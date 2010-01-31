<?php

// {{{ BrdMenuCate

/**
* �{�[�h���j���[�J�e�S���[�N���X
*/
class BrdMenuCate
{
    // {{{ properties

    public $name;          // �J�e�S���[�̖��O
    public $menuitas;      // �N���XBrdMenuIta�̃I�u�W�F�N�g���i�[����z��
    public $num;           // �i�[���ꂽBrdMenuIta�I�u�W�F�N�g�̐�
    public $is_open;       // �J���(bool)
    public $ita_match_num; // �����Ƀq�b�g�����̐�

    // }}}
    // {{{ constructor

    /**
    * �R���X�g���N�^
    */
    public function __construct($name)
    {
        $this->num = 0;
        $this->menuitas = array();
        $this->ita_match_num = 0;

        $this->name = $name;
    }

    // }}}
    // {{{ addBrdMenuIta()

    /**
     * ��ǉ�����
     */
    public function addBrdMenuIta(BrdMenuIta $aBrdMenuIta)
    {
        $this->menuitas[] = $aBrdMenuIta;
        $this->num++;
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
