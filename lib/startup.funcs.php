<?php
/**
 * rep2expack - �Z�b�g�A�b�v�p�֐��Q
 */

// {{{ p2_check_environment()

/**
 * ��������m�F����
 *
 * @return bool
 */
function p2_check_environment($check_recommended)
{
    global $_info_msg_ht;

    include P2_CONF_DIR . '/setup_info.php';

    $php_version = phpversion();

    if (version_compare($php_version, '5.3.0-dev', '>=')) {
        $required_version = $p2_required_version_5_3;
        $recommended_version = $p2_recommended_version_5_3;
    } else {
        $required_version = $p2_required_version_5_2;
        $recommended_version = $p2_recommended_version_5_2;
    }

    // PHP�̃o�[�W����
    if (version_compare($php_version, $required_version, '<')) {
        p2die("PHP {$required_version} �����ł͎g���܂���B");
    }

    // �K�{�g�����W���[��
    foreach ($p2_required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            p2die("{$ext} �g�����W���[�������[�h����Ă��܂���B");
        }
    }

    // �L�����Ɠ��삵�Ȃ�php.ini�f�B���N�e�B�u
    foreach ($p2_incompatible_ini_directives as $directive) {
        if (ini_get($directive)) {
            p2die("{$directive} �� On �ł��B",
                  "php.ini �� {$directive} �� Off �ɂ��Ă��������B");
        }
    }

    // eAccelerator
    if (extension_loaded('eaccelerator') &&
        version_compare(EACCELERATOR_VERSION, '0.9.5.2', '<'))
    {
        $err = 'eAccelerator���X�V���Ă��������B';
        $ev = EACCELERATOR_VERSION;
        $msg = <<<EOP
<p>PHP 5.2�ŗ�O��ߑ��ł��Ȃ����̂���eAccelerator ({$ev})���C���X�g�[������Ă��܂��B<br>
eAccelerator�𖳌��ɂ��邩�A���̖�肪�C�����ꂽeAccelerator 0.9.5.2�ȍ~���g�p���Ă��������B<br>
<a href="http://eaccelerator.net/">http://eaccelerator.net/</a></p>
EOP;
        p2die($err, $msg, true);
    }

    // �����o�[�W����
    if ($check_recommended) {
        if (version_compare($php_version, $recommended_version, '<')) {
            // title.php �̂݃��b�Z�[�W��\��
            if (!is_numeric($check_recommended)) {
                $check_recommended = htmlspecialchars($check_recommended, ENT_QUOTES);
            }
            if (basename($_SERVER['PHP_SELF'], '.php') == 'title') {
                $_info_msg_ht .= <<<EOP
<p><strong>�����o�[�W�������Â�PHP�œ��삵�Ă��܂��B</strong>
<em>(PHP {$php_version})</em><br>
PHP {$recommended_version} �ȍ~�ɃA�b�v�f�[�g���邱�Ƃ��������߂��܂��B</p>
<p style="font-size:smaller">���̃��b�Z�[�W��\�����Ȃ��悤�ɂ���ɂ�
<em>{\$rep2_directory}</em>/conf/conf.inc.php �� {$check_recommended} �s�ځA<br>
<samp>p2_check_environment(<strong>__LINE__</strong>);</samp> ��
<samp>p2_check_environment(<strong>false</strong>);</samp> �ɏ��������Ă��������B</p>
EOP;
            }
            return false;
        }
    }

    return true;
}

// }}}
// {{{ p2_check_migration()

/**
 * �}�C�O���[�V�����̕K�v�����邩�ǂ������`�F�b�N
 *
 * @param   string  $config_version
 * @return  array
 */
function p2_check_migration($config_version)
{
    include P2_CONF_DIR . '/setup_info.php';

    $migrators = array();
    $found = false;

    foreach ($p2_changed_versions as $version) {
        if ($found || version_compare($config_version, $version, '<')) {
            $found = true;
            $migrator_name = str_replace('.', '_', $version);
            $migrator_func = 'p2_migrate_' . $migrator_name;
            $migrator_file = '/migrators/' . $migrator_name . '.php';
            $migrators[$migrator_func] = $migrator_file;
        }
    }

    if ($found) {
        return $migrators;
    } else {
        return null;
    }
}

// }}}
// {{{ p2_invoke_migrators()

/**
 * �}�C�O���[�V���������s
 *
 * @param array $migrators �}�C�O���[�V�����֐��̃��X�g
 * @param array $user_config �Â����[�U�[�ݒ�
 * @return array �V�������[�U�[�ݒ�
 */
function p2_invoke_migrators(array $migrators, array $user_config)
{
    global $_conf;

    foreach ($migrators as $migrator_func => $migrator_file) {
        include P2_LIB_DIR . $migrator_file;
        $user_config = $migrator_func($_conf, $user_config);
    }

    return $user_config;
}

// }}}
// {{{ p2_load_class()

/**
 * �N���X���[�_�[
 *
 * @string $name
 * @return void
 */
function p2_load_class($name)
{
    if (preg_match('/^(?:
            BbsMap |
            BrdCtl |
            BrdMenu(?:Cate|Ita)? |
            DataPhp |
            FavSetManager |
            FileCtl |
            HostCheck |
            Login |
            MD5Crypt |
            MatomeCache(List)? |
            NgAbornCtl |
            P2[A-Z][A-Za-z]* |
            PresetManager |
            Res(Article|Hist) |
            Session |
            SettingTxt |
            ShowBrdMenu(?:K|Pc) |
            ShowThread(?:K|Pc)? |
            StrCtl |
            StrSjis |
            SubjectTxt |
            Thread(?:List|Read)? |
            UA |
            UrlSafeBase64 |
            Wap(UserAgent|Request|Response)
        )$/x', $name))
    {
        if (strncmp($name, 'Wap', 3) === 0) {
            include P2_LIB_DIR . '/Wap.php';
        } else {
            include P2_LIB_DIR . '/' . $name . '.php';
        }
    } elseif (preg_match('/^[A-Z][A-Za-z]*DataStore$/', $name)) {
        include P2_LIB_DIR . '/P2DataStore/' . $name . '.php';
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
