<?php

Yii::import('zii.widgets.grid.CGridView');


class EGridView extends CGridView
{

    protected $rowSetKeys = array();

    public $rowIdPrefix = 'listItem_';

    public function init(){
        parent::init();

        $this->rowSetKeys = $this->dataProvider->getKeys();
    }

    /**
     * @param $row
     * ad row id property to the generated tr element
     */
    public function renderTableRow($row){
        $rowId  =$this->rowIdPrefix.$this->rowSetKeys[$row];

        if($this->rowCssClassExpression!==null)
        {
            $data=$this->dataProvider->data[$row];
            echo '<tr class="'.$this->evaluateExpression($this->rowCssClassExpression,array('row'=>$row,'data'=>$data)).'" id="'.$rowId.'">';
        }
        else if(is_array($this->rowCssClass) && ($n=count($this->rowCssClass))>0)
            echo '<tr class="'.$this->rowCssClass[$row%$n].'" id="'.$rowId.'">';
        else
            echo '<tr id="'.$rowId.'">';
        foreach($this->columns as $column)
            $column->renderDataCell($row);
        echo "</tr>\n";
    }

}
?>