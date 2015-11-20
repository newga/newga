<?php 
class CsvImporter 
{ 
    private $fp; 
    private $parse_header; 
    private $header; 
    private $delimiter; 
    private $length; 
    

    function __construct($file_name, $parse_header=true, $delimiter=",", $length=0) 
    { 
        ini_set('auto_detect_line_endings', true);
        mb_internal_encoding('UTF-8');
        $this->fp = fopen($file_name, "r"); 
        $this->parse_header = $parse_header; 
        $this->delimiter = $delimiter; 
        $this->length = $length; 
        $this->lines = $lines; 

        if ($this->parse_header) 
        { 
           $this->header = fgetcsv($this->fp, $this->length, $this->delimiter); 
        } 

    } 
    
    function __destruct() 
    { 
        if ($this->fp) 
        { 
            fclose($this->fp); 
        } 
    } 
   

    function get($max_lines=0) 
    { 
        //if $max_lines is set to 0, then get all the data 

        $data = array(); 
        $x=0;


        if ($max_lines > 0) 
            $line_count = 0; 
        else 
            $line_count = -1; // so loop limit is ignored 

        while ($line_count < $max_lines && ($row = fgetcsv($this->fp, $this->length, $this->delimiter)) !== FALSE) 
        { 
            $x++;
            if ($this->parse_header) 
            { 
                foreach ($this->header as $i => $heading_i) 
                { 
                    $row_new[$this->convertEncoding($heading_i)] = $this->convertEncoding($row[$i]); 
                } 
                $data[] = $row_new; 
            } 
            else 
            { 
                $data[] = $this->convertEncoding($row); 
            } 

            echo '<br />' . $x;

            if ($max_lines > 0) 
                $line_count++; 
       
            unset($row);
            unset($row_new);
        } 

        
        

      
        return $data; 
    }

    function convertEncoding($str)
    {
        $currentEncoding = mb_detect_encoding($str);

        if($currentEncoding == "UTF-8")
        {
            $str = $this->remove_utf8_bom($str);
        }

        return iconv( $currentEncoding, "UTF-8", trim($str) );
    } 
   
    function remove_utf8_bom($text)
    {
        $bom = pack('H*','EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        
        return $text;
    }

} 
?>