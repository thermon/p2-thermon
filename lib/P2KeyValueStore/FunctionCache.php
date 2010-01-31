<?php

// {{{ P2KeyValueStore_FunctionCache

/**
 * P2KeyValueStore���g���֐��Ăяo���L���b�V��
 *
 * �ϐ����Q�ƂŎ󂯎���ď���������֐��͂��܂����삵�Ȃ��B
 */
class P2KeyValueStore_FunctionCache
{
    // {{{ properties

    /**
     * P2KeyValueStore�I�u�W�F�N�g
     *
     * @var P2KeyValueStore
     */
    private $_kvs;

    /**
     * �L���b�V���̗L������
     *
     * @var int
     */
    private $_lifeTime;

    // }}}
    // {{{ __construct()

    /**
     * �R���X�g���N�^
     *
     * @param P2KeyValueStore $kvs
     *  �ʏ��Serializing Codec���g�����Ƃ�z�肵�Ă��邪�A�������Ԃ��֐�����
     *  ����Ȃ��Ȃ�Compressing Codec��Default Codec���g���������������ǂ��B
     * @param int $lifeTime
     */
    public function __construct(P2KeyValueStore $kvs, $lifeTime = -1)
    {
        $this->_kvs = $kvs;
        $this->_lifeTime = $lifeTime;
    }

    // }}}
    // {{{ createProxy()

    /**
     * �֐������w�肵�ČĂяo���v���L�V�I�u�W�F�N�g�𐶐�����
     *
     * P2KeyValueStore_FunctionCache_Proxy��__invoke()���\�b�h���������Ă���
     * �ϊ֐���N���[�W���̂悤�� $proxy($parameter, ...) �ƌĂяo����B
     * (PHP 5.3�ȍ~�̏ꍇ)
     *
     * @param callable $function
     * @return P2KeyValueStore_FunctionCache_Proxy
     * @throws InvalidArgumentException
     * @see P2KeyValueStore_FunctionCache_Proxy::__construct()
     */
    public function createProxy($function)
    {
        $proxy = new P2KeyValueStore_FunctionCache_Proxy($this, $function);
        $proxy->setLifeTime($this->_lifeTime);
        return $proxy;
    }

    // }}}
    // {{{ invoke()

    /**
     * �֐����Ăяo��
     *
     * �֐����ƈ������猈�肳���L�[�ɑΉ�����l��KVS�ɃL���b�V������Ă����
     * �����Ԃ��A�Ȃ���Ί֐����Ăяo���A���ʂ�KVS�ɃL���b�V������B
     *
     * @param callable $function
     * @param array $parameters
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function invoke($function, array $parameters = array())
    {
        if (!is_callable($function)) {
            throw new InvalidArgumentException('Non-callable value was given');
        }

        // �֐���
        if (is_string($function)) {
            $name = $function;
            if (strpos($function, '::') !== false) {
                $function = explode('::', $function, 2);
            }
        } elseif (is_object($function)) {
            $name = get_class($function) . '->__invoke';
        } elseif (is_object($function[0])) {
            $name = get_class($function[0]) . '->' . $function[1];
        } else {
            $name = $function[0] . '::' . $function[1];
        }

        // �L�[
        $key = strtolower($name) . '(';
        if ($n = count($parameters)) {
            $key .= $n . ':' . md5(serialize($parameters));
        } else {
            $key .= 'void';
        }
        $key .= ')';

        // �L���b�V�����擾
        $record = $this->_kvs->getRaw($key);
        if ($record && !$record->isExpired($this->_lifeTime)) {
            return $this->_kvs->getCodec()->decodeValue($record->value);
        }

        // �Ȃ���Ί֐������s
        if ($n) {
            if ($n == 1 && !is_array($function)) {
                $value = $function(reset($parameters));
            } else {
                $value = call_user_func_array($function, $parameters);
            }
        } elseif (is_array($function)) {
            $value = call_user_func($function);
        } else {
            $value = $function();
        }

        // �L���b�V���ɕۑ�
        if ($record) {
            $this->_kvs->update($key, $value);
        } else {
            $this->_kvs->set($key, $value);
        }

        return $value;
    }

    // }}}
    // {{{ setLifeTime()

    /**
     * �L���b�V���̗L�����Ԃ�ݒ肷��B
     *
     * @param int $lifeTime
     * @return int
     */
    public function setLifeTime($lifeTime = -1)
    {
        $oldLifeTime = $this->_lifeTime;
        $this->_lifeTime = $lifeTime;
        return $oldLifeTime;
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
