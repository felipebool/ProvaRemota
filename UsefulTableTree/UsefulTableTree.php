<?php

class UsefulTableTree {
    private $header_fields;
    private $data;

    function __construct($data, $header_fields) {
        $this->data = $data;
        $this->header_fields = $header_fields;
    }


    function get_table_content() {
        $table_body = array();

        foreach ($this->data as $key => $value) {
            $table_body[] = $this->get_single_body(array($key => $value));
        }

        return $table_body;
    }


    function get_table_header() {
        return $this->header_fields;
    }


    private function get_single_body($data) {
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

