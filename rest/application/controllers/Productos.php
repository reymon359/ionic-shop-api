<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\libraries\REST_Controller;
//esta libreria REST_Controller es lo que hemos añadido en los recursos del curso de chris y esta en las librerias
class Productos extends REST_Controller {
  //para optimizar código este va a ser el constructor que haga funciones principales como inicializar la base de datos.
  public function __construct(){
    //todo esto es para el access controll allow y tal
    header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
    header("Access-Control-Allow-Origin: *");
    parent::__construct();//esto es para ejecutar el constructor del padre
    $this->load->database();//para conectarse ala base de datos
  }
  public function todos_get($pagina = 0){
    $pagina = $pagina*10;
    $query = $this->db->query('SELECT * FROM `productos` limit '.$pagina.',10');
    $respuesta = array(
      'error'=>FALSE,
      'productos'=>$query->result_array()
    );
    $this->response($respuesta);
  }
  public function por_tipo_get($tipo=0,$pagina=0){
    if ($tipo == 0) {
      $respuesta = array(
                'error' => TRUE,
                'mensaje' => 'Falta el parámetro de tipo'
              );
      $this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
      return;
    }
    $pagina = $pagina*10;
    $query = $this->db->query('SELECT * FROM `productos` where linea_id = '.$tipo.' limit '.$pagina.',10');
    $respuesta = array(
      'error'=>FALSE,
      'productos'=>$query->result_array()
    );
    $this->response($respuesta);
  }
  public function buscar_get($termino = "no específico"){
    //Con el like buscamos por texto tal cual. En MySQL no es casesensitive
    //Los % % sirven para buscar el termino da igual lo que haya antes o despues
    $query = $this->db->query("SELECT * FROM `productos` where producto like '%".$termino."%'");
    $respuesta = array(
      'error'=>FALSE,
      'termino'=>$termino,
      'productos'=>$query->result_array()
    );
    $this->response($respuesta);
  }
}
