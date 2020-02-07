<?php
    $start = "https://www.indeed.com/jobs?q=medical+technologist&l=Texas";
    $sponsor = "https://www.indeed.com/jobs?q=Sponsorship+Medical+Technologist&explvl=entry_level";

    $already_crawled = array();
    $crawling = array();
    $titles = array();
    $descriptions = array();
    $count = 0;

    function get_details($url){
        global $titles;
        global $descriptions;

        $doc =  new DOMDocument();
        $doc->loadHTML(@file_get_contents($url)); 
        $title = $doc->getElementsByTagName("title");
        $title = $title->item(0)->nodeValue;
        //echo "title: ".$title."\n";
        $titles[] = $title;


        $keywords = "";
        $p = $doc->getElementsByTagName("p");
        $p = $p->item(0)->nodeValue;
        $div = $doc->getElementsByTagName("div");
        $div = $div->item(0)->nodeValue;
       
        if(strlen($li) > 0) $descriptions[] = $div;

        
        if(strlen($p) > 5 ) $description[] = $p;
        //echo "Paragraph Element: ".$p."\n";
        for($i = 0; $i < $p->length; $i++){
            $para = $p->item($i);
            if($para->getAttribute("name") == "Description"){
                $description = $para->getAttribute("content");
                
            }
            
        }
         echo $description."\n";
        return '{"title": "'.$title.'", "Description": "'.str_replace("\n", "", $p).'", "Keywords": "'.$div.'"}'; 
    }


    function follow_link ($url){
        global $already_crawled;
        global $crawling;
        global $count;

        $count = 0;

        $doc =  new DOMDocument();
        $options = array('http'=>array('method'=>"GET", 'headers'=>"User-agent: wazheBot/1.0\n"));
        $context = stream_context_create($options);
        $title = $doc->getElementsByTagName("title");
        

        $doc->loadHTML(file_get_contents($url, false, $context)); 
        $linkList = $doc->getElementsByTagName("a");
        //echo "\n\n";
        //echo "Printing the list of job description links...\n";
        foreach ($linkList as $link){
            
            $l = $link->getAttribute("href");

            if(!in_array($l, $already_crawled)){
                if(substr($l, 0, 3) == "/rc"){
                    $l = parse_url($url)["scheme"]."://".parse_url($url)["host"].$l;
                    $count = $count + 1;
                    @get_details($l);
                }
                // this will handle the multiple page links (1 , 2, 3, 4, ... Next)
                if(substr($l, 0, 5) == "/jobs"){
                    $l = parse_url($url)["scheme"]."://".parse_url($url)["host"].$l; 
                    //echo "Sites: ".$l."\n";
                    $already_crawled[] = $l;
                    $crawling[] = $l;
                    $count += 1;
                }
            }
            
        }
        array_shift($crawling);
        foreach($crawling as $sites){
            //echo "sites: ".$sites."\n";
            @follow_link($sites);
        }
    }
   
    @follow_link($sponsor);
    //print_r($already_crawled);
    // print_r($descriptions);

    
?>