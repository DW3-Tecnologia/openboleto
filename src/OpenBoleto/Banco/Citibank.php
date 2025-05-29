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
 * Classe boleto Citibank
 *
 * @package    OpenBoleto
 * @author     Daniel Garajau <http://github.com/kriansa>
 * @copyright  Copyright (c) 2013 Estrada Virtual (http://www.estradavirtual.com.br)
 * @license    MIT License
 * @version    1.0
 */
class Citibank extends BoletoAbstract {
    /**
     * Altera layout para o exigido pelo banco
     * @var string
     */
    protected $layout = 'citibank.phtml';

    /**
     * Localização do logotipo do banco, referente ao diretório de imagens
     * @var string
     */
    protected $logoBanco = 'citibank.png';

    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = '745';

    /**
     * Linha de local de pagamento
     * @var string
     */
    protected $localPagamento = 'PAGAVEL NA REDE BANCARIA ATE VENCIMENTO';

    /**
     * Define as carteiras disponíveis para este banco
     * @var array
     */
    protected $carteiras = ['101', '102','104', '201'];

    /**
     * Define os nomes das carteiras para exibição no boleto
     * @var array
     */
    protected $carteirasNomes = ['101' => 'Cobrança Simples ECR', '102' => 'Cobrança Simples CSR'];

    /**
     * Define o número do convênio
     * @var string
     */
    protected $convenio;

    /**
     * Define o tipo de convênio
     * @var int
     */
    protected $tipoConvenio = 10;

    /**
     * Define o código do produto
     * @var int
     */
    protected $codigoProduto;

    

    /**
     * Define o número do convênio
     *
     * @param string $convenio
     * @return string
     */
    public function setConvenio($convenio) {
        $this->convenio = $convenio;
        return $this;
    }

    /**
     * Retorna o número do convênio
     *
     * @return string
     */
    public function getConvenio() {
        return $this->convenio;
    }

    /**
     * Define o tipo de convênio
     *
     * @param int $tipoConvenio
     */
    public function setTipoConvenio($tpConv) {
        $this->tipoConvenio = $tpConv;
    }

    /**
     * Retorna o tipo de convênio
     *
     * @return int
     */
    public function getTipoConvenio() {
        return $this->tipoConvenio;
    }

    /**
     * Define o código do produto
     * 
     * @param int $codigoProduto
     */
    public function setCodigoProduto(int $codigoProduto) {
        $this->codigoProduto = $codigoProduto;
    }

    /**
     * Retorna o código do produto
     * 
     * return int
     */
    public function getCodigoProduto() {
        return $this->codigoProduto;
    }

    

    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero() {
        $sequencial = self::zeroFill($this->getSequencial(), 12);
        return $sequencial;
    }

    protected function gerarDigitoVerificadorNossoNumero() {
        $sequencial = self::zeroFill($this->getSequencial(), 12);

        if (strlen(trim($sequencial)) === 11) {
            $digitoVerificador = static::modulo11($sequencial)['digito'];
        }
        elseif (strlen(trim($sequencial)) === 12) {
            $digitoVerificador = substr($sequencial, -1);
        }

        return $digitoVerificador;
    }

    /**
     * Retorna o índice da conta cosmos
     */
    protected function getIndiceContaCosmos() {
        $indice = null;

        $conta = self::zeroFill($this->getConta(), 9);
        if(trim($conta) !== '') {
            $indice = substr($conta, 0, 1);
        }
        
        return $indice;
    }

    /**
     * Retorna a base da conta cosmos
     */
    protected function getBaseContaCosmos() {
        $base = null;

        $conta = self::zeroFill($this->getConta(), 9);
        if(trim($conta) !== '') {
            $base = substr($conta, 1, 6);
        }

        return $base;
    }

    /**
     * Retorna a sequência da conta cosmos
     */
    protected function getSequenciaContaCosmos() {
        $sequencia = null;

        $conta = self::zeroFill($this->getConta(), 9);
        if(trim($conta) !== '') {
            $sequencia = substr($conta, 7, 2);
        }

        return $sequencia;
    }

    /**
     * Retorna o dígito verificador da conta cosmos
     */
    protected function getDigitoVerificadorContaCosmos() {
        return $this->getContaDv();
    }

    /**
     * Retorna a identificação da empresa
     */
    protected function getIdentificacaoEmpresa() {
        $identificacaoEmpresa = null;

        $convenio = $this->getConvenio();
        if(trim($convenio) !== '') {
            $identificacaoEmpresa = substr($convenio, 8, 12);
        }

        return $identificacaoEmpresa;
    }

    /**
     * Método para gerar o código da posição de 20 a 44
     *
     * @return string
     * @throws \OpenBoleto\Exception
     */
    public function getCampoLivre() {
        $codigo = '';

        $codigo = $this->getCodigoProduto();
        $codigo .= substr($this->getIdentificacaoEmpresa(), -3);
        $codigo .= $this->getBaseContaCosmos();
        $codigo .= $this->getSequenciaContaCosmos();
        $codigo .= $this->getDigitoVerificadorContaCosmos();
        $codigo .= self::zeroFill($this->getSequencial(), 12);

        return $codigo;
    }

    /**
     * Retorna a linha digitável do boleto
     *
     * @return string
     */
    public function getLinhaDigitavel() {
        $return = null;

        // Parâmetros utilizados abaixo
        $nossoNumero = self::zeroFill($this->getSequencial(), 12);

        // Parte 1
        $codigoProduto = $this->getCodigoProduto();
        $portfolio = substr($this->getIdentificacaoEmpresa(), -3);
        $contaCosmos1 = substr($this->getBaseContaCosmos(), 0, 1);

        $part1 = $this->getCodigoBanco() . $this->getMoeda() . $codigoProduto . $portfolio . $contaCosmos1;
        $part1Dv = $part1 . static::modulo10($part1);
        $part1Mask = substr_replace($part1Dv, '.', 5, 0);

        // Parte 2
        $contaCosmos2 = substr($this->getBaseContaCosmos(), 1, 5);
        $nossoNumero1 = substr($nossoNumero, 0, 2);

        $part2 = $contaCosmos2 . $this->getSequenciaContaCosmos() . $this->getDigitoVerificadorContaCosmos() . $nossoNumero1;
        $part2Dv = $part2 . static::modulo10($part2);
        $part2Mask = substr_replace($part2Dv, '.', 5, 0);

        // Parte 3
        $nossoNumero2 = substr($nossoNumero, 2, 10);

        $part3 = $nossoNumero2;
        $part3Dv = $part3 . static::modulo10($nossoNumero2);
        $part3Mask = substr_replace($part3Dv, '.', 5, 0);

        // Parte 4
        $part4 = $this->getDigitoVerificador();

        // Parte 5
        $part5 = $this->getFatorVencimento() . $this->getValorZeroFill();

        // Formatação
        $return = "$part1Mask $part2Mask $part3Mask $part4 $part5";

        // "Debug"
        //var_dump($return);exit;

        return $return;
    }

    /**
     * Define variáveis da view específicas do boleto do Santander
     *
     * @return array
     */
    public function getViewVars() {
        return [
            'esconde_uso_banco' => false
        ];
    }
}
