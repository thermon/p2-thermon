<?php

// {{{ P2KeyValueStore_FunctionCache

/**
 * P2KeyValueStore_FunctionCache���g���֐��Ăяo���v���L�V
 *
 * �ϐ����Q�ƂŎ󂯎���ď���������֐��͂��܂����삵�Ȃ��B
 *
 * ���̃N���X��__invoke()���\�b�h���������Ă���APHP 5.3�ȍ~�ł�
 * �ϊ֐���N���[�W���̂悤�� $proxy($parameter, ...) �ƌĂяo����B
 */
class P2KeyValueStore_FunctionCache_Proxy
{
    // {{{ properties

    /**
     * P2KeyValueStore_FunctionCache�I�u�W�F�N�g
     *
     * @var P2KeyValueStore_FunctionCache
     */
    private $_cache;

    /**
     * __invoke() �ŌĂяo�����֐�
     *
     * @var callable
     */
    private $_function;

    /**
     * __invoke() �ɗ^������ꂽ�����̑O�ɕt�������p�����[�^�̃��X�g
     *
     * @var array
     */
    private $_prependedParameters;

    /**
     * __invoke() �ɗ^������ꂽ�����̌�ɕt�������p�����[�^�̃��X�g
     *
     * @var array
     */
    private $_appendedParameters;

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
     * @param P2KeyValueStore_FunctionCache $cache
     * @param callable $function
     * @throws InvalidArgumentException
     */
    public function __construct(P2KeyValueStore_FunctionCache $cache, $function)
    {
        if (!is_callable($function)) {
            throw new InvalidArgumentException('Non-callable value was given');
        }

        $this->_cache = $cache;
        if (is_string($function) && strpos($function, '::') !== false) {
            $this->_function = explode('::', $function, 2);
        } else {
            $this->_function = $function;
        }
        $this->_prependedParameters = array();
        $this->_appendedParameters = array();
        $this->_lifeTime = -1;
    }

    // }}}
    // {{{ __invoke()

    /**
     * �֐����Ăяo��
     *
     * @param mixed $parameter
     * @param mixed $...
     * @return mixed
     * @see P2KeyValueStore_FunctionCache_Proxy::invoke()
     */
    public function __invoke()
    {
        $parameters = $this->_prependedParameters;
        $arguments = func_get_args();
        foreach ($arguments as $parameter) {
            $parameters[] = $parameter;
        }
        foreach ($this->_appendedParameters as $parameter) {
            $parameters[] = $parameter;
        }

        $oldLifeTime = $this->_cache->setLifeTime($this->_lifeTime);
        $result = $this->_cache->invoke($this->_function, $parameters);
        $this->_cache->setLifeTime($oldLifeTime);

        return $result;
    }

    // }}}
    // {{{ invoke()

    /**
     * __invoke() �̃G�C���A�X
     *
     * @param mixed $parameter
     * @param mixed $...
     * @return mixed
     */
    public function invoke()
    {
        $args = func_get_args();
        if (count($args)) {
            return call_user_func_array(array($this, '__invoke'), $args);
        } else {
            return $this->__invoke();
        }
    }

    // }}}
    // {{{ setPrependedParameters()

    /**
     * �����őO�ɒǉ�����������ݒ肷��
     *
     * @param mixed $...
     * @return void
     */
    public function setPrependedParameters()
    {
        $this->_prependedParameters = func_get_args();
    }

    // }}}
    // {{{ setAppendedParameters()

    /**
     * �����Ō�ɒǉ�����������ݒ肷��
     *
     * @param mixed $...
     * @return void
     */
    public function setAppendedParameters()
    {
        $this->_appendedParameters = func_get_args();
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
