<?php

/**
 * Esta classe transforma arrays aninhados em uma estrura
 * pronta para ser impressa na tela com seus rowspans
 * calculados corretamente.
 *
 * @author Felipe Lopes <bolzin [at] gmail [dot] com>
 * 
*/
class UsefulTableTree {
    private $header_fields;
    private $data;


    /**
     * Construtor da classe
     *
     * @param array $data Estrutura de dados representando a tabela
     * @param array $header_fields Nomes das colunas da tabela
    */
    function __construct($data, $header_fields) {
        $this->data = $data;
        $this->header_fields = $header_fields;
    }


    /**
     * Retorna o conteúdo da tabela no formato para ser impresso:
     * array em três níveis, no primeiro estão todos os TBODY. Para
     * cada TBODY estão os TR correspondentes e dentro de cada TR
     * seus TDs. Cada TD, no final, é um array ('td' => 'value') ou
     * ('td' => 'value', 'rowspan' => 'value') quando necessário.
    */
    function get_table_content() {
        $table_body = array();

        foreach ($this->data as $key => $value) {
            $table_body[] = $this->get_single_body(array($key => $value));
        }

        return $table_body;
    }


    /**
     * Retorna as colunas da tabela
    */
    function get_table_header() {
        return $this->header_fields;
    }


    /**
     * Cria a estrutura de um único TBODY
    */
    private function get_single_body($data) {
        // usado por get_rowspan para determinar o rowspan correto
        $qbody_index = 0;
        $queued_body = array();

        $this->queuefy_body($data, $queued_body);
        $this->max_level_qbody = $this->max_level($queued_body);

        $single_body = array();
        $tr_elements = array();

        foreach ($queued_body as $element) {
            foreach ($element as $key => $value) {
                if ($key == $this->max_level_qbody) {
                    $tr_elements[] = array('td' => $value);

                    $single_body[] = $tr_elements;
                    $tr_elements = array();
                }
                else {
                    $rowspan = $this->get_rowspan($queued_body, $key, $qbody_index);
                    $tr_elements[] = array('td' => $value, 'rowspan' => $rowspan);
                }

                $qbody_index++;
            }
        }

        return $single_body;
    }


    /**
     * Implementa uma busca em profundidade recursiva sobre
     * os arrays aninhados e retorna a versão transformada
     * em array de dois níveis representando os dados e seu
     * nível.
     *
     * Por exemplo:
     * queuefy_body(array(
     *   1 => array(
     *     'A' => ('a', 'b', c),
     *   )   
     * ));
     *
     * Retorna:
     *  array(
     *    array(1 => 1),
     *    array(2 => 'A'),
     *    array(3 => 'a'),
     *    array(3 => 'b'),
     *    array(3 => 'c'));
    */
    private function queuefy_body($data, &$queue, $level=1) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                array_push($queue, array($level => $key));
                $this->queuefy_body($value, $queue, $level + 1);
            }
            else {
                array_push($queue, array($level => $value));
            }
        }
    }


    /**
     * Percorre o retorno de queuefy_body e retorna o maior índice
    */
    private function max_level($qbody) {
        $max_level = 0;

        foreach ($qbody as $item) {
            foreach ($item as $key => $value) {
                if ($key > $max_level) {
                    $max_level = $key;
                }
            }
        }

        return $max_level;
    }


    /**
     * Retorna a quantidade de folhas de um nó
    */
    private function get_rowspan($qbody, $level, $index) {
        $rowspan = 0;

        for ($i = $index + 1; $i < count($qbody); $i++) {
            if (array_keys($qbody[$i])[0] == $level) {
                break;
            }
            else if (array_keys($qbody[$i])[0] == $this->max_level_qbody) {
                $rowspan++;
            }
        }
        return $rowspan;
    }
}

