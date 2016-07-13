<?php
class UsefulTableTree {
    public $header_fields;
    public $data;
    public $debug;
    
    function __construct($data, $header_fields) {
        $this->data = $data;
        $this->header_fields = $header_fields;
    }

    function get_table() {
        $table = "";
        $table .= $this->get_table_header();
        $table .= $this->get_table_body();
        $table .= $this->get_table_footer();

        return $table;
    }

    private function get_table_header() {
        $table_header = "<table><thead><tr>";

        foreach ($this->header_fields as $field) {
            $table_header .= "<th>$field</th>";
        }

        $table_header .= "</tr></thead>";
        return $table_header;    
    }

    private function get_table_body() {
        $table_body = "";
        foreach ($this->data as $key => $value) {
            $table_body .= $this->get_single_body(array($key => $value));
        }

        return $table_body;
    }

    private function get_single_body($data) {
        $tr_element = "";
        $single_body = "<tbody>";
        $queued_body = array();
        $queued_body2 = array();

        $this->queuefy_body($data, $queued_body);
        $max_level = $this->max_level($queued_body);

        foreach ($queued_body as $element) {
            foreach ($element as $key => $value) {
                if ($key == $max_level) {
                    $tr_element  .= "<td>$value</td>";
                    $single_body .= "<tr>$tr_element</tr>";
                    $tr_element = "";
                }
                else {
                   $tr_element .= "<td>$value</td>";
                }
            }
        }

        $single_body .= "</tbody>";

        unset($queued_body);
        return $single_body;
    }

    private function get_table_footer() {
        return "</table>";
    }


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

    private function max_level($tree) {
        $max_level = 0;

        foreach ($tree as $item) {
            foreach ($item as $key => $value) {
                if ($key > $max_level) {
                    $max_level = $key;
                }
            }
        }

        return $max_level;
    }
}


$data = array(
    'X' => array(
        '1' => array('a', 'b', 'c'),
        '2' => array('d', 'e', 'f'),
    ),
    'Y' => array(
        '3' => array('d', 'e', 'f'),
        '4' => array('d', 'e', 'f')
    )
);

$pretty_table = new UsefulTableTree($data, array('cl1', 'cl2', 'cl3'));
echo $pretty_table->get_table();

