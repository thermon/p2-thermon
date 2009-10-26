<?php
/**
 * rep2expack - �{���̒�^��
 */

require_once P2_LIB_DIR . '/SjisPersister.php';

// {{{ fixed_message_get_persister()

/**
 * ��^����ۑ�����X�g���[�W���擾����
 *
 * @param void
 * @return SjisPersister
 */
function fixed_message_get_persister()
{
    global $_conf;
    $filename = $_conf['pref_dir'] . DIRECTORY_SEPARATOR . 'fixed_message.db';
    return KeyValuePersister::getPersister($filename, 'SjisPersister');
}

// }}}
// {{{ fixed_name_get_select_element()

/**
 * ��^����I������select�v�f���擾����
 *
 * @param   string  $name       select�v�f��id�����l�E���Ename�����l (�f�t�H���g��'fixed_message')
 * @param   string  $onchange   option�v�f���I�����ꂽ�Ƃ��̃C�x���g�n���h�� (�f�t�H���g�͂Ȃ�)
 * @return  string  select�v�f��HTML
 */
function fixed_name_get_select_element($name = 'fixed_message', $onchange = null)
{
    $name_ht = htmlspecialchars($name, ENT_QUOTES, 'Shift_JIS');
    if ($onchange !== null) {
        $onchange_ht = htmlspecialchars($onchange, ENT_QUOTES, 'Shift_JIS');
        $select = "<select id=\"{$name_ht}\" name=\"{$name_ht}\" onchange=\"{$onchange_ht}\">\n";
    } else {
        $select = "<select id=\"{$name_ht}\" name=\"{$name_ht}\">\n";
    }
    $select .= "<option value=\"\">��^��</option>\n";
    foreach (fixed_message_get_persister()->getKeys() as $key) {
        $key_ht = htmlspecialchars($onchange, ENT_QUOTES, 'Shift_JIS');
        $select .= "<option value=\"{$key_ht}\">{$key_ht}</option>\n";
    }
    $select .= "</option>";
    return $select;
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
