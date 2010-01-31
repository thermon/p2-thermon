<?php
/**
 * rep2expack - ��^���Ǘ��N���X
 */

// {{{ constants

define('PRESETMANAGER_REAF_FIRST',    0);
define('PRESETMANAGER_NODE_FIRST',    1);
define('PRESETMANAGER_GROUP_BY_TYPE', 2);
define('PRESETMANAGER_ALL_LEAVES_FIRST',
       PRESETMANAGER_REAF_FIRST | PRESETMANAGER_GROUP_BY_TYPE);
define('PRESETMANAGER_ALL_NODES_FIRST',
       PRESETMANAGER_NODE_FIRST | PRESETMANAGER_GROUP_BY_TYPE);

// }}}
// {{{ PresetManager

/**
 * ��^���Ǘ��N���X
 *
 * �����R�[�h�͓��o�͂�CP932�A����������UTF-8
 */
class PresetManager
{
    // {{{ properties

    /**
     * ��^���̘A�z�z��
     *
     * @var array
     */
    private $_data;

    /**
     * �ݒ�t�@�C���̃p�X��
     *
     * @var string
     */
    private $_filename;

    /**
     * ���s�������邩�ۂ�
     *
     * @var bool
     */
    private $_allowLinebreaks;

    /**
     * �c���[�\�����[�h (0-3)
     *
     * @var bool
     */
    private $_treeMode;

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     *
     * @param string $filename
     * @param bool $allow_linebreaks
     */
    public function __construct($filename, $allow_linebreaks = false)
    {
        if (!file_exists($filename)) {
            FileCtl::make_datafile($filename);
            $this->_data = array();
        } else {
            $content = FileCtl::file_read_contents($filename);
            if (!$content || !is_array($this->_data = @unserialize($content))) {
                $this->_data = array();
            }
        }

        $this->_filename = $filename;
        $this->_allowLinebreaks = $allow_linebreaks;
    }

    // }}}
    // {{{ setData()

    /**
     * ��^����o�^����
     *
     * @param string $key
     * @param string $value
     * @param bool $overwrite
     * @return bool
     */
    public function setData($key, $value, $overwrite = true)
    {
        $key = $this->_normalizeKey($key);
        if ($key === '') {
            return false;
        }

        if (array_key_exists($key, $this->_data) && !$overwrite) {
            return false;
        }

        $value = $this->_normalizeValue($value);
        if ($value === '') {
            return false;
        }

        $this->_data[$key] = $value;
        return true;
    }

    // }}}
    // {{{ getData()

    /**
     * ��^�����擾����
     *
     * @param string $key
     * @return string|null
     */
    public function getData($key)
    {
        $key = $this->_normalizeKey($key);
        if (array_key_exists($key, $this->_data)) {
            return mb_convert_encoding($this->_data[$key], 'CP932', 'UTF-8');
        }

        return null;
    }

    // }}}
    // {{{ hasData()

    /**
     * ��^�����o�^����Ă��邩�ǂ������肷��
     *
     * @param string $key
     * @return bool
     */
    public function hasData($key)
    {
        $key = $this->_normalizeKey($key);
        if ($key === '') {
            return false;
        }

        return array_key_exists($key, $this->_data);
    }

    // }}}
    // {{{ removeData()

    /**
     * ��^�����폜����
     *
     * @param string $key
     * @return bool
     */
    public function removeData($key)
    {
        $key = $this->_normalizeKey($key);
        if ($key === '') {
            return false;
        }

        if (array_key_exists($key, $this->_data)) {
            unset($this->_data);
            return true;
        }
        return false;
    }

    // }}}
    // {{{ clearAllData()

    /**
     * ��^�������ׂč폜����
     *
     * @return void
     */
    public function clearAllData()
    {
        $this->_data = array();
    }

    // }}}
    // {{{ getAllData()

    /**
     * ��^�������ׂĎ擾����
     *
     * @param bool $as_tree
     * @param bool $as_utf8
     * @return array|stdClass
     */
    public function getAllData($as_tree = false, $as_utf8 = false)
    {
        ksort($this->_data);

        if ($as_tree) {
            $ret = $this->_createNode();

            foreach ($this->_data as $key => $value) {
                // $key��_normalizeKey()�K�p�ς̑Ó��Ȓl�ł���O��ŏ�������
                $keys = explode('/', $key);
                if (!$as_utf8) {
                    mb_convert_variables('CP932', 'UTF-8', $keys);
                }

                $ref = $ret;
                foreach ($keys as $k) {
                    if (!array_key_exists($k, $ref->children)) {
                        $ref->children[$k] = $this->_createNode($k, null, $ref->depth + 1);
                    }
                    $ref = $ref->children[$k];
                }

                if ($as_utf8) {
                    $ref->value = $value;
                } else {
                    $ref->value = mb_convert_encoding($value, 'CP932', 'UTF-8');
                }
            }

            unset($ref);
        } elseif ($as_utf8) {
            $ret = $this->_data;
        } else {
            $keys = array_keys($this->_data);
            $values = array_values($this->_data);
            mb_convert_variables('CP932', 'UTF-8', $keys, $values);

            if (function_exists('array_combine')) {
                $ret = array_combine($keys, $values);
            } else {
                $ret = array();
                $n = count($keys);
                for ($i = 0; $i < $n; $i++) {
                    $ret[$keys[$i]] = $values[$i];
                }
            }
        }

        return $ret;
    }

    // }}}
    // {{{ getAllDataAsHTML()

    /**
     * ��^�������ׂ�HTML�Ƃ��Ď擾����
     *
     * @param bool $as_tree
     * @param bool $as_utf8
     * @param array $options
     * @return string
     */
    public function getAllDataAsHTML($as_tree = false, $as_utf8 = false, $options = array())
    {
        $data = $this->getAllData($as_tree, $as_utf8);

        if (isset($options['id']) && strlen($options['id']) > 0) {
            $id_str = htmlspecialchars($options['id'], ENT_QUOTES);
            $id_attr = " id=\"{$id_str}\"";
        } else {
            $id_attr = $id_str = '';
        }

        if (isset($options['class']) && strlen($options['class']) > 0) {
            $class_str = htmlspecialchars($options['class'], ENT_QUOTES);
            $class_attr = " class=\"{$class_str}\"";
        } else {
            $class_attr = $class_str = '';
        }

        if (isset($options['linker']) && function_exists($options['linker'])) {
            $linker = $options['linker'];
        } else {
            $linker = 'presetmanager_samplelinker';
        }

        $ret = "<ul{$id_attr}{$class_attr}>\n";

        if ($as_tree) {
            $this->_treeMode = 0;
            if (isset($options['tree_mode'])) {
                $mode = (int) $options['tree_mode'];
                if ($mode > 0 && $mode < 4) {
                    $this->_treeMode = $mode;
                }
            }

            if ($this->_treeMode == PRESETMANAGER_ALL_LEAVES_FIRST) {
                // �S�Ẵ��[�t���u�����`����ɕ\��
                foreach ($data->children as $node) {
                    $ret .= $this->_leafToHTML($node, $linker, $id_str);
                }
            }

            foreach ($data->children as $node) {
                $ret .= $this->_nodeToHTML($node, $linker, $id_str);
            }

            if ($this->_treeMode == PRESETMANAGER_ALL_NODES_FIRST) {
                // �S�Ẵ��[�t���u�����`����ɕ\��
                foreach ($data->children as $node) {
                    $ret .= $this->_leafToHTML($node, $linker, $id_str);
                }
            }
        } else {
            foreach ($data as $key => $value) {
                $ret .= $this->_leafToHTML($this->_createNode($key, $value, 1), $linker, $id_str);
            }
        }

        $ret .= "</ul>\n";

        return $ret;
    }

    // }}}
    // {{{ save()

    /**
     * ��^����ۑ�����
     *
     * @return bool
     */
    public function save()
    {
        ksort($this->_data);
        return (FileCtl::file_write_contents($this->_filename, serialize($this->_data)) !== false);
    }

    // }}}
    // {{{ _normalizeKey()

    /**
     * ��^���̃L�[�𐳋K������
     *
     * @param string $key
     * @return string
     */
    private function _normalizeKey($key)
    {
        $key = mb_convert_encoding($key, 'UTF-8', 'CP932');
        $key = preg_replace(array('/[\\x00-\\x20]+/u', '@//+@u'), array(' ', '/'), $key);
        return (string) trim(preg_replace('@ ?/ ?@u', '/', $key), '/ ');
    }

    // }}}
    // {{{ _normalizeValue()

    /**
     * ��^���̓��e�𐳋K������
     *
     * @param string $value
     * @return string
     */
    private function _normalizeValue($value)
    {
        $value = mb_convert_encoding($value, 'UTF-8', 'CP932');
        $value = preg_replace('/\\r\\n?/u', "\n", $value);
        if ($this->_allowLinebreaks) {
            $value = preg_replace('/[\\x00-\\x09\\x0B\\x0C\\x0E\\x0F]/u', ' ', $value);
        } else {
            $value = preg_replace('/[\\x00-\\x1F]/u', ' ', $value);
        }
        return (string) $value;
    }

    // }}}
    // {{{ _createNode()

    /**
     * �c���[�\���p�̃m�[�h�I�u�W�F�N�g���쐬����
     *
     * @param string $name
     * @return stdClass
     */
    private function _createNode($name = null, $value = null, $depth = 0)
    {
        $node = new stdClass;
        $node->name = $name;
        $node->value = $value;
        $node->depth = $depth;
        $node->children = array();
        return $node;
    }

    // }}}
    // {{{ _nodeToHTML()

    /**
     * �m�[�h��HTML�ɕϊ�����
     *
     * @param stdClass $node
     * @param callback $linker
     * @param string $id_str
     * @return string
     */
    private function _nodeToHTML($node, $linker, $id_str)
    {
        $ret = '';

        if ($this->_treeMode == PRESETMANAGER_REAF_FIRST) {
            // �����̃��[�t���u�����`����ɕ\��
             $ret .= $this->_leafToHTML($node, $linker, $id_str);
        }

        if ($node->children) {
            $indent = str_repeat("\t", $node->depth);

            $ret .= "{$indent}<li>";
            $ret .= $linker($node, $id_str, true);
            $ret .= "<ul>\n";

            if ($this->_treeMode == PRESETMANAGER_ALL_LEAVES_FIRST) {
                // �S�Ẵ��[�t���u�����`����ɕ\��
                foreach ($node->children as $n) {
                    $ret .= $this->_leafToHTML($n, $linker, $id_str);
                }
            }

            foreach ($node->children as $n) {
                $ret .= $this->_nodeToHTML($n, $linker, $id_str);
            }

            if ($this->_treeMode == PRESETMANAGER_ALL_NODES_FIRST) {
                // �S�Ẵ��[�t���u�����`����ɕ\��
                foreach ($node->children as $n) {
                    $ret .= $this->_leafToHTML($n, $linker, $id_str);
                }
            }

            $ret .= "{$indent}</ul></li>\n";
        }

        if ($this->_treeMode == PRESETMANAGER_NODE_FIRST) {
            // �����̃��[�t���u�����`����ɕ\��
            $ret .= $this->_leafToHTML($node, $linker, $id_str);
        }

        return $ret;
    }

    // }}}
    // {{{ _leafToHTML()

    /**
     * ���[�t��HTML�ɕϊ�����
     *
     * @param stdClass $node
     * @param callback $linker
     * @param string $id_str
     * @return string
     */
    private function _leafToHTML($node, $linker, $id_str)
    {
        if ($node->value === null) {
            return '';
        }

        return str_repeat("\t", $node->depth)
               . '<li>'
               . $linker($node, $id_str)
               . "</li>\n";
    }

    // }}}
}

// }}}
// {{{ presetmanager_samplelinker()

/**
 * ���[�t�������N����R�[���o�b�N�֐��̃T���v��
 *
 * @param stdClass $node
 * @param string $id_str
 * @param bool $is_branch
 * @return string
 */
function presetmanager_samplelinker($node, $id_str, $is_branch = false)
{
    if ($is_branch) {
        return sprintf('<span>%s/</span>',
                       htmlspecialchars($node->name, ENT_QUOTES)
                       );
    } else {
        return sprintf('<span title="%s">%s</span>',
                       htmlspecialchars($node->value, ENT_QUOTES),
                       htmlspecialchars($node->name, ENT_QUOTES)
                       );
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
