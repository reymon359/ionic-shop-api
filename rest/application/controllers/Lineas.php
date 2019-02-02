<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\libraries\REST_Controller;
//esta libreria REST_Controller es lo que hemos añadido en los recursos del curso de chris y esta en las librerias
class Lineas extends REST_Controller {
  //para optimizar código este va a ser el constructor que haga funciones principales como inicializar la base de datos.
  public function __construct(){
    //todo esto es para el access controll allow y tal
    header("Access-Control-Allow-Methods:  GET");
    header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
    header("Access-Control-Allow-Origin: *");
    parent::__construct();//esto es para ejecutar el constructor del padre
    $this->load->database();//para conectarse ala base de datos
  }
  //la funcion index es si solo queremos hacer la peticion al index http://localhost/rest/index.php/lineas
  public function index_get(){
    //ir a la base de datos coger todos los registros(lineas) y mostrarlos
    $query = $this->db->query('SELECT * FROM `lineas`');
    $respuesta = array(
      'error'=>FALSE,
      'lineas'=>$query->result_array()
    );
    $this->response($respuesta);
  }
}
