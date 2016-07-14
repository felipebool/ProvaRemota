<?php
/**
    * Classe UsefulTableTree
    * Esta classe é responsável por transformar uma estrutura de
    * dados em níveis (array( x=>array(y=>array(...)))) em uma
    * tabela HTML com todos os seus rowspans setados corretamente.
    * 
    * Particularidade:
    * A classe funciona para quantos níveis a estrutura de dados
    * possuir, entretanto, todos os níveis mais distantes da raiz
    * *devem* possuir pelo menos uma folha.
*/

class UsefulTableTree {
    public $header_fields;
    public $data;
    public $debug;
    
    /**
        * __construct
        * Construtor da classe.
        * @param array $data
        *   A estrutura de dados contendo a tabela a ser construída.
        * @param array $header_fields
        *   Vetor contendo os nomes das colunas da tabela
    */
    function __construct($data, $header_fields) {
        $this->data = $data;
        $this->header_fields = $header_fields;
    }


    /**
        * get_table()
        * Este método é o único método público da classe. Ele
        * chama os métodos responsáveis por construir as partes
        * intermediárias da tabela (cabeçalho, corpo e rodapé),
        * concatena seus retornos e retorna a tabela montada.
    */
    function get_table() {
        $table = "";
        $table .= $this->get_table_header();
        $table .= $this->get_table_body();
        $table .= $this->get_table_footer();

        return $table;
    }


    /**
        * get_table_header()
        * Retorna o cabeçalho da tabela construído de acordo
        * com os nomes das colunas passados para o construtor
        * e armazenados em $this->header_fields.
    */
    private function get_table_header() {
        $table_header = "<table><thead><tr>";

        foreach ($this->header_fields as $field) {
            $table_header .= "<th>$field</th>";
        }

        $table_header .= "</tr></thead>";
        return $table_header;    
    }


    /**
        * get_table_body()
        * Este método itera sobre o nível mais alto da estrutura de dados.
        * Cada um dos nós do primeiro nível é gerado isoladamente, inclusive,
        * com seu próprio <tbody>. A tarefa de gerar os <tbody> é delegada
        * para a função get_single_body().
    */
    private function get_table_body() {
        $table_body = "";
        foreach ($this->data as $key => $value) {
            $table_body .= $this->get_single_body(array($key => $value));
        }

        return $table_body;
    }


    /**
        * get_single_body()
        * Este método é responsável por gerar um <tbody> para uma árvore
        * recebida como parâmetro. Ele primeiro chama $this->queuefy_body()
        * que transforma a representação em múltiplos níveis da estrutura
        * da tabela em uma lista, depois define o nível máximo daquela
        * árvore recebida e então itera sobre a lista gerada, armazenando
        * o índice atual em $qbody_index.
        *
        * Sempre que é encontrado um item do último nível o <tr> é fechado,
        * e concatenado a $single_tbody, caso contrário é criado um <td> que
        * tem seu rowspan definido por $this->get_rowspan(), que itera sobre
        * $queued_body a partir de $qbody_index.
        *
        * @param array $data
        *   Uma subárvore com raiz no primeiro nível de $this->data
    */
    private function get_single_body($data) {
        $tr_element = "";
        $single_body = "<tbody>";
        $queued_body = array();

        $this->queuefy_body($data, $queued_body);
        $this->max_level_qbody = $this->max_level($queued_body);

        $qbody_index = 0;

        foreach ($queued_body as $element) {
            foreach ($element as $key => $value) {
                if ($key == $this->max_level_qbody) {
                    $tr_element  .= "<td>$value</td>";
                    $single_body .= "<tr>$tr_element</tr>";
                    $tr_element = "";
                }
                else {
                    $tr_element .= "<td rowspan='".
                        $this->get_rowspan($queued_body, $key, $qbody_index).
                        "'>$value</td>";
                }

                $qbody_index++;
            }
        }

        $single_body .= "</tbody>";
        return $single_body;
    }


    /**
        * queuefy_body()
        * Este método implementa uma busca em profundidade recursiva
        * sobre a estrutura de dados múltinível recebida por parâmetro
        * e gera um array de arrays onde cada elemento é um array
        * associativo no seguinte formato:
        * ('nível do nó' => 'texto do nó')
        *
        * @param array $data
        *   Uma subárvore com raiz no primeiro nível de $this->data
        * @param array &$queue
        *   Array passado por referência que será populado durante
        *   a busca pela árvore.
        * @param integer $level
        *   Utilizado para manter determinar o nível atual e é utilizado
        *   como chave para $queue 
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
        * get_table_footer()
        * Método implementado somente para alterações futuras onde
        * seja necessário um footer customizado, sua função nesta
        * classe atualmente é só fechar a tag </table>, mas poderia
        * ser utilizado para adicionar informações no rodapé da tabela.
    */
    private function get_table_footer() {
        return "</table>";
    }


    /**
        * max_level()
        * Este método retorna o nível mais alto da subárvore transformada
        * em lista por $this->queuefy_body(), ele existe para dar independência
        * de número de colunas entre as diferentes subárvores da estrutura
        * multinível.
        *
        * @param array $qbody
        *   O array gerado por $this->queuefy_body()
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
        * get_rowspan()
        * Este método retorna o rowspan correto de cada nível,
        * isto é feito contando o número de elementos do último
        * nível entre $index e a próxima ocorrência do mesmo
        * nível ($level) em $qbody, ou o final de $qbody.
        *
        * @param array $qbody
        *   O array gerado por $this->queuefy_body()
        * @param integer $level
        *   Nível do nó para o qual se está definindo o rowspan
        * @param integer $index
        *   Posição de $qbody a partir da qual começa o nível
        *   que se está definindo o rowspan.
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

