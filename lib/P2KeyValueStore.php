<?php
require_once dirname(__FILE__) . '/P2KeyValueStore/Codec/Interface.php';

// {{{ P2KeyValueStore

/**
 * �L�[/�l�̃y�A��SQLite3�̃f�[�^�x�[�X�ɕۑ����� Key-Value Store
 */
class P2KeyValueStore implements ArrayAccess, Countable, IteratorAggregate
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
    const Q_SET         = 'INSERT INTO $__table (arkey, value, sort_order) VALUES (:key, :value, :order)';
    const Q_UPDATE      = 'UPDATE $__table SET value = :value, mtime = strftime(\'%s\',\'now\'), sort_order = :order WHERE arkey = :key';
    const Q_TOUCH       = 'UPDATE $__table SET mtime = (CASE WHEN :mtime IS NULL THEN strftime(\'%s\',\'now\') ELSE :mtime END) WHERE arkey = :key';
    const Q_SETORDER    = 'UPDATE $__table SET sort_order = :order WHERE arkey = :key';
    const Q_DELETE      = 'DELETE FROM $__table WHERE arkey = :key';
    const Q_DELETEBYID  = 'DELETE FROM $__table WHERE id = :id';
    const Q_CLEAR       = 'DELETE FROM $__table';
    const Q_GC          = 'DELETE FROM $__table WHERE mtime < :expires';

    const C_KEY_BEGINS = 'arkey LIKE :pattern ESCAPE :escape';

    const CODEC_DEFAULT         = 'P2KeyValueStore_Codec_Default';
    const CODEC_BINARY          = 'P2KeyValueStore_Codec_Binary';
    const CODEC_COMPRESSING     = 'P2KeyValueStore_Codec_Compressing';
    const CODEC_SHIFTJIS        = 'P2KeyValueStore_Codec_ShiftJIS';
    const CODEC_SERIALIZING     = 'P2KeyValueStore_Codec_Serializing';
    const CODEC_ARRAY           = 'P2KeyValueStore_Codec_Array';
    const CODEC_ARRAYSHIFTJIS   = 'P2KeyValueStore_Codec_ArrayShiftJIS';
    const CODEC_JSON            = 'P2KeyValueStore_Codec_JSON';
    const CODEC_JSONSHIFTJIS    = 'P2KeyValueStore_Codec_JSONShiftJIS';
    const CODEC_SIMPLECSV       = 'P2KeyValueStore_Codec_SimpleCSV';

    const MEMORY_DATABASE   = ':memory:';

    // }}}
    // {{{ static private properties

    /**
     * �e��I�u�W�F�N�g��ێ�����z��Q
     *
     * @var array
     */
    static private $_pdoCache   = array();
    static private $_stmtCache  = array();
    static private $_kvsCache   = array();
    static private $_codecCache = array();

    // }}}
    // {{{ private properties

    /**
     * PDO�̃C���X�^���X
     *
     * @var PDO
     */
    private $_pdo;

    /**
     * PDO�̃C���X�^���X�ɑΉ������ӂȒl
     *
     * @var string
     */
    private $_pdoId;

    /**
     * Codec�̃C���X�^���X
     *
     * @var P2KeyValueStore_Codec_Interface
     */
    private $_codec;

    /**
     * �e�[�u����
     *
     * @var string
     */
    private $_tableName;

    /**
     * ���ʎq�Ƃ��ăN�H�[�g�ς݂̃e�[�u����
     *
     * @var string
     */
    private $_quotedTableName;

    /**
     * �g���񂵗p�̌��ʃZ�b�g�I�u�W�F�N�g
     *
     * @var P2KeyValueStore_Result
     */
    private $_sharedResult;

    // }}}
    // {{{ getStore()

    /**
     * �V���O���g�����\�b�h
     *
     * @param string $fileName
     * @param string $codec
     * @param string $tableName
     * @return P2KeyValueStore
     * @throws PDOException
     */
    static public function getStore($fileName,
                                    $codec = self::CODEC_DEFAULT,
                                    $tableName = null)
    {
        // �I�u�W�F�N�gID�ƃe�[�u����������
        if (is_object($codec)) {
            $className = get_class($codec);
        } else {
            $className = $codec;
        }

        $lcName = strtolower($className);
        if (strpos($lcName, 'p2keyvaluestore_codec_') === 0) {
            $codecId = substr($lcName, 22);
        } else {
            $codecId = 'user_' . $lcName;
        }

        $kvsId = $lcName . ':' . $fileName;
        if ($tableName === null) {
            $tableName = 'kvs_' . $codecId;
        } else {
            $kvsId = strtolower($tableName) . ':' . $kvsId;
        }

        // P2KeyValueStore�̃L���b�V�����m�F
        if (array_key_exists($kvsId, self::$_kvsCache)) {
            return self::$_kvsCache[$kvsId];
        }

        // PDO�̃L���b�V�����m�F
        if (array_key_exists($fileName, self::$_pdoCache)) {
            $pdo = self::$_pdoCache[$fileName];
        } else {
            $pdo = new PDO('sqlite:' . $fileName);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$_pdoCache[$fileName] = $pdo;
        }

        // Codec�̃L���b�V�����m�F
        if (!is_object($codec)) {
            if (array_key_exists($codecId, self::$_codecCache)) {
                $codec = self::$_codecCache[$codecId];
            } else {
                $codec = new $className;
                self::$_codecCache[$codecId] = $codec;
            }
        }

        // P2KeyValueStore�̃C���X�^���X���쐬
        $kvs = new P2KeyValueStore($pdo, $codec, $tableName);
        self::$_kvsCache[$kvsId] = $kvs;

        return $kvs;
    }

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     * getStore()����Ăяo�����
     *
     * @param PDO $pdo
     * @param P2KeyValueStore_Codec_Interface $codec
     * @param string $tableName
     * @throws PDOException
     */
    public function __construct(PDO $pdo,
                                P2KeyValueStore_Codec_Interface $codec,
                                $tableName)
    {
        $this->_pdo = $pdo;
        $this->_pdoId = spl_object_hash($pdo);
        $this->_codec = $codec;
        $this->_tableName = $tableName;
        $this->_quotedTableName = '"' . str_replace('"', '""', $tableName) . '"';
        $this->_sharedResult = new P2KeyValueStore_Result;

        if (!array_key_exists($this->_pdoId, self::$_stmtCache)) {
            self::$_stmtCache[$this->_pdoId] = array();
        }

        // �e�[�u�������݂��邩�𒲂�
        $stmt = $pdo->prepare(self::Q_TABLEEXISTS);
        $stmt->bindValue(':table', $tableName);
        $stmt->execute();
        $exists = $stmt->fetchColumn();
        $stmt->closeCursor();
        unset($stmt);

        // ������΍��
        if (!$exists) {
            // ��ɍ쐬�ς݃v���y�A�[�h�X�e�[�g�����g���N���A
            self::$_stmtCache[$this->_pdoId] = array();
            $pdo->exec(str_replace('$__table', $this->_quotedTableName, self::Q_CREATETABLE));
        }
    }

    // }}}
    // {{{ _prepare()

    /**
     * �v���y�A�[�h�X�e�[�g�����g���쐬���� (�����p)
     *
     * @param string $query
     * @param bool $isTemporary
     * @param bool $forwardOnly
     * @return PDOStatement
     * @throws PDOException
     */
    protected function _prepare($query, $isTemporary = false, $forwardOnly = true)
    {
        $query = str_replace('$__table', $this->_quotedTableName, $query);

        if (!$isTemporary && array_key_exists($query, self::$_stmtCache[$this->_pdoId])) {
            $stmt = self::$_stmtCache[$this->_pdoId][$query];
        } else {
            if ($forwardOnly && strncmp($query, 'SELECT ', 7) == 0) {
                $stmt = $this->_pdo->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            } else {
                $stmt = $this->_pdo->prepare($query);
            }
            if (!$isTemporary) {
                self::$_stmtCache[$this->_pdoId][$query] = $stmt;
            }
        }

        return $stmt;
    }

    // }}}
    // {{{ prepare()

    /**
     * �v���y�A�[�h�X�e�[�g�����g���쐬����
     *
     * @param string $query
     * @param bool $forwardOnly
     * @return PDOStatement
     * @throws PDOException
     * @see P2KeyValueStore::_prepare()
     */
    public function prepare($query, $forwardOnly = true)
    {
        $mapping = array(
            '$__key'   => 'arkey',
            '$__value' => 'value',
            '$__mtime' => 'mtime',
            '$__order' => 'sort_order',
        );

        $query = str_replace(array_keys($mapping), array_values($mapping), $query);

        return $this->_prepare($query, true, $forwardOnly);
    }

    // }}}
    // {{{ bindValueForPrefixSearch()

    /**
     * ���R�[�h��ړ����Ō�������ۂ̒l���G�X�P�[�v&�o�C���h����
     *
     * @param PDOStatement $stmt
     * @param string $prefix
     * @param string $escape
     * @return void
     */
    public function bindValueForPrefixSearch(PDOStatement $stmt,
                                             $prefix,
                                             $escape = '\\')
    {
        $pattern = str_replace(array('%', '_', $escape),
                               array("{$escape}%", "{$escape}_", "{$escape}{$escape}"),
                               $this->_codec->encodeKey($prefix)) . '%';
        $stmt->bindValue(':pattern', $pattern);
        $stmt->bindValue(':escape', $escape);
    }

    // }}}
    // {{{ buildOrderBy()

    /**
     * ���R�[�h���܂Ƃ߂Ď擾����ۂ�OREDER BY��𐶐�����
     *
     * @param array $orderBy
     * @return string
     */
    public function buildOrderBy(array $orderBy = null)
    {
        if ($orderBy === null) {
            return ' ORDER BY sort_order ASC, arkey ASC';
        }

        $terms = array();
        $mapping = array(
            'id' => 'id',
            'key' => 'arkey',
            'arkey' => 'arkey',
            'value' => 'value',
            'mtime' => 'mtime',
            'order' => 'sort_order',
            'sort_order' => 'sort_order',
        );

        foreach ($orderBy as $column => $direction) {
            $column = strtolower($column);
            if (array_key_exists($column, $mapping)) {
                $condition = $mapping[$column];
                if (strcasecmp($direction, 'ASC') == 0) {
                    $condition .= ' ASC';
                } elseif (strcasecmp($direction, 'DESC') == 0) {
                    $condition .= ' DESC';
                }
            }
            $terms[] = $condition;
        }

        if (count($terms)) {
            return ' ORDER BY ' . implode(', ', $terms);
        } else {
            return '';
        }
    }

    // }}}
    // {{{ buildLimit()

    /**
     * ���R�[�h���܂Ƃ߂Ď擾����ۂ�LIMIT���OFFSET��𐶐�����
     *
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function buildLimit($limit = null, $offset = null)
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
    // {{{ exists()

    /**
     * �L�[�ɑΉ�����l���ۑ�����Ă��邩�𒲂ׂ�
     *
     * @param string $key
     * @param int $lifeTime
     * @return bool
     */
    public function exists($key, $lifeTime = -1)
    {
        $stmt = $this->_prepare(self::Q_EXSITS);
        $stmt->setFetchMode(PDO::FETCH_INTO, $this->_sharedResult);
        $stmt->bindValue(':key', $this->_codec->encodeKey($key));
        $stmt->execute();
        $row = $stmt->fetch();
        $stmt->closeCursor();

        if ($row === false) {
            return false;
        } elseif ($row->isExpired($lifeTime)) {
            $this->deleteById($row->id);
            return false;
        } else {
            return true;
        }
    }

    // }}}
    // {{{ findById()

    /**
     * ID�ɑΉ�����L�[�ƒl�̃y�A���擾����
     * ��Ƃ���P2KeyValueStore_Iterator�Ŏg��
     *
     * @param int $id
     * @param int $lifeTime
     * @return array
     */
    public function findById($id, $lifeTime = -1)
    {
        $stmt = $this->_prepare(self::Q_FINDBYID);
        $stmt->setFetchMode(PDO::FETCH_INTO, $this->_sharedResult);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        $stmt->closeCursor();

        if ($row === false) {
            return null;
        } elseif ($row->isExpired($lifeTime)) {
            $this->deleteById($id);
            return null;
        } else {
            return array(
                'key' => $this->_codec->decodeKey($row->arkey),
                'value' => $this->_codec->decodeValue($row->value),
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
    public function get($key, $lifeTime = -1)
    {
        $stmt = $this->_prepare(self::Q_GET);
        $stmt->setFetchMode(PDO::FETCH_INTO, $this->_sharedResult);
        $stmt->bindValue(':key', $this->_codec->encodeKey($key));
        $stmt->execute();
        $row = $stmt->fetch();
        $stmt->closeCursor();

        if ($row === false) {
            return null;
        } elseif ($row->isExpired($lifeTime)) {
            $this->deleteById($row->id);
            return null;
        } else {
            return $this->_codec->decodeValue($row->value);
        }
    }

    // }}}
    // {{{ getRaw()

    /**
     * �L�[�ɑΉ����錋�ʃZ�b�g�I�u�W�F�N�g���擾����
     *
     * @param string $key
     * @return P2KeyValueStore_Result
     */
    public function getRaw($key)
    {
        $stmt = $this->_prepare(self::Q_GET);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'P2KeyValueStore_Result');
        $stmt->bindValue(':key', $this->_codec->encodeKey($key));
        $stmt->execute();
        $row = $stmt->fetch();
        $stmt->closeCursor();

        if ($row === false) {
            return null;
        } else {
            return $row;
        }
    }

    // }}}
    // {{{ getAll()

    /**
     * �S�Ẵ��R�[�h��A�z�z��Ƃ��ĕԂ�
     * �L�������؂�̃��R�[�h�����O�������ꍇ�͎��O��gc()���Ă�������
     *
     * @param string $prefix
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @param bool $getRaw
     * @return array
     */
    public function getAll($prefix = null,
                           array $orderBy = null,
                           $limit = null,
                           $offset = null,
                           $getRaw = false)
    {
        $query = self::Q_GETALL;
        if ($prefix !== null) {
            $query .= ' WHERE ' . self::C_KEY_BEGINS;
        }
        $query.= $this->buildOrderBy($orderBy);
        $query.= $this->buildLimit($limit, $offset);

        $stmt = $this->_prepare($query, true);
        if ($prefix !== null) {
            $this->bindValueForPrefixSearch($stmt, $prefix);
        }
        $stmt->execute();

        $values = array();
        if ($getRaw) {
            $stmt->setFetchMode(PDO::FETCH_CLASS, 'P2KeyValueStore_Result');
            while ($row = $stmt->fetch()) {
                $values[$this->_codec->decodeKey($row->arkey)] = $row;
            }
        } else {
            $stmt->setFetchMode(PDO::FETCH_INTO, $this->_sharedResult);
            while ($row = $stmt->fetch()) {
                $value = $this->_codec->decodeValue($row->value);
                $values[$this->_codec->decodeKey($row->arkey)] = $value;
            }
        }

        return $values;
    }

    // }}}
    // {{{ getIds()

    /**
     * �S�Ă�ID�̔z���Ԃ�
     * ��Ƃ���P2KeyValueStore_Iterator�Ŏg��
     * �L�������؂�̃��R�[�h�����O�������ꍇ�͎��O��gc()���Ă�������
     *
     * @param string $prefix
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getIds($prefix = null,
                           array $orderBy = null,
                           $limit = null,
                           $offset = null)
    {
        $query = self::Q_GETIDS;
        if ($prefix !== null) {
            $query .= ' WHERE ' . self::C_KEY_BEGINS;
        }
        $query.= $this->buildOrderBy($orderBy);
        $query.= $this->buildLimit($limit, $offset);

        $stmt = $this->_prepare($query, true);
        if ($prefix !== null) {
            $this->bindValueForPrefixSearch($stmt, $prefix);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    // }}}
    // {{{ getKeys()

    /**
     * �S�ẴL�[�̔z���Ԃ�
     * �L�������؂�̃��R�[�h�����O�������ꍇ�͎��O��gc()���Ă�������
     *
     * @param string $prefix
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getKeys($prefix = null,
                            array $orderBy = null,
                            $limit = null,
                            $offset = null)
    {
        $query = self::Q_GETKEYS;
        if ($prefix !== null) {
            $query .= ' WHERE ' . self::C_KEY_BEGINS;
        }
        $query.= $this->buildOrderBy($orderBy);
        $query.= $this->buildLimit($limit, $offset);

        $stmt = $this->_prepare($query, true);
        if ($prefix !== null) {
            $this->bindValueForPrefixSearch($stmt, $prefix);
        }
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_COLUMN, 0);

        $keys = array();
        while (($key = $stmt->fetch()) !== false) {
            $keys[] = $this->_codec->decodeKey($key);
        }

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
        $stmt = $this->_prepare(self::Q_SET);
        $stmt->bindValue(':key', $this->_codec->encodeKey($key));
        $stmt->bindValue(':value', $this->_codec->encodeValue($value));
        $stmt->bindValue(':order', $order, PDO::PARAM_INT);
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
        $stmt->bindValue(':key', $this->_codec->encodeKey($key));
        $stmt->bindValue(':value', $this->_codec->encodeValue($value));
        $stmt->bindValue(':order', $order, PDO::PARAM_INT);
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
        $stmt->bindValue(':key', $this->_codec->encodeKey($key));
        if ($time === null) {
            $stmt->bindValue(':mtime', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':mtime', $time, PDO::PARAM_INT);
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
        $stmt->bindValue(':key', $this->_codec->encodeKey($key));
        $stmt->bindValue(':order', $order, PDO::PARAM_INT);
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
        $stmt->bindValue(':key', $this->_codec->encodeKey($key));
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
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return $stmt->rowCount() == 1;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ clear()

    /**
     * ���ׂẴ��R�[�h�܂��̓L�[���w�肳�ꂽ�ړ����Ŏn�܂郌�R�[�h���폜����
     *
     * @param string $prefix
     * @return int
     */
    public function clear($prefix = null)
    {
        $query = self::Q_CLEAR;
        if ($prefix !== null) {
            $query .= ' WHERE ' . self::C_KEY_BEGINS;
        }

        $stmt = $this->_prepare($query, true);
        if ($prefix !== null) {
            $this->bindValueForPrefixSearch($stmt, $prefix);
        }

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
    // {{{ optimize()

    /**
     * �쐬�ς݃v���y�A�[�h�X�e�[�g�����g���N���A���AVACUUM��REINDEX�𔭍s����
     * ���̃v���Z�X�������f�[�^�x�[�X���J���Ă���Ƃ��Ɏ��s���ׂ��ł͂Ȃ�
     *
     * @param void
     * @return void
     */
    public function optimize()
    {
        self::$_stmtCache[$this->_pdoId] = array();
        $this->_pdo->exec('VACUUM');
        $this->_pdo->exec('REINDEX');
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
     * @return P2KeyValueStore_Iterator
     */
    public function getIterator()
    {
        return new P2KeyValueStore_Iterator($this);
    }

    // }}}
    // {{{ getPDO()

    /**
     * �g�p���Ă���PDO�I�u�W�F�N�g��Ԃ�
     *
     * @param void
     * @return PDO
     */
    public function getPDO()
    {
        return $this->_pdo;
    }

    // }}}
    // {{{ getCodec()

    /**
     * �g�p���Ă���Codec�I�u�W�F�N�g��Ԃ�
     *
     * @param void
     * @return P2KeyValueStore_Codec_Interface
     */
    public function getCodec()
    {
        return $this->_codec;
    }

    // }}}
    // {{{ getTableName()

    /**
     * �e�[�u������Ԃ�
     *
     * @param bool $quoted
     * @return string
     */
    public function getTableName($quoted = false)
    {
        if ($quoted) {
            return $this->_quotedTableName;
        } else {
            return $this->_tableName;
        }
    }

    // }}}
    // {{{ getRawKVS()

    /**
     * �����e�[�u���������A�L�[�E�l�̕ϊ������Ȃ�Key-Value Store��Ԃ�
     *
     * @param void
     * @return new P2KeyValueStore
     */
    public function getRawKVS()
    {
        if (array_key_exists('__raw__', self::$_codecCache)) {
            $codec = self::$_codecCache['__raw__'];
        } else {
            $className = self::CODEC_DEFAULT;
            $codec = new $className;
            self::$_codecCache['__raw__'] = $codec;
        }

        return new P2KeyValueStore($this->_pdo, $codec, $this->_tableName);
    }

    // }}}
    // {{{ loadClass()

    /**
     * �N���X���[�_�[
     *
     * @string $name
     * @return void
     */
    static public function loadClass($name)
    {
        if (strncmp($name, 'P2KeyValueStore_', 16) === 0) {
            include dirname(__FILE__) . '/' . str_replace('_', '/', $name) . '.php';
        }
    }

    // }}}
}

// }}}

spl_autoload_register('P2KeyValueStore::loadClass');

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
