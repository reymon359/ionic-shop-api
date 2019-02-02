<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\libraries\REST_Controller;
//esta libreria REST_Controller es lo que hemos añadido en los recursos del curso de chris y esta en las librerias
class Pedidos extends REST_Controller {
  //para optimizar código este va a ser el constructor que haga funciones principales como inicializar la base de datos.
  public function __construct(){
    //todo esto es para el access controll allow y tal
    header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
    header("Access-Control-Allow-Origin: *");
    parent::__construct();//esto es para ejecutar el constructor del padre
    $this->load->database();//para conectarse ala base de datos
  }
  public function realizar_orden_post($token="0", $id_usuario="0"){
    //necesito el token y el id del usuario, el 0 es lo que pongo por defecto.
    $data = $this->post();
    if ($token == "0" || $id_usuario == "0") {
      $respuesta = array(
            'error' => TRUE,
            'mensaje'=>"Token inválido y/o usuario inválido."
        );
      $this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
      return;
    }
    //si el array de items esta vavio o tiene 1 vacio, error
    if (!isset($data["items"])|| strlen($data["items"])==0) {
      $respuesta = array(
            'error' => TRUE,
            'mensaje'=>"Faltan los items en el post"
        );
      $this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
      return;
    }
    //AQUi.Todo ha ido bien tenemos items, usuario y token
    //ahora voy a crear las condiciones para hacer el query
    //el id y el token tienen que ser iguales a los del usuario
    $condiciones = array('id'=> $id_usuario,'token'=>$token);
    $this->db->where($condiciones);
    //con esto comparo los id y token con los de la tabla login
    $query = $this->db->get('login');
    //ahora compruebo si ha devuelto algo
    $existe = $query->row();
    if (!$existe) {
      $respuesta = array(
          'error' => TRUE,
          'mensaje' =>"Usuario y Token incorrectos"
        );
      $this->response($respuesta);
      return;
    }
    //usuario y token son correctos
    $this->db->reset_query();//limpiar query
    //primero creo un array de lo que quiero insertar
    $insertar = array('usuario_id' => $id_usuario);
    $this->db->insert('ordenes',$insertar);
    //lo sigueinte regresa el id de la ultima inserccion realizada siempre
    //que el campo tenga una columna autonumerica
    $orden_id =  $this->db->insert_id();

    //ahora vamos a crear el detalle de la orden
    $this->db->reset_query();//limpiar query
    //split del string de productos id
    $items = explode(',', $data['items']);
    //ahora hacemos una inserccion por cada uno
    foreach ($items as &$producto_id) {
      //el $producto_id sera cada una de las posiciones del array
      $data_insertar = array('producto_id' => $producto_id,'orden_id' => $orden_id );
      $this->db->insert('ordenes_detalle',$data_insertar); //donde quiero insertarlo y la data a insertar
    }
    $respuesta = array(
          'error' => FALSE,
          'orden_id'=> $orden_id
        );

    $this->response($respuesta);
  }
  //funcion para obtener los pedidos de un usuario
  public function obtener_pedidos_get($token ="0",$id_usuario="0"){
    //primero comprobamos el token y el id del usuario. Como en la funcion de realizar_orden_post
    if ($token == "0" || $id_usuario == "0") {
      $respuesta = array(
            'error' => TRUE,
            'mensaje'=>"Token inválido y/o usuario inválido."
        );
      $this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
      return;
    }
    $condiciones = array('id'=> $id_usuario,'token'=>$token);
    $this->db->where($condiciones);
    //con esto comparo los id y token con los de la tabla login
    $query = $this->db->get('login');
    //ahora compruebo si ha devuelto algo
    $existe = $query->row();
    if (!$existe) {
      $respuesta = array(
          'error' => TRUE,
          'mensaje' =>"Usuario y Token incorrectos"
        );
      $this->response($respuesta);
      return;
    }
    //aqui ya esta comprobado que hay token e id
    //retornar todas las ordenes del usuario
    $query = $this->db->query('SELECT * FROM `ordenes` where usuario_id = '.$id_usuario);
    $ordenes = array();//las ordenes que devolvere.
    //for each para barrer las ordenes que devuelve el query
    foreach ($query->result() as $row){
      //haremos un inner join de la tabla de ordenes detalle con la del producto para recoger toda la info del producto
      $query_detalle = $this->db->query('SELECT a.orden_id, b.* FROM `ordenes_detalle` a INNER JOIN productos b on a.producto_id = b.codigo WHERE orden_id = '.$row->id);
      //Ahora crearemos las ordenes para meterlas en el anterior array de ordenes
      $orden = array(
        'id' => $row->id,
        'creado_en' => $row->creado_en,
        'detalle' => $query_detalle->result()
      );
      //para insertarlo usamos el array_push(donde, el que);
      array_push($ordenes, $orden);
    }
    $respuesta = array(
      'error' => FALSE,
      'ordenes' => $ordenes
    );
    $this->response( $respuesta );
  }
  //Borrar Pedidos
  public function borrar_pedido_delete($token ="0",$id_usuario="0", $orden_id="0"){
    //primero comprobamos el token y el id del usuario. Como en lotras funciones.  ademas el orden_id
    if ($token == "0" || $id_usuario == "0" || $orden_id == "0") {
      $respuesta = array(
            'error' => TRUE,
            'mensaje'=>"Token inválido y/o usuario inválido y/o orden inválida."
        );
      $this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
      return;
    }
    $condiciones = array('id'=> $id_usuario,'token'=>$token);
    $this->db->where($condiciones);
    //con esto comparo los id y token con los de la tabla login
    $query = $this->db->get('login');
    //ahora compruebo si ha devuelto algo
    $existe = $query->row();
    if (!$existe) {
      $respuesta = array(
          'error' => TRUE,
          'mensaje' =>"Usuario y Token incorrectos"
        );
      $this->response($respuesta);
      return;
    }
    //aqui ya esta comprobado que hay token e id e orden_id
    //verificar si la orden es de ese usuario y ejecutar el query
    $this->db->reset_query();
    $condiciones = array('id' => $orden_id, 'usuario_id' => $id_usuario );
    $this->db->where($condiciones);
    $query = $this->db->get('ordenes');
    //existe es igual a lo quedevuelve el query osea si la orden es de ese usuario
    $existe = $query->row();
    if (!$existe) {
      $respuesta = array(
          'error' => TRUE,
          'mensaje' =>"Esa orden no puede ser borrada"
        );
      $this->response($respuesta);
      return;
    }
    //todo esta bien para realizar el delete
    $condiciones = array('id' => $orden_id );
    $this->db->delete('ordenes', $condiciones);
    //ahora borramos tambien de la tabla de detalle.
    $condiciones = array('orden_id' => $orden_id );
    $this->db->delete('ordenes_detalle', $condiciones);

    $respuesta = array(
      'error' => FALSE,
      'mensaje' => 'Orden eliminada'
    );
    $this->response($respuesta);

  }
}
