<?php
class block_dispatch_frequency extends block_base
{
  
  function init()
  {
    $this->title   = 'Частота рассылки материалов';
    $this->user_frequency=0;
    $this->is_active=0;
    $this->periods="";
    
  }
 
 function get_content()
  {
    global $PAGE, $CFG, $USER, $DB;

      $PAGE->requires->js('/lib/jquery/jquery-1.11.1.min.js');
      $PAGE->requires->js('/lib/jquery/ui-1.11.1/jquery-ui.js');
//$PAGE->requires->js('/lib/json2/json2.js');
     $PAGE->requires->css('/lib/jquery/ui-1.11.1/jquery-ui.css');
     $PAGE->requires->js('/blocks/dispatch_frequency/libs/script.js');

    require_login();

    //get data about frequency for user
    $this->user_frequency=0;
    $this->is_active=0;
    $this->o_user_frequency = $DB->get_record('dispatch_frequency',array('user_id'=>$USER->id));
    if($this->o_user_frequency==false){
        $user_record = new StdClass();
        $user_record->user_id = $USER->id;
        $user_record->user_fullname = $USER->firstname." ".$USER->lastname;
        $user_record->frequency = 7;
        $user_record->is_active = 0;
        $user_record->last_dispatch_date=strtotime('2001-01-01');
        $user_record->last_dispatch_matherial_id=0;
        $user_record->last_dispatch_matherial_name="--";
        $user_record->next_dispatch_date = strtotime('+7 days');
        $user_record->next_dispatch_matherial_id = 0;
        $user_record->next_dispatch_matherial_name = "--";
        $DB->insert_record('dispatch_frequency', $user_record);
        $this->user_frequency = $user_record->frequency;
        $this->is_active = $user_record->is_active;
    }else{
        $this->user_frequency = $this->o_user_frequency->frequency;
        $this->is_active = $this->o_user_frequency->is_active;
        }
	/*
    if($this->config->periods){
        $this->periods = $this->config->periods;
    }else{
        $this->periods = '{"periodData":[{"periodName":"1 неделя","periodQuantity":"7"},{"periodName":"2 недели","periodQuantity":"14"},{"periodName":"3 недели","periodQuantity":"21"},{"periodName":"4 недели","periodQuantity":"28"},{"periodName":"1,5 месяца","periodQuantity":"42"},{"periodName":"2 месяца","periodQuantity":"56"}]}';
    }
	*/
	$this->periods = '{"periodData":[{"periodName":"1 неделя","periodQuantity":"7"},{"periodName":"2 недели","periodQuantity":"14"},{"periodName":"3 недели","periodQuantity":"21"},{"periodName":"4 недели","periodQuantity":"28"},{"periodName":"1,5 месяца","periodQuantity":"42"},{"periodName":"2 месяца","periodQuantity":"56"}]}';


    //$this->periodData = '<div>'.$this->config->periods.'</div>';
    $this->my_content = '
      <p id="slider_label">Открывать новую лекцию раз в:</p>
       <div id="my_slider"></div>

        <p>
          <input type="text" id="amount" style="border:0; color:#f6931f; font-weight:bold;" disabled/>
        </p>
        <p>
          <input type="button" id="stop_start" value="Остановить рассылку"/>
        </p>
        <input type="hidden" id="oPeriodsData" value=\''.$this->periods.'\'>
        <input type="hidden" id="user_start_frequency" value=\''.$this->user_frequency.'\'>
        <input type="hidden" id="is_active" value=\''.$this->is_active.'\'>
        <input type="hidden" id="address" value=\''.$CFG->wwwroot.'/blocks/dispatch_frequency/save_updates.php'.'\'>


      ';



    if ($this->content !== NULL) {
      return $this->content;
    }
 
    $this->content         =  new stdClass;
    //$this->content->text = $this->config->frequency;
    $this->content->text = '';
    $this->content->footer = $this->my_content;
 
    return $this->content;
  }

  function instance_allow_config() 
  {
    return true;
  }

}   // Конец класса
?>


