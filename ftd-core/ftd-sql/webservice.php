<?php
class WebService
{

    public function isPost( )
    {
        return strtolower( $_SERVER['REQUEST_METHOD'] ) == "post";
    }

    public function isCallback( )
    {
        return isset( $_GET['_a1_'] );
    }

    public function load( )
    {
    }

    public function unload( )
    {
    }

    public function run( )
    {
        $this->load( );
        $this->unload( );
        unset( $this );
    }

    public function redirect( $url )
    {
        $this->unload( );
        unset( $this );
        header( "location: ".$url );
        exit( 0 );
    }
	
	function date_word($number){
	switch($number){
	case 1;
	return 'يناير';
	break;
	case 2;
	return 'فبراير';
	break;
	case 3;
	return 'مارس';
	break;
	case 4;
	return 'ابريل';
	break;
	case 5;
	return 'مايو';
	break;
	case 6;
	return 'يونيو';
	break;
	case 7;
	return 'يوليو';
	break;
	case 8;
	return 'اغسطس';
	break;
	case 9;
	return 'سبتمبر';
	break;
	case 10;
	return 'أكتوبر';
	break;
	case 11;
	return 'نوفمبر';
	break;
	case 12;
	return 'ديسمبر';
	break;
	}
	}
	function mdate_row($data){
	$explode_data_1 = explode(' ', $data);
	$explode_data_2 = explode('/', $explode_data_1[0]);
	$datanow_1 = date('d');
	$datanow_2 = date('d' ,strtotime('-1 days'));
    if($explode_data_2[2] == $datanow_1){
	return 'اليوم '.$explode_data_1[1];
	}
	else if($explode_data_2[2] == $datanow_2){
	return 'أمس '.$explode_data_1[1];
	}
	else{
	return $data;
	}
	}


}

?>
