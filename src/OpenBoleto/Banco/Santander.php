<?php

/*
 * OpenBoleto - Geração de boletos bancários em PHP
 *
 * LICENSE: The MIT License (MIT)
 *
 * Copyright (C) 2013 Estrada Virtual
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the "Software"), to deal in the Software
 * without restriction, including without limitation the rights to use, copy, modify,
 * merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace OpenBoleto\Banco;

use OpenBoleto\BoletoAbstract;
use OpenBoleto\Exception;

/**
 * Classe boleto Santander
 *
 * @package    OpenBoleto
 * @author     Daniel Garajau <http://github.com/kriansa>
 * @copyright  Copyright (c) 2013 Estrada Virtual (http://www.estradavirtual.com.br)
 * @license    MIT License
 * @version    1.0
 */
class Santander extends BoletoAbstract
{
    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = '033';

    /**
     * Localização do logotipo do banco, referente ao diretório de imagens
     * @var string
     */
    protected $logoBanco = 'santander.jpg';

    protected $tipoConvenio = 10;

    /**
     * Linha de local de pagamento
     * @var string
     */
    protected $localPagamento = 'Pagar preferencialmente no Banco Santander';

    /**
     * Define as carteiras disponíveis para este banco
     * @var array
     */
    protected $carteiras = array('101', '102','104', '201');

    /**
     * Define os nomes das carteiras para exibição no boleto
     * @var array
     */
    protected $carteirasNomes = array('101' => 'Cobrança Simples ECR', '102' => 'Cobrança Simples CSR');

    /**
     * Define o valor do IOS - Seguradoras (Se 7% informar 7. Limitado a 9%) - Demais clientes usar 0 (zero)
     * @var int
     */
    protected $ios;

    protected $convenio = null;

    public function setTipoConvenio($tpConv) {
        $this->tipoConvenio = $tpConv;
    }

    public function getTipoConvenio() {
        return $this->tipoConvenio;
    }


    /**
     * Define o valor do IOS
     *
     * @param int $ios
     */
    public function setIos($ios)
    {
        $this->ios = $ios;
    }

    /**
     * Retorna o atual valor do IOS
     *
     * @return int
     */
    public function getIos()
    {
        return $this->ios;
    }

    public function setConvenio($convenio) {
        $this->convenio = $convenio;
    }
    
    public function getConvenio() {
        return $this->convenio;
    }

    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        if($this->getTamanhoSequencial() > 12) {
            $sequencial = self::zeroFill($this->getSequencialTrimado(), 13);
        } else {
            $sequencial = self::zeroFill($this->getSequencialTrimado(), 12);
        }
        // return $sequencial . '-' . $this->gerarDigitoVerificadorNossoNumero();
        return $sequencial;
    }

    protected function gerarDigitoVerificadorNossoNumero() {
        if($this->getTamanhoSequencial() > 12) {
            $sequencial = self::zeroFill($this->getSequencialTrimado(), 13);
        } else {
            $sequencial = self::zeroFill($this->getSequencialTrimado(), 12);
        }
        if($this->getTipoConvenio() == 10) {
            $digitoVerificador = null; // static::modulo10($sequencial);
        } else {
            $digitoVerificador = static::modulo11($sequencial)['digito'];
        }
        return $digitoVerificador;
    }
    
    /**
     * Método para gerar o código da posição de 20 a 44
     *
     * @return string
     * @throws \OpenBoleto\Exception
     */
    public function getCampoLivre()
    {
        if($this->getTipoConvenio() == 10) {
            return '9' . self::zeroFill(substr($this->getConvenio(), 0, 7), 7) .
                self::zeroFill($this->getSequencialTrimado(), 13) .
                self::zeroFill($this->gerarDigitoVerificadorNossoNumero(), 1) .            
                //self::zeroFill($this->getIos(), 1) .
                self::zeroFill($this->getCarteira(), 3);
        } else {
            return '9' . self::zeroFill(substr($this->getConvenio(), 0, 7), 7) .
                self::zeroFill($this->getSequencialTrimado(), 12) .
                self::zeroFill($this->gerarDigitoVerificadorNossoNumero(), 1) .            
                // self::zeroFill($this->getIos(), 1) .
                self::zeroFill($this->getCarteira(), 3);
        }
    }


    /**
     * Define variáveis da view específicas do boleto do Santander
     *
     * @return array
     */
    public function getViewVars()
    {
        return array(
            'esconde_uso_banco' => true
        );
    }
}
