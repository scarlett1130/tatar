<?php
class bot
{
     public function __construct($url)
	 {
	      $this->ch  = curl_init();
          $this->url = $url;
     }

     public function login_to_server($url_login, $array)
	 {
          curl_setopt($this->ch, CURLOPT_URL, $this->url.$url_login);
          curl_setopt($this->ch, CURLOPT_POST, 1);
          curl_setopt($this->ch, CURLOPT_POSTFIELDS, $array);
          curl_setopt($this->ch, CURLOPT_COOKIEJAR, 'ndc/cookie.txt');
          curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
          return curl_exec($this->ch);
     }

     public function post_page($url, $array)
	 {
          curl_setopt($this->ch, CURLOPT_URL, $this->url.$url);
          curl_setopt($this->ch, CURLOPT_POST, 1);
          curl_setopt($this->ch, CURLOPT_POSTFIELDS, $array);
          return curl_exec($this->ch);
     }

     public function open_page($url)
	 {
          curl_setopt($this->ch, CURLOPT_URL, $this->url.$url);
          return curl_exec($this->ch); 
     }

     public function close()
	 {
          curl_close($this->ch);
     }

}
?>