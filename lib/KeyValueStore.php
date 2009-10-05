<?php
// {{{ KeyValueStore

/**
 * �L�[/�l�̃y�A��SQLite3�̃f�[�^�x�[�X�ɕۑ����� Key-Value Store
 */
class KeyValueStore implements ArrayAccess, Countable, IteratorAggregate
{
    // {{{ constants

    const Q_TABLEEXISTS = 'SELECT 1 FROM sqlite_master WHERE type = \'table\' AND name = :table LIMIT 1';
    const Q_CREATETABLE = 'CREATE TABLE $__table (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  arkey TEXT NOT NULL ON CONFLICT FAIL UNIQUE ON CONFLICT REPLACE,
  value TEXT NOT NULL,
  mtime INTEGER DEFAULT (strftime(\'%s\',\'now\')),
  sort_order INTEGER DEFAULT 0
)';
    const Q_COUNT       = 'SELECT COUNT(*) FROM $__table LIMIT 1';
    const Q_EXSITS      = 'SELECT id, mtime FROM $__table WHERE arkey = :key LIMIT 1';
    const Q_FINDBYID    = 'SELECT * FROM $__table WHERE id = :id LIMIT 1';
    const Q_GET         = 'SELECT * FROM $__table WHERE arkey = :key LIMIT 1';
    const Q_GETALL      = 'SELECT * FROM $__table';
    const Q_GETIDS      = 'SELECT id FROM $__table';
    const Q_GETKEYS     = 'SELECT arkey FROM $__table';
    const Q_SAVE        = 'INSERT INTO $__table (arkey, value, sort_order) VALUES (:key, :value, :order)';
    const Q_UPDATE      = 'UPDATE $__table SET value = :value, mtime = strftime(\'%s\',\'now\'), sort_order = :order WHERE arkey = :key';
    const Q_TOUCH       = 'UPDATE $__table SET mtime = (CASE WHEN :mtime IS NULL THEN strftime(\'%s\',\'now\') ELSE :mtime END) WHERE arkey = :key';
    const Q_SETORDER    = 'UPDATE $__table SET sort_order = :order WHERE arkey = :key';
    const Q_DELETE      = 'DELETE FROM $__table WHERE arkey = :key';
    const Q_DELETEBYID  = 'DELETE FROM $__table WHERE id = :id';
    const Q_CLEAN       = 'DELETE FROM $__table';
    const Q_GC          = 'DELETE FROM $__table WHERE mtime < :expires';

    // }}}
    // {{{ staric private properties

    /**
     * �f�[�^�x�[�X���Ɉ�ӂ�PDO,PDOStatement,KeyValueStore�̃C���X�^���X��ێ�����z��
     *
     * @var array
     */
    static private $_objects = array();

    // }}}
    // {{{ private properties

    /**
     * PDO�̃C���X�^���X
     *
     * @var PDO
     */
    private $_conn;

    /**
     * SQLite3�f�[�^�x�[�X�̃p�X
     *
     * @var string
     */
    private $_path;

    /**
     * ���ʎq�Ƃ��ăN�H�[�g�ς݂̃e�[�u����
     *
     * @var string
     */
    private $_quotedTableName;

    // }}}
    // {{{ getStore()

    /**
     * �V���O���g�����\�b�h
     *
     * @param string $fileName
     * @param string $className
     * @param string &$openedPath
     * @return KeyValueStore
     * @throws InvalidArgumentException, UnexpectedValueException, RuntimeException, PDOException
     */
    static public function getStore($fileName, $className = 'KeyValueStore', &$openedPath = null)
    {
        // �����̌^���`�F�b�N
        if (!is_string($fileName)) {
            throw new InvalidArgumentException('Parameter #1 \'$fileName\' should be a string value');
        }
        if (!is_string($className)) {
            throw new InvalidArgumentException('Parameter #2 \'$className\' should be a string value');
        }

        // �N���X�����`�F�b�N
        if (strcasecmp($className, 'KeyValueStore') != 0) {
            if (!class_exists($className, false)) {
                throw new UnexpectedValueException("Class '{$className}' is not declared");
            }
            if (!is_subclass_of($className, 'KeyValueStore')) {
                throw new UnexpectedValueException("Class '{$className}' is not a subclass of KeyValueStore");
            }
        }

        // �f�[�^�x�[�X�t�@�C�����`�F�b�N
        if ($fileName == ':memory:') {
            $path = $fileName;
            $createTable = true;
        } elseif (file_exists($fileName)) {
            if (!is_file($fileName)) {
                throw new RuntimeException("'{$fileName}' is not a standard file");
            }
            if (!is_writable($fileName)) {
                throw new RuntimeException("File '{$fileName}' is not writable");
            }
            $path = realpath($fileName);
            $createTable = false;
        } else {
            if (strpos($fileName, '/') !== false ||
                (strncasecmp(PHP_OS, 'WIN', 3) == 0 && strpos($fileName, '\\') !== false))
            {
                $dirName = dirname($fileName);
                $baseName = basename($fileName);
            } else {
                $dirName = getcwd();
                $baseName = $fileName;
            }
            if (!is_string($dirName) || !is_dir($dirName)) {
                throw new RuntimeException("No directory for '{$fileName}'");
            }
            if (!is_writable($dirName)) {
                throw new RuntimeException("Directory '{$dirName}' is not writable");
            }
            $path = realpath($dirName) . DIRECTORY_SEPARATOR . $baseName;
            $createTable = true;
        }

        $lcname = strtolower($className);
        $tableName = 'kvs_' . $lcname;
        $openedPath = $path;

        // �C���X�^���X���쐬���A�ÓI�ϐ��ɕێ�
        if (array_key_exists($path, self::$_objects)) {
            if (array_key_exists($lcname, self::$_objects[$path]['persisters'])) {
                $kvs = self::$_objects[$path]['persisters'][$lcname];
            } else {
                $conn = self::$_objects[$path]['connection'];
                $kvs = new $className($conn, $path, $tableName);
                self::$_objects[$path]['persisters'][$lcname] = $kvs;
            }
        } else {
            $conn = new PDO('sqlite:' . $path);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $kvs = new $className($conn, $path, $tableName);
            self::$_objects[$path] = array(
                'connection' => $conn,
                'statements' => array(),
                'persisters' => array($lcname => $kvs),
            );
        }

        return $kvs;
    }

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     * getStore()����Ăяo�����
     *
     * @param PDO $conn
     * @param string $path
     * @param string $tableName
     * @throws PDOException
     */
    private function __construct(PDO $conn, $path, $tableName)
    {
        $this->_conn = $conn;
        $this->_path = $path;
        $this->_quotedTableName = '"' . str_replace('"', '""', $tableName) . '"';

        // �e�[�u�������݂��邩�𒲂�
        $stmt = $conn->prepare(self::Q_TABLEEXISTS);
        $stmt->bindValue(':table', $tableName, PDO::PARAM_STR);
        $stmt->execute();
        $exists = $stmt->fetchColumn();
        $stmt->closeCursor();
        unset($stmt);

        // ������΍��
        if (!$exists) {
            $conn->exec(str_replace('$__table', $this->_quotedTableName, self::Q_CREATETABLE));
        }
    }

    // }}}
    // {{{ _prepare()

    /**
     * �v���y�A�[�h�X�e�[�g�����g���쐬����
     *
     * @param string $query
     * @param bool $isTemporary
     * @return PDOStatement
     * @throws PDOException
     */
    private function _prepare($query, $isTemporary = false)
    {
        $query = str_replace('$__table', $this->_quotedTableName, $query);

        if (!$isTemporary && array_key_exists($query, self::$_objects[$this->_path]['statements'])) {
            $stmt = self::$_objects[$this->_path]['statements'][$query];
        } else {
            if (strncmp($query, 'SELECT ', 7) == 0) {
                $stmt = $this->_conn->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            } else {
                $stmt = $this->_conn->prepare($query);
            }
            if (!$isTemporary) {
                self::$_objects[$this->_path]['statements'][$query] = $stmt;
            }
        }

        return $stmt;
    }

    // }}}
    // {{{ _buildOrderBy()

    /**
     * ���R�[�h���܂Ƃ߂Ď擾����ۂ�OREDER BY��𐶐�����
     *
     * @param array $orderBy
     * @return string
     */
    private function _buildOrderBy(array $orderBy = null)
    {
        if ($orderBy === null) {
            return ' ORDER BY sort_order ASC, arkey ASC';
        }

        $terms = array();
        foreach ($orderBy as $column => $ascending) {
            $direction = $ascending ? 'ASC' : 'DESC';
            switch ($column) {
                case 'id':
                    $terms[] = 'id ' . $direction;
                    break;
                case 'key':
                    $terms[] = 'arkey ' . $direction;
                    break;
                case 'value':
                    $terms[] = 'value ' . $direction;
                    break;
                case 'mtime':
                    $terms[] = 'mtime ' . $direction;
                    break;
                case 'order':
                    $terms[] = 'sort_order ' . $direction;
                    break;
            }
        }

        if (count($terms)) {
            return ' ORDER BY ' . implode(', ', $terms);
        } else {
            return '';
        }
    }

    // }}}
    // {{{ _buildLimit()

    /**
     * ���R�[�h���܂Ƃ߂Ď擾����ۂ�LIMIT���OFFSET��𐶐�����
     *
     * @param int $limit
     * @param int $offset
     * @return string
     */
    private function _buildLimit($limit = null, $offset = null)
    {
        if ($limit === null) {
            return '';
        } elseif ($offset === null) {
            return sprintf(' LIMIT %d', $limit);
        } else {
            return sprintf(' LIMIT %d OFFSET %d', $limit, $offset);
        }
    }

    // }}}
    // {{{ _encodeKey()

    /**
     * �L�[��UTF-8 or US-ASCII������ɃG���R�[�h����
     *
     * @param string $key
     * @return string
     */
    protected function _encodeKey($key)
    {
        return (string)$key;
    }

    // }}}
    // {{{ _decodeKey()

    /**
     * �L�[���f�R�[�h����
     *
     * @param string $key
     * @return string
     */
    protected function _decodeKey($key)
    {
        return $key;
    }

    // }}}
    // {{{ _encodeValue()

    /**
     * �l��UTF-8 or US-ASCII������ɃG���R�[�h����
     *
     * @param string $value
     * @return string
     */
    protected function _encodeValue($value)
    {
        return (string)$value;
    }

    // }}}
    // {{{ _decodeValue()

    /**
     * �l���f�R�[�h����
     *
     * @param string $value
     * @return string
     */
    protected function _decodeValue($value)
    {
        return $value;
    }

    // }}}
    // {{{ exists()

    /**
     * �L�[�ɑΉ�����l���ۑ�����Ă��邩�𒲂ׂ�
     *
     * @param string $key
     * @param int $lifeTime
     * @return bool
     */
    public function exists($key, $lifeTime = null)
    {
        $stmt = $this->_prepare(self::Q_EXSITS);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch();
        $stmt->closeCursor();
        if ($row === false) {
            return false;
        } elseif ($lifeTime !== null && $row['mtime'] < time() - $lifeTime) {
            $this->deleteById($row['id']);
            return false;
        } else {
            return true;
        }
    }

    // }}}
    // {{{ findById()

    /**
     * ID�ɑΉ�����L�[�ƒl�̃y�A���擾����
     * ��Ƃ���KeyValueStoreIterator�Ŏg��
     *
     * @param int $id
     * @param int $lifeTime
     * @return array
     */
    public function findById($id, $lifeTime = null)
    {
        $stmt = $this->_prepare(self::Q_FINDBYID);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        $stmt->closeCursor();
        if ($row === false) {
            return null;
        } elseif ($lifeTime !== null && $row['mtime'] < time() - $lifeTime) {
            $this->deleteById($id);
            return null;
        } else {
            return array(
                'key' => $this->_decodeKey($row['arkey']),
                'value' => $this->_decodeValue($row['value']),
            );
        }
    }

    // }}}
    // {{{ get()

    /**
     * �L�[�ɑΉ�����l���擾����
     *
     * @param string $key
     * @param int $lifeTime
     * @return string
     */
    public function get($key, $lifeTime = null)
    {
        $stmt = $this->_prepare(self::Q_GET);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch();
        $stmt->closeCursor();
        if ($row === false) {
            return null;
        } elseif ($lifeTime !== null && $row['mtime'] < time() - $lifeTime) {
            $this->deleteById($row['id']);
            return null;
        } else {
            return $this->_decodeValue($row['value']);
        }
    }

    // }}}
    // {{{ getDetail()

    /**
     * �L�[�ɑΉ����郌�R�[�h���擾����
     *
     * @param string $key
     * @param int $lifeTime
     * @return array
     */
    public function getDetail($key, $lifeTime = null)
    {
        $stmt = $this->_prepare(self::Q_GET);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch();
        $stmt->closeCursor();
        if ($row === false) {
            return null;
        } elseif ($lifeTime !== null && $row['mtime'] < time() - $lifeTime) {
            $this->deleteById($row['id']);
            return null;
        } else {
            return array(
                'id' => (int)$row['id'],
                'key' => $this->_decodeKey($row['arkey']),
                'value' => $this->_decodeValue($row['value']),
                'mtime' => (int)$row['mtime'],
                'order' => (int)$row['sort_order'],
            );
        }
    }

    // }}}
    // {{{ getAll()

    /**
     * �S�Ẵ��R�[�h��A�z�z��Ƃ��ĕԂ�
     * �L�������؂�̃��R�[�h�����O�������ꍇ�͎��O��gc()���Ă�������
     *
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @param bool $getDetails
     * @return array
     */
    public function getAll(array $orderBy = null, $limit = null, $offset = null, $getDetails = false)
    {
        $query = self::Q_GETALL
               . $this->_buildOrderBy($orderBy)
               . $this->_buildLimit($limit, $offset);
        $stmt = $this->_prepare($query, true);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        $values = array();
        if ($getDetails) {
            while ($row = $stmt->fetch()) {
                $key = $this->_decodeKey($row['arkey']);
                $values[$key] = array(
                    'id' => (int)$row['id'],
                    'key' => $key,
                    'value' => $this->_decodeValue($row['value']),
                    'mtime' => (int)$row['mtime'],
                    'order' => (int)$row['sort_order'],
                );
            }
        } else {
            while ($row = $stmt->fetch()) {
                $values[$this->_decodeKey($row['arkey'])] = $this->_decodeValue($row['value']);
            }
        }
        $stmt->closeCursor();
        return $values;
    }

    // }}}
    // {{{ getIds()

    /**
     * �S�Ă�ID�̔z���Ԃ�
     * ��Ƃ���KeyValueStoreIterator�Ŏg��
     * �L�������؂�̃��R�[�h�����O�������ꍇ�͎��O��gc()���Ă�������
     *
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getIds(array $orderBy = null, $limit = null, $offset = null)
    {
        $query = self::Q_GETIDS
               . $this->_buildOrderBy($orderBy)
               . $this->_buildLimit($limit, $offset);
        $stmt = $this->_prepare($query, true);
        $stmt->setFetchMode(PDO::FETCH_COLUMN, 0);
        $stmt->execute();
        $ids = array();
        while (($id = $stmt->fetch()) !== false) {
            $ids[] = (int)$id;
        }
        $stmt->closeCursor();
        return $ids;
    }

    // }}}
    // {{{ getKeys()

    /**
     * �S�ẴL�[�̔z���Ԃ�
     * �L�������؂�̃��R�[�h�����O�������ꍇ�͎��O��gc()���Ă�������
     *
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getKeys(array $orderBy = null, $limit = null, $offset = null)
    {
        $query = self::Q_GETKEYS
               . $this->_buildOrderBy($orderBy)
               . $this->_buildLimit($limit, $offset);
        $stmt = $this->_prepare($query, true);
        $stmt->setFetchMode(PDO::FETCH_COLUMN, 0);
        $stmt->execute();
        $keys = array();
        while (($key = $stmt->fetch()) !== false) {
            $keys[] = $this->_decodeKey($key);
        }
        $stmt->closeCursor();
        return $keys;
    }

    // }}}
    // {{{ set()

    /**
     * �f�[�^��ۑ�����
     *
     * @param string $key
     * @param string $value
     * @param int $order
     * @return bool
     */
    public function set($key, $value, $order = 0)
    {
        $stmt = $this->_prepare(self::Q_SAVE);
        $stmt->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        $stmt->bindValue(':value', $this->_encodeValue($value), PDO::PARAM_STR);
        $stmt->bindValue(':order', (int)$order, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return $stmt->rowCount() == 1;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ update()

    /**
     * �f�[�^���X�V����
     *
     * @param string $key
     * @param string $value
     * @param int $order
     * @return bool
     */
    public function update($key, $value, $order = 0)
    {
        $stmt = $this->_prepare(self::Q_UPDATE);
        $stmt->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        $stmt->bindValue(':value', $this->_encodeValue($value), PDO::PARAM_STR);
        $stmt->bindValue(':order', (int)$order, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return $stmt->rowCount() == 1;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ touch()

    /**
     * �f�[�^�̍X�V���������ݎ����ɐݒ肷��
     *
     * @param string $key
     * @param int $time
     * @return bool
     */
    public function touch($key, $time = null)
    {
        $stmt = $this->_prepare(self::Q_TOUCH);
        $stmt->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        if ($time === null) {
            $stmt->bindValue(':mtime', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':mtime', (int)$time, PDO::PARAM_INT);
        }
        if ($stmt->execute()) {
            return $stmt->rowCount() == 1;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ setOrder()

    /**
     * �f�[�^�̕��я� (sort_order�J�����̒l) ��ݒ肷��
     *
     * @param string $key
     * @param int $order
     * @return bool
     */
    public function setOrder($key, $order)
    {
        $stmt = $this->_prepare(self::Q_SETORDER);
        $stmt->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        $stmt->bindValue(':order', (int)$order, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return $stmt->rowCount() == 1;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ delete()

    /**
     * �L�[�ɑΉ����郌�R�[�h���폜����
     *
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        $stmt = $this->_prepare(self::Q_DELETE);
        $stmt->bindValue(':key', $this->_encodeKey($key), PDO::PARAM_STR);
        if ($stmt->execute()) {
            return $stmt->rowCount() == 1;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ deleteById()

    /**
     * ID�ɑΉ����郌�R�[�h���폜����
     *
     * @param int $id
     * @return bool
     */
    public function deleteById($id)
    {
        $stmt = $this->_prepare(self::Q_DELETEBYID);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return $stmt->rowCount() == 1;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ clean()

    /**
     * ���ׂẴ��R�[�h���폜����
     *
     * @param void
     * @return int
     */
    public function clean()
    {
        $stmt = $this->_prepare(self::Q_CLEAN, true);
        if ($stmt->execute()) {
            return $stmt->rowCount();
        } else {
            return false;
        }
    }

    // }}}
    // {{{ gc()

    /**
     * �����؂�̃��R�[�h���폜����
     *
     * @param int $lifeTime
     * @return int
     */
    public function gc($lifeTime)
    {
        $stmt = $this->_prepare(self::Q_GC, true);
        $stmt->bindValue(':expires', time() - $lifeTime, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return $stmt->rowCount();
        } else {
            return false;
        }
    }

    // }}}
    // {{{ vacuum()

    /**
     * �쐬�ς݃v���y�A�[�h�X�e�[�g�����g���N���A���AVACUUM�𔭍s����
     * ���̃v���Z�X�������f�[�^�x�[�X���J���Ă���Ƃ��Ɏ��s���ׂ��ł͂Ȃ�
     *
     * @param void
     * @return void
     */
    public function vacuum()
    {
        self::$_objects[$this->_path]['statements'] = array();
        $this->_conn->exec('VACUUM');
    }

    // }}}
    // {{{ count()

    /**
     * Countable::count()
     *
     * ���R�[�h�̑�����Ԃ�
     *
     * @param void
     * @return int
     */
    public function count()
    {
        $stmt = $this->_prepare(self::Q_COUNT);
        $stmt->execute();
        $ret = (int)$stmt->fetchColumn();
        $stmt->closeCursor();
        return $ret;
    }

    // }}}
    // {{{ offsetExists()

    /**
     * ArrayAccess::offsetExists()
     *
     * @param string $offset
     * @return string
     */
    public function offsetExists($offset)
    {
        return $this->exists($offset);
    }

    // }}}
    // {{{ offsetGet()

    /**
     * ArrayAccess::offsetGet()
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    // }}}
    // {{{ offsetSet()

    /**
     * ArrayAccess::offsetSet()
     *
     * @param string $offset
     * @param string $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    // }}}
    // {{{ offsetUnset()

    /**
     * ArrayAccess::offsetUnset()
     *
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }

    // }}}
    // {{{ getIterator()

    /**
     * IteratorAggregate::getIterator()
     *
     * ��������get�n���\�b�h���Ă΂�Ă����v�Ȃ悤��
     * �\�ߎ擾����ID�̃��X�g���g���C�e���[�^��Ԃ�
     *
     * @param void
     * @return KeyValueStoreIterator
     */
    public function getIterator()
    {
        if (!class_exists('KeyValueStoreIterator', false)) {
            include dirname(__FILE__) . '/KeyValueStoreIterator.php';
        }
        return new KeyValueStoreIterator($this);
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
