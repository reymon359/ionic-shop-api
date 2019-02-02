<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\libraries\REST_Controller;
//esta libreria REST_Controller es lo que hemos añadido en los recursos del curso de chris y esta en las librerias
class Prueba extends REST_Controller {
  //para optimizar código este va a ser el constructor que haga funciones principales como inicializar la base de datos.
  public function __construct(){
    //todo esto es para el access controll allow y tal
    header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
    header("Access-Control-Allow-Origin: *");
    parent::__construct();//esto es para ejecutar el constructor del padre
    $this->load->database();//para conectarse ala base de datos
  }

  public function index(){
    // el index es lo que se llama por defecto si no tenemos definidos otros url o parametros
    echo "Hola mundo";
  }
  public function obtener_arreglo_get($index=0){//el =0 es un valor por defecto
    if ($index>2) {
      $respuesta = array('error'=>TRUE, 'mensaje'=>'no existe el elemento con la posicion'.$index);
      $this->response(  $respuesta,REST_Controller::HTTP_BAD_REQUEST);//esto es lo que devuelvo
    }else{
      $arreglo = array("Manzana","Pera", "Piña");
      $respuesta = array('error'=>FALSE, 'fruta'=>$arreglo[$index]);
      $this->response(  $respuesta);//esto es lo que devuelvo
      // echo json_encode($arreglo[$index]);//el echo es como console.log
    }
  }
  public function obtener_producto_get($codigo){
    // $this->load->database();//para conectarse ala base de datos
    //lanzar querys a la base de datos
    $query = $this->db->query("SELECT * FROM `productos` WHERE codigo = '".$codigo. "'");
      // $query->result()
      $this->response(  $query->result());//esto es lo que devuelvo
      // echo json_encode( $query->result() );
  }
}
