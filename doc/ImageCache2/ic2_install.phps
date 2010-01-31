<?php
/**
 * ImageCache2::Installer
 */

// {{{ p2��{�ݒ�ǂݍ��݁��F��

require_once './conf/conf.inc.php';

$_login->authorize();

if ($_conf['expack.ic2.enabled'] == 0) {
    p2die('ImageCache2�͖����ł��B', 'conf/conf_admin_ex.inc.php �̐ݒ��ς��Ă��������B');
}

// }}}
// {{{ ���C�u�����ǂݍ��݁�������

$ok = true;

// ���C�u�����ǂݍ���
require_once 'PEAR.php';
require_once 'DB.php';
require_once 'DB/DataObject.php';
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ObjectFlexy.php';
require_once 'HTML/Template/Flexy.php';
require_once 'HTML/Template/Flexy/Element.php';
require_once 'Validate.php';
require_once P2EX_LIB_DIR . '/ic2/bootstrap.php';

// �ݒ�t�@�C���ǂݍ���
$ini = ic2_loadconfig();

// DB_DataObject�̐ݒ�
$options = &PEAR::getStaticProperty('DB_DataObject','options');
$options = array('database' => $ini['General']['dsn'], 'quote_identifiers' => true);

// �ݒ�֘A�̃G���[�͂����̃N���X�̃R���X�g���N�^�Ń`�F�b�N�����
$thumbnailer = new IC2_Thumbnailer;
$icdb = new IC2_DataObject_Images;
$db = $icdb->getDatabaseConnection();

// }}}
// {{{ SQL����

// �A�ԂŎ�L�[�ƂȂ��̌^�Ȃ�
preg_match('/^(\w+)(?:\((\w+)\))?:/', $ini['General']['dsn'], $m);
switch ($m[1]) { // phptype
case 'mysql':
case 'mysqli':
    $serial = 'INTEGER PRIMARY KEY AUTO_INCREMENT';
    $table_extra_defs = ' TYPE=MyISAM';
    $version = $db->getRow("SHOW VARIABLES LIKE 'version'", array(), DB_FETCHMODE_ORDERED);
    if (!DB::isError($version) && version_compare($version[1], '4.1.0') != -1) {
        $charset = $db->getRow("SHOW VARIABLES LIKE 'character_set_database'", array(), DB_FETCHMODE_ORDERED);
        if (!DB::isError($charset) && $charset[1] == 'latin1') {
            $errmsg = "<p><b>Warning:</b> �f�[�^�x�[�X�̕����Z�b�g�� latin1 �ɐݒ肳��Ă��܂��B</p>";
            $errmsg .= "<p>mysqld �� default-character-set �� binary, ujis, utf8 ���łȂ��Ɠ��{��̕���������̂� ";
            $errmsg .= "<a href=\"http://www.mysql.gr.jp/frame/modules/bwiki/?FAQ#content_1_40\">���{MySQL���[�U���FAQ</a>";
            $errmsg .= " ���Q�l�� my.cnf �̐ݒ��ς��Ă��������B</p>";
            die($errmsg);
        }
        $db->query('SET NAMES utf8');
        if (version_compare($version[1], '4.1.2') != -1) {
            $table_extra_defs = ' ENGINE=MyISAM DEFAULT CHARACTER SET utf8';
            //$table_extra_defs = ' ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci';
            //$table_extra_defs = ' ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_bin';
        }
    }
    break;
case 'pgsql':
    $serial = 'SERIAL PRIMARY KEY';
    $table_extra_defs = '';
    break;
case 'sqlite':
    $serial = 'INTEGER PRIMARY KEY';
    $table_extra_defs = '';
    break;
default:
    die('MySQL, PostgreSQL, SQLite2�ȊO�̃f�[�^�x�[�X�ɂ͑Ή����Ă��܂���B');
}

// �e�[�u�����͐ݒ�ɂ���Ă�DB�̗\��ꂪ�g���邩������Ȃ��̂�DB_xxx::quoteIdentifier()��
// �N�H�[�g���邪�A�J�������ɂ͗\�����g��Ȃ��̂�quoteIdentifier()�͏ȗ�����B

$createTableSQL = array();
$createIndexSQL = array();
$format_createIndex = 'CREATE INDEX %s ON %s (%s);';

// ���C���e�[�u��
$imgcache_table_quoted = $db->quoteIdentifier($ini['General']['table']);
$createTableSQL['imgcache'] = <<<EOQ
CREATE TABLE $imgcache_table_quoted (
    id     $serial,
    uri    VARCHAR (255),
    host   VARCHAR (255),
    name   VARCHAR (255),
    size   INTEGER NOT NULL,
    md5    CHAR (32) NOT NULL,
    width  SMALLINT NOT NULL,
    height SMALLINT NOT NULL,
    mime   VARCHAR (50) NOT NULL,
    time   INTEGER NOT NULL,
    rank   SMALLINT NOT NULL DEFAULT 0,
    memo   TEXT
)$table_extra_defs;
EOQ;

// ���C���e�[�u���̃C���f�b�N�X�iURL�j
$createIndexSQL['imgcache_uri'] = sprintf($format_createIndex,
    $db->quoteIdentifier('idx_'.$ini['General']['table'].'_uri'),
    $imgcache_table_quoted,
    'uri'
);

// ���C���e�[�u���̃C���f�b�N�X�i�L���b�V���������Ԃ�UNIX�^�C���X�^���v�j
$createIndexSQL['imgcache_time'] = sprintf($format_createIndex,
    $db->quoteIdentifier('idx_'.$ini['General']['table'].'_time'),
    $imgcache_table_quoted,
    'time'
);

// ���C���e�[�u���̃C���f�b�N�X�i�t�@�C���T�C�Y�EMD5�`�F�b�N�T���EMIME�^�C�v�j
$createIndexSQL['imgcache_unique'] = sprintf($format_createIndex,
    $db->quoteIdentifier('idx_'.$ini['General']['table'].'_unique'),
    $imgcache_table_quoted,
    'size, md5, mime'
);

// �G���[���O�p�e�[�u��
$ic2error_table_quoted = $db->quoteIdentifier($ini['General']['error_table']);
$createTableSQL['ic2_error'] = <<<EOQ
CREATE TABLE $ic2error_table_quoted (
    uri     VARCHAR (255),
    errcode VARCHAR(64) NOT NULL,
    errmsg  TEXT,
    occured INTEGER NOT NULL
)$table_extra_defs;
EOQ;

// �G���[���O�̃C���f�b�N�X�iURL�j
$createIndexSQL['errorlog_uri'] = sprintf($format_createIndex,
    $db->quoteIdentifier('idx_'.$ini['General']['error_table'].'_uri'),
    $ic2error_table_quoted,
    'uri'
);

// �u���b�N���X�g
$blacklist_table_quoted = $db->quoteIdentifier($ini['General']['blacklist_table']);
$createTableSQL['blacklist'] = <<<EOQ
CREATE TABLE $blacklist_table_quoted (
    id     $serial,
    uri    VARCHAR (255),
    size   INTEGER NOT NULL,
    md5    CHAR (32) NOT NULL,
    type   SMALLINT NOT NULL DEFAULT 0
)$table_extra_defs;
EOQ;

// �u���b�N���X�g�̃C���f�b�N�X�iURL�j
$createIndexSQL['blacklist_uri'] = sprintf($format_createIndex,
    $db->quoteIdentifier('idx_'.$ini['General']['blacklist_table'].'_uri'),
    $blacklist_table_quoted,
    'uri'
);

// �u���b�N���X�g�̃C���f�b�N�X�i�t�@�C���T�C�Y�EMD5�`�F�b�N�T���EMIME�^�C�v�j
$createIndexSQL['blacklist_unique'] = sprintf($format_createIndex,
    $db->quoteIdentifier('idx_'.$ini['General']['blacklist_table'].'_unique'),
    $blacklist_table_quoted,
    'size, md5'
);

// }}}
// {{{ �֐�

function ic2_createTable($sql)
{
    global $db, $ok;

    echo "<pre>{$sql}</pre>\n";
    echo "<p><strong>";

    $result = $db->query($sql);

    if (DB::isError($result)) {
        $why = $result->getMessage();
        if (!stristr($why, 'already exists')) {
            $ok = false;
        }
        echo $why;
    } else {
        echo "OK!";
    }

    echo "</strong></p>\n";
}

function ic2_createIndex($sql)
{
    global $db, $ok;

    echo "<pre>{$sql}</pre>\n";
    echo "<p><strong>";

    $result = $db->query($sql);

    if (DB::isError($result)) {
        $why = $result->getMessage();
        echo $why;
        if (!stristr($why, 'already exists') && !stristr($why, 'unknown error')) {
            $ok = false;
        } else {
            echo " (���ɃC���f�b�N�X�쐬�ς݂Ȃ�OK)";
        }
    } else {
        echo "OK!";
    }

    echo "</strong></p>\n";
}

// }}}
// {{{ �m�F���\��

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <title>ImageCache2::Install</title>
</head>
<body style="background:white;font-size:small">

<h1>ImageCache2::install</h1>

<hr>

<h2>�C���[�W�h���C�o</h2>

<p><?php echo $ini['General']['driver']; ?> - OK!</p>

<hr>

<h2>�e�[�u�����쐬</h2>

<?php
foreach ($createTableSQL as $sql) {
    ic2_createTable($sql);
}
?>

<hr>

<h2>�C���f�b�N�X���쐬</h2>

<?php
foreach ($createIndexSQL as $sql) {
    ic2_createIndex($sql);
}
?>

<hr>

<h2>�f�B���N�g�����쐬</h2>

<?php
$dirs = array(
    $ini['Source']['name'],
    $ini['Thumb1']['name'],
    $ini['Thumb2']['name'],
    $ini['Thumb3']['name'],
);

foreach ($dirs as $dir) {
    $path = $ini['General']['cachedir'] . DIRECTORY_SEPARATOR . $dir;
    if (is_dir($path)) {
        echo "<p>�f�B���N�g�� <em>{$path}</em> �͍쐬��";
        if (is_writable($path)) {
            echo "�i�������݌�������j</p>\n";
        } else {
            echo "�i<strong>�������݌����Ȃ�</strong>�j</p>\n";
            $ok = false;
        }
    } else {
        if (@mkdir($path)) {
            echo "<p>�f�B���N�g�� <em>{$path}</em> ���쐬</p>\n";
        } else {
            echo "<p>�f�B���N�g�� <em>{$path}</em> ��<strong>�쐬���s</strong></p>\n";
            $ok = false;
        }
    }
}
?>

<hr>

<h2>.htaccess���쐬</h2>

<?php
$htaccess_path = $ini['General']['cachedir'] . '/.htaccess';
$htaccess_cont = <<<EOS
Order allow,deny
Deny from all
<FilesMatch "\\.(gif|jpg|png)\$">
    Allow from all
</FilesMatch>\n
EOS;
$cachedir_path_ht = htmlspecialchars(realpath($ini['General']['cachedir']), ENT_QUOTES);
$htaccess_path_ht = htmlspecialchars($htaccess_path, ENT_QUOTES);
$htaccess_cont_ht = htmlspecialchars($htaccess_cont, ENT_QUOTES);

if (FileCtl::file_write_contents($htaccess_path, $htaccess_cont) !== false) {
    echo <<<EOS
<p>�t�@�C�� <em>{$htaccess_path_ht}</em> ���쐬</p>
<div>Apache�̏ꍇ�A�p�t�H�[�}���X�̂��߁A�܂��A.htaccess���̂�������������Ȃ��̂ŁA��L.htacces���폜����httpd.conf�Ɉȉ��̂悤�ȋL�q�����邱�Ƃ��������߂��܂��B</div>
<pre>&lt;Directory &quot;{$cachedir_path_ht}&quot;&gt;
{$htaccess_cont_ht}&lt;/Directory&gt;</pre>
EOS;
} else {
    echo "<p>�t�@�C�� <em>{$htaccess_path_ht}</em> ��<strong>�쐬���s</strong></p>\n";
    $ok = false;
}

?>

<hr>

<h2><?php echo ($ok ? "����OK" : "���߂�"); ?></h2>

<?php if (!$ok) echo '<!-- '; ?>
<p>�C���X�g�[���ɐ��������炱�̃t�@�C��(ic2_install.php)�͍폜���Ă��������B</p>
<?php if (!$ok) echo ' -->'; ?>

</body>
</html>
<?php
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
