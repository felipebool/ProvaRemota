<?php
class UsefulTableTree {
    public $header_fields;
    public $data;
    
    // construtor da classe, recebe o array multidimencional
    // e um array contendo os nomes das colunas da tabela
    function __construct($data, $header_fields) {
        $this->data = $data;
        $this->header_fields = $header_fields;
    }


    // get_table simplesmente chama as outras funções que
    // retornam as partes da tabela, junta em uma variável
    // e retorna a tabela construída
    function get_table() {
        $table .= $this->get_table_header();
        $table .= $this->get_table_body();
        $table .= $this->get_table_footer();

        return $table;
    }


    // get_table_header constrói o cabeçalho da tabela de
    // acordo com os nomes das colunas passados ao construtor
    private function get_table_header() {
        $table_header = "<table><thead><tr>";

        foreach ($this->header_fields as $field) {
            $table_header .= "<th>$field<th>";
        }

        $table_header .= "<tr></thead>";
        return $table_header;    
    }

    
    // get_table_body é responsável por retornar o corpo da
    // tabela, ele itera sobre o primeiro nível da estrutura
    // de dados e passa cada uma das árvores para get_single_body.
    // o valor retornado é concatenado e a função retorna o
    // corpo da tabela
    private function get_table_body() {
        $table_body = "";
        foreach ($this->data as $tree) {
            $table_body .= $this->get_single_body($tree);
        }

        return $table_body;
    }


    // get_single_body é a função auxiliar de get_table_body.
    // ela constrói uma fila com a tabela (chamando queuefy_body)
    // e itera sobre a fila retornada construindo o html de 
    // um único body. eu explico melhor o pq do uso desta função
    // na documentação do git
    private function get_single_body($data) {
        $single_body .= "<tbody>";

        $queued_body = $this->queuefy_body($data);
        $tr_element = "";

        foreach ($queued_body as $element) {
            foreach ($element as $key => $value) {
                // para mais níveis, basta testar para o
                // último nível, setado em queuefy_body
                if ($key == 'lvl3') {
                    $tr_element  .= "<td>$value</td>";
                    $single_body .= "<tr>$tr_element<tr>";
                    $tr_element = "";
                }
                else {
                   $tr_element .= "<td>$value</td>";
                }
            }
        }

        $single_body .= "<tbody>";

        return $single_body;
    }


    // get_table_footer foi criada basicamente pra suportar
    // modificações futuras, nas quais seja necessário customizar
    // o footer da tabela.
    private function get_table_footer() {
        return "</table>";
    }

    // a tarefa da queuefy_body é simples, é uma busca em profundidade
    // iterativa construindo uma fila com o valor de cada célula. na
    // documentação eu explico melhor o pq destas escolhas
    private function queuefy_body() {
        $queued_body = array();
        
        foreach ($this->data as $lvl1 => $lvl1Value) {
            array_push($queued_body, array('lvl1' => $lvl1));
            foreach ($lvl1Value as $lvl2 => $lvl2Value) {
                array_push($queued_body, array('lvl2' => $lvl2));
                foreach ($lvl2Value as $lvl3) {
                    array_push($queued_body, array('lvl3' => $lvl3));
                }
            }
        }

        return $queued_body;
    }
}

$data = array(
    'X' => array(
        '1' => array('a', 'b', 'c')
    )
);

$pretty_table = new UsefulTableTree($data, array('cl1', 'cl2', 'cl3'));
echo $pretty_table->get_table();
