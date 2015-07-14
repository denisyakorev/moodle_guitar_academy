<?php


class block_dispatch_frequency_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $CFG, $DB;

        //Check db
        $rows_number = $DB->count_records('dispatch_frequency_settings');
        if ($rows_number==0){
            //create default record
            $record = new StdClass();
            $record->period_name = '1 неделя';
            $record->period_length = 7;
            $DB->insert_record('dispatch_frequency_settings', $record);
        }


        $html_edit_form = '
        <table id="editPeriods" style="width:100%;">
            <tr>
                <th>Название периода</th>
                <th>Количество дней между отправками</th>
                <th></th>
            </tr>
        </table>
    
        <input type="button" class="addPeriod" value="Добавить период отправки"></input>

        <script>
        var limit = 6;
        var numOfRows = 0;
                
        var startData = $("[name=\'config_periods\']").val();
        if(startData==""){
            startData="{\"periodData\":[]}";
        }

        periodData = JSON.parse(startData);
        //Шаблон строки таблицы
        var rowTemplateU = _.template("<tr class=\"periodRow\">"+
            "<td><input type=\"text\" id=\"period<%=index%>\" class=\"periodLabel\" value=\"<%=labelValue%>\"></input></td>"+
            "<td><input type=\"number\" id=\"numOfDays<%=index%>\" class=\"periodQuantity\" value=\"<%=quantityValue%>\"></input></td>"+
            "<td><input type=\"button\" class=\"deleteRow\" value=\"Удалить\"></input></td>"+
            "</tr>");

        
        $(document).ready(function(){
            addRows(periodData.periodData);
            addDeleteListeners();
                        
            $(".addPeriod").click(function(){
                if(numOfRows<limit){
                    
                    var rowData = [{
                        index: numOfRows,
                        labelValue:"",
                        quantityValue:0
                    }];

                    addRows(rowData);
                }               
            });

            $(".periodLabel").change(function(){
                refreshSumData();
            });

            $(".periodQuantity").change(function(){
                refreshSumData();
            });

        });

        function addDeleteListeners(){
            $(".deleteRow").off("click");
            $(".deleteRow").click(function(){
                $(this).parent().parent().remove();
                refreshSumData();
            });
        };

        function refreshSumData(){
            var result = {
                periodData:[]
            };
            var curName = "";
            var curQuantity=0;
            var curObj = {};
            for(i=1; i<=numOfRows; i++){
                curName = $("#period"+i).val();
                curQuantity = $("#numOfDays"+i).val();  
                if(curName=="" || curName==undefined || curQuantity==0 || curQuantity==undefined || curQuantity==""){
                    //alert("Не все поля заполнены");
                }else{
                    result.periodData.push({periodName:curName, periodQuantity:curQuantity});
                }
            };
            if(result.periodData!=[]){
                resultJson = JSON.stringify(result);
                $("[name=\'config_periods\']").val(resultJson);
            };
        };

        function addRows(aPeriodData){
            var curData;
            var string;
            var data;
            
            for(data in aPeriodData){
                numOfRows++;
                curData={
                    index: numOfRows,
                    labelValue:aPeriodData[data].periodName,
                    quantityValue:aPeriodData[data].periodQuantity
                }
                string = rowTemplateU(curData);

                $("#editPeriods tr:last-child").after(string);

                $("#period"+numOfRows).change(function(){
                        refreshSumData();
                    });

                $("#numOfDays"+numOfRows).change(function(){
                        refreshSumData();
                });
            }
        }

        </script>';


        $mform->addElement('hidden', 'config_periods');
        if (isset($this->block->config->periods)) {
            $mform->setDefault('config_periods', $this->block->config->periods);
        } else {
            $mform->setDefault('config_periods', "{\"periodData\":[]}");
        }
        $mform->addElement('header', 'config_header', 'Периоды отправки(не более 5)');
        $mform->addElement('html', $html_edit_form);
        
        
        
    }
}