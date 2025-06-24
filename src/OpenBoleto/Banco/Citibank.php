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
    protected $carteiras = ['101', '102','104', '112', '201'];

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
     * Define a conta cosmos
     * @var string
     */
    protected $contaCosmos;

    /**
     * Define o dígito verificador da conta cosmos
     * @var string
     */
    protected $contaCosmosDv;

    

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
     * Define a conta cosmos
     *
     * @param string $contaCosmos
     */
    public function setContaCosmos(string $contaCosmos) {
        $this->contaCosmos = $contaCosmos;
    }
    
    /**
     * Retorna a conta cosmos
     *
     * return string
     */
    public function getContaCosmos() {
        return $this->contaCosmos;
    }

    /**
     * Define o dígito verificador da conta cosmos
     *
     * @param string $contaCosmos
     */
    public function setContaCosmosDv(string $contaCosmosDv) {
        $this->contaCosmosDv = $contaCosmosDv;
    }
    
    /**
     * Retorna o dígito verificador da conta cosmos
     *
     * return string
     */
    public function getContaCosmosDv() {
        return $this->contaCosmosDv;
    }

    public function getContaCosmosCompleta() {
        return $this->getContaCosmos() . $this->getContaCosmosDv();
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

        $conta = self::zeroFill($this->getContaCosmosCompleta(), 9);
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

        $conta = self::zeroFill($this->getContaCosmosCompleta(), 9);
        if(trim($conta) !== '') {
            $base = substr($conta, 0, 6);
        }

        return $base;
    }

    /**
     * Retorna a sequência da conta cosmos
     */
    protected function getSequenciaContaCosmos() {
        $sequencia = null;

        $conta = self::zeroFill($this->getContaCosmosCompleta(), 9);
        if(trim($conta) !== '') {
            $sequencia = substr($conta, 6, 2);
        }

        return $sequencia;
    }

    /**
     * Retorna o dígito verificador da conta cosmos
     */
    protected function getDigitoVerificadorContaCosmos() {
        return $this->getContaCosmosDv();
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

        // "Debug"
        /*echo 'Parâmetros completos: <br>';
        echo '- Identificação da Empresa completa: ' . $this->getConvenio() . '<br>';
        echo '- Conta Cosmos completa: ' . $this->getContaCosmosCompleta() . '<br>';
        echo '- Nosso número completo: ' . $this->getSequencial() . '<br>';
         
        echo '<br>';
        echo 'Parâmetros conforme a tabela da linha digitável: <br>';
        echo '- Código do produto: ' . $this->getCodigoProduto() . '<br>';
        echo '- Portfólio: ' . substr($this->getIdentificacaoEmpresa(), -3) . '<br>';
        echo '- Conta Cosmos ( base ): ' . $this->getBaseContaCosmos() . '<br>';
        echo '- Conta Cosmos ( sequência ): ' . $this->getSequenciaContaCosmos() . '<br>';
        echo '- Conta Cosmos ( dígito verificador ): ' . $this->getDigitoVerificadorContaCosmos() . '<br>';
        echo '- Nosso número: ' . self::zeroFill($this->getSequencial(), 12) . '<br>';
        
        echo '<br>';
        echo 'Campo livre gerado: ' . $codigo;
        exit;*/

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

        // "Debug"
        /*echo 'Parâmetros completos: <br>';
        echo '- Identificação da Empresa completa: ' . $this->getConvenio() . '<br>';
        echo '- Conta Cosmos completa: ' . $this->getContaCosmosCompleta() . '<br>';
        echo '- Nosso número completo: ' . $nossoNumero . '<br>';

        echo '<br>';
        echo 'Parâmetros conforme a tabela da linha digitável: <br>';
        echo '- Identificação do Banco: ' . $this->getCodigoBanco() . '<br>';
        echo '- Moeda: ' . $this->getMoeda() . '<br>';
        echo '- Código do produto: ' . $codigoProduto . '<br>';
        echo '- Portfólio: ' . $portfolio . '<br>';
        echo '- Conta Cosmos ( parte 1 ): ' . $contaCosmos1 . '<br>';
        echo '- Dígito verificador da linha digitável ( parte 1 ): ' . $part1Dv . '<br>';
        echo '- Conta Cosmos ( parte 2 ): ' . $contaCosmos2 . '<br>';
        echo '- Conta Cosmos ( parte 3 ): ' . $this->getSequenciaContaCosmos() . '<br>';
        echo '- Conta Cosmos ( parte 4 ): ' . $this->getDigitoVerificadorContaCosmos() . '<br>';
        echo '- Nosso número ( parte 1 ): ' . $nossoNumero1 . '<br>';
        echo '- Dígito verificador da linha digitável ( parte 2 ): ' . $part2Dv . '<br>';
        echo '- Nosso número ( parte 2 ): ' . $nossoNumero2 . '<br>';
        echo '- Dígito verificador da linha digitável ( parte 3 ): ' . $part3Dv . '<br>';
        echo '- Dígito verificador do código de barras: ' . $this->getDigitoVerificador() . '<br>';
        echo '- Fator de vencimento: ' . $this->getFatorVencimento() . '<br>';
        echo '- Valor do título: ' . $this->getValorZeroFill() . '<br>';
        exit;*/
        
        return $return;
    }

    // > Regras: Caso seja necessário gerar um dígito verificador específico, basta descomentar o método abaixo e personalizar o conteúdo.
    /*protected function getDigitoVerificador() {

        $num = self::zeroFill($this->getCodigoBanco(), 3) . $this->getMoeda() . $this->getFatorVencimento() . $this->getValorZeroFill() . $this->getCampoLivre();

        $modulo = static::modulo11($num);
        if ($modulo['resto'] == 0 || $modulo['resto'] == 1)
            $dv = 1;
        else 
            $dv = 11 - $modulo['resto'];
        
        return $dv;

    }*/

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
