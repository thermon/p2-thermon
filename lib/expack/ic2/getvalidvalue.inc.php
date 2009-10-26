<?php
/**
 * ImageCache2 - ���N�G�X�g�ϐ��̋������[�e�B���e�B�֐�
 */

// {{{ getValidValue()

/**
 * Submit���ꂽ�l���Ó��Ȃ�i�t�B���^��K�p���āj�Ԃ��A�����łȂ���΃f�t�H���g�l��K�p����
 *
 * ���W�I�{�^����`�F�b�N�{�b�N�X�AHTML_QuickForm�̃O���[�v�ɂ͔�Ή�
 * HTML_QuickForm���p�������N���X�̃��\�b�h�Ƃ��Ď������ׂ�
 */
function getValidValue($key, $default, $filter = '')
{
    global $qf, $qfe;
    $value = $qf->getSubmitValue($key);
    if (is_null($value) || $qf->getElementError($key)) {
        if ($qfe[$key]->getType() == 'select') {
            $qfe[$key]->setSelected($default);
        } else {
            $qfe[$key]->setValue($default);
        }
        return $default;
    }
    return (strlen($filter) > 0) ? $filter($value) : $value;
}

// }}}
// {{{ intoRange()

/**
 * ���l���w�肳�ꂽ�͈͂ɖ�����������߂�֐�
 */
function intoRange($int)
{
    $a = func_get_args();
    $r = array_map('intval', $a);
    $g = func_num_args();
    $int = $r[0];
    switch ($g) {
        case 1: return $int;
        case 2: $min = 0; $max = $r[1]; break;
        default: $min = $r[1]; $max = $r[2];
    }
    if ($min > $max) {
        list($min, $max) = array($max, $min);
    }
    return max($min, min($max, intval($int)));
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
