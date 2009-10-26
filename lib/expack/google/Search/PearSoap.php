<?php
require_once dirname(__FILE__) . '/Common.php';
require_once 'SOAP/Client.php';
require_once 'SOAP/WSDL.php';

// {{{ Google_Search_PearSoap

class Google_Search_PearSoap extends Google_Search_Common
{
    // {{{ init()

    /**
     * SOAP�N���C�A���g�̃C���X�^���X�𐶐�����
     *
     * @param string $wsdl  Google Search WSDL�t�@�C���̃p�X
     * @param string $key   Google Web APIs �̃��C�Z���X�L�[
     * @return boolean
     */
    public function init($wsdl, $key)
    {
        if (!file_exists($wsdl)) {
            //return PEAR::raiseError('GoogleSearch.wsdl not found.');

            /* SOAP�T�[�o��URI���w�肵��SOAP_Client�N���X���g��
               @link http://www.googleduel.com/apiexample.php */
            $soapClient = new SOAP_Client('http://api.google.com/search/beta2');
        } else {
            /* SOAP_Client�N���X��WSDL���w�肷�� */
            //$soapClient = new SOAP_Client($wsdl, TRUE);

            /* SOAP_WSDL�N���X��SOAP_Client���p�������N���X�𐶐������� */
            $wsdl = new SOAP_WSDL($wsdl);
            $soapClient = $wsdl->getProxy();
        }

        $this->setConf($wsdl, $key);

        if (PEAR::isError($soapClient)) {
            return $soapClient;
        }

        $this->_soapClient = $soapClient;

        return true;
    }

    // }}}
    // {{{ doSearch()

    /**
     * ���������s����
     *
     * @param string  $q  �����L�[���[�h
     * @param integer $start  �������ʂ��擾����ʒu
     * @param integer $maxResults  �������ʂ��擾����ő吔
     * @return object ��������
     */
    public function doSearch($q, $maxResults = 10, $start = 0)
    {
        $params = $this->prepareParams($q, $maxResults, $start);
        $result = $this->_soapClient->call('doGoogleSearch', $params, $this->_options);
        return $result;
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
