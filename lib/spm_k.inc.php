<?php
/**
 * rep2expack - �g�т��� SPM �����̋@�\�𗘗p���邽�߂̊֐�
 */

// {{{ kspform()

/**
 * ���X�ԍ����w�肵�� �ړ��E�R�s�[(+���p)�EAAS ����t�H�[���𐶐�
 *
 * @return string
 */
function kspform($aThread, $default = '', $params = null)
{
    global $_conf;

    if ($_conf['iphone']) {
        $input_numeric_at = ' autocorrect="off" autocapitalize="off" placeholder="#"';
    } else {
        // ���͂�4���ȉ��̐����Ɍ��肷��
        //$input_numeric_at = ' maxlength="4" istyle="4" format="*N" mode="numeric"';
        $input_numeric_at = ' maxlength="4" istyle="4" format="4N" mode="numeric"';
    }

    // �I���\�ȃI�v�V����
    $options = array();
    $options['goto'] = 'GO';
    $options['copy'] = '��߰';
    $options['copy_quote'] = '&gt;��߰';
    $options['res']  = 'ڽ';
    $options['res_quote']  = '&gt;ڽ';
    if ($_conf['expack.aas.enabled']) {
        $options['aas']        = 'AAS';
        $options['aas_rotate'] = 'AAS*';
    }
    $options['aborn_res']  = '����:ڽ';
    $options['aborn_name'] = '����:���O';
    $options['aborn_mail'] = '����:Ұ�';
    $options['aborn_id']   = '����:ID';
    $options['aborn_msg']  = '����:ү����';
    $options['aborn_be']  = '����:BE';  // +Wiki
    $options['ng_name'] = 'NG:���O';
    $options['ng_mail'] = 'NG:Ұ�';
    $options['ng_id']   = 'NG:ID';
    $options['ng_msg']  = 'NG:ү����';
    $options['ng_be']  = 'NG:BE';   // +Wiki

    // �t�H�[������
    $form = "<form method=\"get\" action=\"spm_k.php\">";
    $form .= $_conf['k_input_ht'];

    // �B���p�����[�^
    $hidden = '<input type="hidden" name="%s" value="%s">';
    $form .= sprintf($hidden, 'host', htmlspecialchars($aThread->host, ENT_QUOTES));
    $form .= sprintf($hidden, 'bbs', htmlspecialchars($aThread->bbs, ENT_QUOTES));
    $form .= sprintf($hidden, 'key', htmlspecialchars($aThread->key, ENT_QUOTES));
    $form .= sprintf($hidden, 'rescount', $aThread->rescount);
    $form .= sprintf($hidden, 'offline', '1');
    $form .= sprintf($hidden, 'ttitle_en',htmlspecialchars(base64_encode($aThread->ttitle), ENT_QUOTES));

    // �ǉ��̉B���p�����[�^
    if (is_array($params)) {
        foreach ($params as $param_name => $param_value) {
            $form .= sprintf($hidden, $param_name, htmlspecialchars($param_value, ENT_QUOTES));
        }
    }

    // �I�v�V������I�����郁�j���[
    $form .= '<select name="ktool_name">';
    foreach ($options as $opt_name => $opt_title) {
        $form .= "<option value=\"{$opt_name}\">{$opt_title}</option>";
    }
    $form .= '</select>';

    // ���l���̓t�H�[���Ǝ��s�{�^��
    $form .= "<input type=\"text\" size=\"3\" name=\"ktool_value\" value=\"{$default}\"{$input_numeric_at}>";
    $form .= '<input type="submit" value="OK" title="OK">';

    $form .= '</form>';

    return $form;
}

// }}{
// {{{ kspDetectThread()

/**
 * �X���b�h���w�肷��
 */
function kspDetectThread()
{
    global $_conf, $host, $bbs, $key, $ls;

    list($nama_url, $host, $bbs, $key, $ls) = P2Util::detectThread();

    if (!($host && $bbs && $key)) {
        if ($nama_url) {
            $nama_url = htmlspecialchars($nama_url, ENT_QUOTES);
            p2die('�X���b�h�̎w�肪�ςł��B', "<a href=\"{$nama_url}\">{$nama_url}</a>", true);
        } else {
            p2die('�X���b�h�̎w�肪�ςł��B');
        }
    }
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
