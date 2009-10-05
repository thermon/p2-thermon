<?php
/**
 * rep2expack - RSS���[�e�B���e�B�֐�
 */

require_once P2_LIB_DIR . '/FileCtl.php';
require_once 'PEAR.php';

// {{{ rss_get_save_path()

/**
 * RSS��URL���烍�[�J���ɕۑ�����t�@�C���̃p�X��ݒ肷��
 */
function rss_get_save_path($remotefile)
{
    global $_conf, $_info_msg_ht;
    static $done = array();

    $remotefile = preg_replace('|^feed://|', 'http://', $remotefile);

    if (isset($done[$remotefile])) {
        return $done[$remotefile];
    }

    $pURL = @parse_url($remotefile);
    if (!$pURL || !isset($pURL['scheme']) || $pURL['scheme'] != 'http' || !isset($pURL['host'])) {
        $errmsg = 'p2 error: �s����RSS��URL (' . htmlspecialchars($remotefile, ENT_QUOTES) . ')';
        $error = &PEAR::raiseError($errmsg);
        return ($done[$remotefile] = $error);
    }

    $localname = '';
    if (isset($pURL['path']) && $pURL['path'] !== '') {
        $localname .= preg_replace('/[^\w.]/', '_', substr($pURL['path'], 1));
    }
    if (isset($pURL['query']) && $pURL['query'] !== '') {
        $localname .= '_' . preg_replace('/[^\w.%]/', '_', $pURL['query']) . '.rdf';
    }

    // �g���q.cgi��.php���ŕۑ�����̂�h��
    if (!preg_match('/\.(rdf|rss|xml)$/i', $localname)) {
        // �Â��o�[�W�����Ŏ擾����RSS���폜
        if (file_exists($localname)) {
            @unlink($localname);
        }
        // �g���q.rdf��t��
        $localname .= '.rdf';
    }
    // dotFile�������̂�h��
    if (substr($localname, 0, 1) == '.') {
        $localname = '_' . $localname;
    }

    $localpath = $_conf['dat_dir'] . '/p2_rss/' . $pURL['host'] . '/' . $localname;

    return ($done[$remotefile] = $localpath);
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
