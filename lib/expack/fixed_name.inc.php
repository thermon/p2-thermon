<?php
/**
 * rep2expack - ���O���̒�^�� = �R�e�n��
 */

require_once P2_LIB_DIR . '/SjisPersister.php';
require_once P2_LIB_DIR . '/P2Util.php';

// {{{ fixed_name_get_persister()

/**
 * ��^����ۑ�����X�g���[�W���擾����
 *
 * @param void
 * @return SjisPersister
 */
function fixed_name_get_persister()
{
    global $_conf;
    $filename = $_conf['pref_dir'] . DIRECTORY_SEPARATOR . 'fixed_name.db';
    return KeyValuePersister::getPersister($filename, 'SjisPersister');
}

// }}}
// {{{ fixed_name_get_select_element()

/**
 * ��^����I������select�v�f���擾����
 *
 * @param   string  $name       select�v�f��id�����l�E���Ename�����l (�f�t�H���g��'fixed_name')
 * @param   string  $onchange   option�v�f���I�����ꂽ�Ƃ��̃C�x���g�n���h�� (�f�t�H���g�͂Ȃ�)
 * @return  string  select�v�f��HTML
 */
function fixed_name_get_select_element($name = 'fixed_name', $onchange = null)
{
    $name_ht = htmlspecialchars($name, ENT_QUOTES, 'Shift_JIS');
    if ($onchange !== null) {
        $onchange_ht = htmlspecialchars($onchange, ENT_QUOTES, 'Shift_JIS');
        $select = "<select id=\"{$name_ht}\" name=\"{$name_ht}\" onchange=\"{$onchange_ht}\">\n";
    } else {
        $select = "<select id=\"{$name_ht}\" name=\"{$name_ht}\">\n";
    }
    $select .= "<option value=\"\">�R�e�n��</option>\n";
    foreach (fixed_name_get_persister()->getKeys() as $key) {
        $key_ht = htmlspecialchars($onchange, ENT_QUOTES, 'Shift_JIS');
        $select .= "<option value=\"{$key_ht}\">{$key_ht}</option>\n";
    }
    $select .= "</option>";
    return $select;
}

// }}}
// {{{ fixed_name_convert_trip()

/**
 * �R�e�n����'#'������g���b�v�ɕϊ�����
 *
 * @param   string  $name   ���̃R�e�n��
 * @return  string  �g���b�v�ϊ��ς݂̃R�e�n��
 */
function fixed_name_convert_trip($name)
{
    $pos = strpos($name, '#');
    if ($pos === false) {
        return $name;
    }
    return substr($name, 0, $pos) . '��' . P2Util::mkTrip(substr($name, $pos + 1));
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
