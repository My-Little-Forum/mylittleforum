<?php
/**
 * creates a backup file with buffering every 500 lines
 * in order to prevent timeout or exhausting memory size
 */
class Backup
 {
  var $start_time;
  var $check_time;
  var $file;
  var $dump = '';
  var $queries = 0;
  var $max_queries = 300;
  var $errors = Array();

  function Backup()
   {
    @set_time_limit(30);
    $this->start_time = time();
    $this->check_time = $this->start_time;
   }

  function set_max_queries($max_queries=500)
   {
    $this->max_queries = $max_queries;
   }

  function set_file($file)
   {
    $this->file = $file;
   }

  function assign($data)
   {
    #$this->dump .= utf8_encode($data);
    $this->dump .= $data;
    $this->queries++;

    $now = time();
    if(($now-25) >= $this->check_time)
     {
      $this->check_time = $now;
      @set_time_limit(30);
     }

    if($this->queries >= $this->max_queries)
     {
      // buffer:
      if(!$this->save()) $buffering_failed = true;
      $this->queries = 0;
     }

    if(empty($buffering_failed))
     {
      return true;
     }
    else
     {
      return false;
     }
   }

  function save()
   {
    if($this->dump != '')
     {
      if(empty($this->file))
       {
        $this->file = 'backup_'.date("YmdHis").'.sql';
       }
      if($handle = @fopen($this->file, 'a+'))
       {
        #flock($fp, 2);
        @fwrite($handle, $this->dump);
        #flock($fp, 3);
        @fclose($handle);
        $this->dump = '';
       }
      else
       {
        $write_error = true;
       }
     }
    if(empty($write_error))
     {
      return true;
     }
    else
     {
      $this->errors[] = 'write_error';
      return false;
     }
   }
 }
?>
