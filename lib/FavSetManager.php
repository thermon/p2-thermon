<?php
/**
 * rep2expack -���C�ɃZ�b�g�̐ݒ�E�؂�ւ����[�e�B���e�B
 */

// {{{ FavSetManager

/**
 * ���C�ɃZ�b�g�̐ݒ�E�؂�ւ����[�e�B���e�B�N���X
 *
 * @static
 */
class FavSetManager
{
    // {{{ loadAllFavSet()

    /**
     * ���ׂĂ̂��C�ɃX���E���C�ɔ�ǂݍ���
     *
     * @param bool $force
     * @return void
     */
    static public function loadAllFavSet($force = false)
    {
        global $_conf;
        static $done = false;

        if ($done && !$force) {
            return;
        }

        // �L���b�V���̗L�����`�F�b�N
        $cache_file = $_conf['cache_dir'] . DIRECTORY_SEPARATOR . 'favset_cache.txt';
        if ($force || !file_exists($cache_file)) {
            $use_cache = false;
        } else {
            $cache_mtime = filemtime($cache_file);
            if (filemtime(__FILE__) > $cache_mtime) {
                $use_cache = false;
            } else {
                $use_cache = true;
            }
        }

        // �L���b�V�����L�����ǂ������`�F�b�N
        if ($use_cache && file_exists($_conf['orig_favlist_idx']) && filemtime($_conf['orig_favlist_idx']) > $cache_mtime) {
            $use_cache = false;
        }
        if ($use_cache && file_exists($_conf['orig_favita_brd']) && filemtime($_conf['orig_favita_brd']) > $cache_mtime) {
            $use_cache = false;
        }

        // �ǂݍ��ݑΏۃt�@�C���̃��X�g���쐬����Ɠ����ɃL���b�V�����L�����ǂ������`�F�b�N
        $favlist_idxes = array($_conf['orig_favlist_idx']);
        $favita_brds = array($_conf['orig_favita_brd']);
        for ($i = 1; $i <= $_conf['expack.misc.favset_num']; $i++) {
            $favlist_idx = $_conf['pref_dir'] . DIRECTORY_SEPARATOR . sprintf('p2_favlist%d.idx', $i);
            if ($use_cache && file_exists($favlist_idx) && filemtime($favlist_idx) > $cache_mtime) {
                $use_cache = false;
            }
            $favlist_idxes[$i] = $favlist_idx;

            $favita_brd = $_conf['pref_dir'] . DIRECTORY_SEPARATOR . sprintf('p2_favita%d.brd', $i);
            if ($use_cache && file_exists($favita_brd) && filemtime($favita_brd) > $cache_mtime) {
                $use_cache = false;
            }
            $favita_brds[$i] = $favita_brd;
        }

        // �L���b�V�����L���Ȃ�A������g��
        if ($use_cache) {
            $cache = unserialize(file_get_contents($cache_file));
            if (is_array($cache)) {
                list($_conf['favlists'], $_conf['favitas']) = $cache;
                $done = true;
                return;
            }
        }

        // ���ׂĂ̂��C�ɃX����ǂݍ���
        $_conf['favlists'] = array();
        foreach ($favlist_idxes as $i => $favlist_idx) {
            $_conf['favlists'][$i] = array();
            if ($favlines = FileCtl::file_read_lines($favlist_idx, FILE_IGNORE_NEW_LINES)) {
                foreach ($favlines as $l) {
                    $lar = explode('<>', $l);
                    // bbs�̂Ȃ����͕̂s���f�[�^�Ȃ̂ŃX�L�b�v
                    if (!isset($lar[11])) {
                        continue;
                    }
                    $key   = $lar[1];
                    $host  = $lar[10];
                    $bbs   = $lar[11];
                    $group = P2Util::getHostGroupName($host);
                    $_conf['favlists'][$i][] = array('group' => $group, 'host' => $host, 'bbs' => $bbs, 'key' => $key);
                }
            }
        }

        // ���ׂĂ̂��C�ɔ�ǂݍ���
        $_conf['favitas'] = array();
        foreach ($favita_brds as $i => $favita_brd) {
            $_conf['favitas'][$i] = array();
            if ($favlines = FileCtl::file_read_lines($favita_brd, FILE_IGNORE_NEW_LINES)) {
                foreach ($favlines as $l) {
                    $lar = explode("\t", $l);
                    $host  = $lar[1];
                    $bbs   = $lar[2];
                    $itaj  = $lar[3];
                    $group = P2Util::getHostGroupName($host);
                    $_conf['favitas'][$i][] = array('group' => $group, 'host' => $host, 'bbs' => $bbs, 'itaj' => $itaj);
                }
            }
        }

        //�L���b�V���ɕۑ�����
        if (!is_dir($_conf['pref_dir'])) {
            FileCtl::mkdir_for($cache_file);
        }
        file_put_contents($cache_file, serialize(array($_conf['favlists'], $_conf['favitas'])));

        $done = true;
    }

    // }}}
    // {{{ switchFavSet()

    /**
     * ���C�ɃX���A���C�ɔARSS�̃J�����g�Z�b�g��؂�ւ���
     *
     * @param bool $force
     * @return void
     */
    static public function switchFavSet($force = false)
    {
        global $_conf;
        static $done = false;

        if ($done && !$force) {
            return;
        }

        $sets = array(
            // ���C�ɃX���Z�b�g
            'm_favlist_set' => array('favlist_idx', 'p2_favlist%d.idx'),
            // ���C�ɔZ�b�g
            'm_favita_set' => array('favita_brd', 'p2_favita%d.brd'),
            // RSS�Z�b�g
            'm_rss_set' => array('expack.rss.setting_path', 'p2_rss%d.txt'),
        );

        $ar = array();

        foreach ($sets as $key => $value) {
            if (isset($_REQUEST[$key]) && 0 <= $_REQUEST[$key] && $_REQUEST[$key] <= $_conf['expack.misc.favset_num']) {
                $_SESSION[$key] = (int)$_REQUEST[$key];
            }

            $_conf[$key] = $id = (isset($_SESSION[$key])) ? $_SESSION[$key] : 0;
            $_conf["{$key}_at_a"] = "&amp;{$key}={$id}";
            $_conf["{$key}_input_ht"] = "<input type=\"hidden\" name=\"{$_conf[$key]}\" value=\"{$id}\">";

            $ar[] = $key . '=' . $id;

            if (!empty($_SESSION[$key])) {
                list($cnf, $fmt) = $value;
                $_conf[$cnf] = $_conf['pref_dir'] . DIRECTORY_SEPARATOR . sprintf($fmt, $_SESSION[$key]);
            }
        }

        if ($_conf['ktai'] && !$_conf['iphone']) {
            $k_to_index_q = implode('&', $ar);
            if ($_conf['view_forced_by_query']) {
                $k_to_index_q .= '&b=k';
            }
            $k_to_index_q = htmlspecialchars($k_to_index_q, ENT_QUOTES);
            $_conf['k_to_index_ht'] = "<a href=\"index.php?{$k_to_index_q}\"{$_conf['k_accesskey_at'][0]}>{$_conf['k_accesskey_st'][0]}TOP</a>";
        }

        $done = true;
    }

    // }}}
    // {{{ getFavSetTitles()

    /**
     * ���C�ɃX���A���C�ɔARSS�̃Z�b�g���X�g�i�^�C�g���ꗗ�j��ǂݍ���
     *
     * @param string $set_name
     * @return array
     */
    static public function getFavSetTitles($set_name = null)
    {
        global $_conf;

        if (!file_exists($_conf['expack.misc.favset_file'])) {
            return false;
        }

        $favset_titles = @unserialize(file_get_contents($_conf['expack.misc.favset_file']));

        if ($set_name === null) {
            return $favset_titles;
        }

        if (is_array($favset_titles) && isset($favset_titles[$set_name]) && is_array($favset_titles[$set_name])) {
            return $favset_titles[$set_name];
        }

        return false;
    }

    // }}}
    // {{{ getFavSetPageTitleHt()

    /**
     * �Z�b�g���X�g����y�[�W�^�C�g�����擾����
     *
     * @param string $set_name
     * @param string $default_title
     * @return string
     */
    static public function getFavSetPageTitleHt($set_name, $default_title)
    {
        global $_conf;

        $i = (isset($_SESSION[$set_name])) ? (int)$_SESSION[$set_name] : 0;
        $favlist_titles = FavSetManager::getFavSetTitles($set_name);

        if (!$favlist_titles || !isset($favlist_titles[$i]) || strlen($favlist_titles[$i]) == 0) {
            if ($i == 0) {
                $title = $default_title;
            } else {
                $title = $default_title . $i;
            }
            $title = htmlspecialchars($title, ENT_QUOTES);
        } else {
            $title = $favlist_titles[$i];
        }
        // �S�p�p���X�y�[�X�J�i�𔼊p��
        if ($_conf['ktai'] && $_conf['mobile.save_packet']) {
            $title = mb_convert_kana($title, 'rnsk');
        }
        return $title;
    }

    // }}}
    // {{{ makeFavSetSwitchForm()

    /**
     * ���C�ɃX���A���C�ɔARSS�̃Z�b�g���X�g��؂�ւ���t�H�[���𐶐�����
     *
     * @param string $set_name
     * @param string $set_title
     * @param string $script
     * @param bool $inline
     * @param array $hidden_values
     * @return string
     */
    static public function makeFavSetSwitchForm($set_name,
                                                $set_title,
                                                $script = null,
                                                $target = null,
                                                $inline = false,
                                                array $hidden_values = null
                                                )
    {
        global $_conf;

        // �ϐ�������
        if (!$script) {
            $script = $_SERVER['SCRIPT_NAME'];
        }
        if (!$target) {
            $target = '_self';
        }
        $style = ($inline) ? ' style="display:inline;"' : '';

        // �t�H�[���쐬
        $form_ht = "<form method=\"get\" action=\"{$script}\" target=\"{$target}\"{$style}>";
        $form_ht .= $_conf['k_input_ht'];
        if (is_array($hidden_values)) {
            foreach ($hidden_values as $key => $value) {
                $value = htmlspecialchars($value, ENT_QUOTES);
                $form_ht .= "<input type=\"hidden\" name=\"{$key}\" value=\"{$value}\">";
            }
        }
        $form_ht .= FavSetManager::makeFavSetSwitchElem($set_name, $set_title, true);
        $submit_value = ($_conf['ktai']) ? '��Đؑ�' : '�Z�b�g�ؑ�';
        $form_ht .= "<input type=\"submit\" value=\"{$submit_value}\"></form>\n";

        return $form_ht;
    }

    // }}}
    // {{{ makeFavSetSwitchElem()

    /**
     * ���C�ɃX���A���C�ɔARSS�̃Z�b�g���X�g��؂�ւ���select�v�f�𐶐�����
     *
     * @param string $set_name
     * @param string $set_title
     * @param bool $set_selected
     * @param string $onchange
     * @return string
     */
    static public function makeFavSetSwitchElem($set_name,
                                                $set_title,
                                                $set_selected = false,
                                                $onchange = null
                                                )
    {
        global $_conf;

        // �ϐ�������
        $i = (isset($_SESSION[$set_name])) ? (int)$_SESSION[$set_name] : 0;
        if ($onchange) {
            $onchange_ht = " onchange=\"{$onchange}\"";
        } else {
            $onchange_ht = '';
        }

        // ���[�U�ݒ�^�C�g����ǂݍ���
        if (!($titles = FavSetManager::getFavSetTitles($set_name))) {
            $titles = array();
        }

        // SELECT�v�f�쐬
        $select_ht  = "<select name=\"{$set_name}\"{$onchange_ht}>";
        if (!$set_selected) {
            $select_ht .= "<option value=\"{$i}\" selected>[{$set_title}]</option>";
        }
        for ($j = 0; $j <= $_conf['expack.misc.favset_num']; $j++) {
            if (!isset($titles[$j]) || strlen($titles[$j]) == 0) {
                $titles[$j] = ($j == 0) ? $set_title : $set_title . $j;
            }
            // �S�p�p���X�y�[�X�J�i�𔼊p��
            if ($_conf['ktai'] && $_conf['mobile.save_packet']) {
                $titles[$j] = mb_convert_kana($titles[$j], 'rnsk');
            }
            $selected = ($set_selected && $i == $j) ? ' selected' : '';
            $select_ht .= "<option value=\"{$j}\"{$selected}>{$titles[$j]}</option>";
        }
        $select_ht .= "</select>\n";

        return $select_ht;
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
