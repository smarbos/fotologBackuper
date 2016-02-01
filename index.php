<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('allow_url_fopen', true);
ini_set('allow_url_include', true);
error_reporting(E_ALL);
include('simple_html_dom.php');


function backupFotolog($name){
  $fotoCounter = 1;

  //INITIALIZE VARS
  $links = [];
  $clean_links = [];
  $css = [];
  $js = [];

  //CREATE DIRS
  mkdir($name, 0777, true);
  mkdir($name . "/img/", 0777, true);
  mkdir($name . "/js/", 0777, true);
  mkdir($name . "/css/", 0777, true);

  //SET THE REQUESTED FOTOLOG URL
  $request = "http://www.fotolog.com/" . $name . "/";

  //START PAGE BY PAGE FROM THE MOSAIC
  for($i=0; $i<=270; $i+=30){

    //SET THE CURRENT PAGE REQUEST URL
    $current = $request . "mosaic/" . $i;

    //GET THE CONTENT OF THE CURRENT REQUEST
    $html = file_get_html($current);

    //GET ALL LINKS FROM THE CURRENT REQUEST
    foreach($html->find('li.float_left') as $e)
       foreach($e->find('a') as $element)
       array_push($links, $element->href);

    //CLEAN LINKS AND REMOVE ALL THAT HAVE PROMO_CLICK
    foreach($links as $link){
      if (strpos($link, 'promo_click') === false) {
        array_push($clean_links, $link);
      }
    }

    //ITERATE OVER ALL COLLECTED LINKS
    foreach($clean_links as $post_link){

      //SET THE CURRENT POST NAME
      $post_name = explode('/',$post_link);

      //GET CONTENT OF THE CURRENT LINK
      $post_html = file_get_html($post_link);

      //GET ALL JS
      foreach($post_html->find('head') as $e)
        foreach($e->find('script') as $element)

        //GET THE SRC OF THE ORIGINAL ELEMENT
        $original_javascript = $element->src;

        //SAVE THE ORIGINAL STYLESHEET TO DOWNLOAD IT LATER
        array_push($js, $element->src);

        //GET THE NAME OF THE CURRENT ELEMENT
        if($original_javascript){
          $original_javascript_name = explode('/',$original_javascript);
          $original_javascript_name = $original_javascript_name[4];

          //MAKE NEW STYLESHEET HREF
          $original_javascript_src = "css/" . $original_javascript_name;

          //REPLACE THE SRC WITH THE NEW ONE
          $element->src = $original_javascript_src;
        }

      //DOWNLOAD ALL JS
      foreach($js as $script){

        //CHECK IF SCRIPT IS NOT NULL
        if($script){

          //GET CURRENT JS NAME
          $script_name = explode('/',$script);
          $script_name = $script_name[4];
          flush();

          //IF IS THE FIRST TIME, DOWNLOAD JS FILES
          if($fotoCounter = 1){
            //DOWNLOAD CURRENT JS
            $ch = curl_init($script);
            $fp = fopen($name . "/js/" . $script_name, 'wb') or print("Can't create file " . $script_name . "<br>");
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
          }
        }
      }

      //GET ALL CSS
      foreach($post_html->find('head') as $e){
        foreach($e->find('link') as $element){

          //GET THE HREF OF THE ORIGINAL ELEMENT
          $original_stylesheet = $element->href;

          //SAVE THE ORIGINAL STYLESHEET TO DOWNLOAD IT LATER
          array_push($css, $original_stylesheet);

          //GET THE NAME OF THE CURRENT ELEMENT
          if($original_stylesheet){
            $original_stylesheet_name = explode('/',$original_stylesheet);
            $original_stylesheet_name = end($original_stylesheet_name);

            //MAKE NEW STYLESHEET HREF
            $new_stylesheet_href = "css/" . $original_stylesheet_name;

            //REPLACE THE SRC WITH THE NEW ONE
            $element->href = $new_stylesheet_href;
          }
        }
      }

      //DOWNLOAD ALL CSS
      foreach($css as $stylesheet){

        //IF STYLESHEET IS NOT NULL
        if($stylesheet){

          //GET CURRENT CSS NAME
          $stylesheet_name = explode('/',$stylesheet);
          $stylesheet_name = $stylesheet_name[4];

          //IF IS THE FIRST TIME, DOWNLOAD CSS FILES
          if($fotoCounter = 1){
            //DOWNLOAD CURRENT CSS
            $ch = curl_init($stylesheet);
            $fp = fopen($name . "/css/" . $stylesheet_name, 'wb') or print("Can't create file " . $stylesheet_name . "<br>");
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
          }
        }

      }

      //GET THE MAIN IMAGE OF THE POST
      foreach($post_html->find('a.wall_img_container_big') as $e)
        foreach($e->find('img') as $element)

          //GET THE SRC OF THE ORIGINAL URL
          $orignal_img_url =  $element->src;

          //GET THE FILENAME OF THE ORIGINAL IMAGE
          $img_name = explode('/',$orignal_img_url);

          //MAKE NEW IMG NAME
          $new_img_url= "img/" . $img_name[8];

          //REPLACE THE SRC
          $element->src = $new_img_url;

      //INDICATE FILE TO WRITE TO
      $file = $name . "/" . $post_name[4] . ".html";

      //OPEN THE FILE TO STORE THE CURRENT PAGE
      $handle = fopen($file, "w") or die("Can't create file " . $post_name[4] . "<br>");

      //SAVE CURRENT POST TO FILE
      file_put_contents($file, $post_html);

      //GET CURRENT IMAGE
      $ch = curl_init($orignal_img_url);

      //DOWNLOAD CURRENT IMAGE
      $fp = fopen($name . "/" . $new_img_url, 'wb') or die("Can't create file " . $name . "/" . $new_img_url . "<br>");
      curl_setopt($ch, CURLOPT_FILE, $fp);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_exec($ch);
      curl_close($ch);
      fclose($fp);

      //OUTPUT SUCCESS
      echo "<h1>" . $fotoCounter . "</h1>";
      flush();
      $fotoCounter++;
    }


  }


}

backupFotolog("dayco");
