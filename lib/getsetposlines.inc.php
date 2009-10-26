<?php
/**
 * rep2 - �|�W�V�������l�����Ȃ���A���C���f�[�^��ǉ����āA���ʂ��擾����֐�
 */

// {{{ getSetPosLines()

/**
 * �|�W�V�������l�����Ȃ���A���C���f�[�^��ǉ����āA���ʂ��擾����
 *
 * @param array     $lines            ���炩�߂��ߏd���v�f���폜�������C���z��
 * @param string    $data             �V�K���C���f�[�^
 * @param integer   $before_line_num  �ړ��O�̍s�ԍ��i�擪��0�j
 * @param mixed     $set              0(����), 1(�ǉ�), top, up, down, bottom
 * @return array
 */
function getSetPosLines($lines, $data, $before_line_num, $set)
{
    if ($set == 1 or $set == 'top') {
        $after_line_num = 0; // �ړ���̍s�ԍ�

    } elseif ($set == 'up') {
        $after_line_num = $before_line_num - 1;
        if ($after_line_num < 0) {
            $after_line_num = 0;
        }

    } elseif ($set == 'down') {
        $after_line_num = $before_line_num + 1;
        if ($after_line_num >= sizeof($lines)) {
            $after_line_num = 'bottom';
        }

    } elseif ($set == 'bottom') {
        $after_line_num = 'bottom';

    } else {
        return $lines;
    }

    //================================================
    // �Z�b�g����
    //================================================
    $reclines = array();
    if (!empty($lines)) {
        $i = 0;
        foreach ($lines as $l) {
            if ($i === $after_line_num) {
                $reclines[] = $data;
            }
            $reclines[] = $l;
            $i++;
        }
        if ($after_line_num === 'bottom') {
            $reclines[] = $data;
        }
        //�u$after_line_num == "bottom"�v���ƌ듮�삷��B
    } else {
        $reclines[] = $data;
    }

    return $reclines;
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
