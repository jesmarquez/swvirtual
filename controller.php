<?php 
    // Permite la conexion desde cualquier origen
    header("Access-Control-Allow-Origin: *");
    // Permite la ejecucion de los metodos
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");  

    $array = explode("/", $_SERVER['REQUEST_URI']);
    // Obtener el cuerpo de la solicitud HTTP
    $bodyRequest = file_get_contents("php://input");
    //en caso de solicitar una URL con final / eliminamos esa posicion
    foreach ($array as $key => $value) {
        if(empty($value)) {
        unset($array[$key]);
        }
    }

    // creamos parametros

    if (count($array) == 4) {
        $id = $array[count($array)];
        $entity = $array[count($array) - 1];
    } else {
        $entity = $array[count($array)];
    }

     // Analiza el metodo usado actualmente de los cuatro disponibles: GET, POST, PUT, DELETE
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
        // Si la variable Id existe, solicita al modelo el elemento especifico
        if(isset($id)) {
            //$data = array("URI:" => $_SERVER['REQUEST_URI']);
            $data = $array;
        // Si no existe, solicita todos los elementos
        } else {
            print_json(400, "Bad Request", $array);
        }
    
        // Si la cantidad de elementos que trae el array de $data es igual a 0 entra en este condicional
        if(count($data)==0) {
            // Si la variable Id existe pero el array de $data no arroja resultado, significa que elemento no existe
            print_json(404, "Not Found", null);
        } else {
            // Imprime la informacion solicitada
            print_json(200, "OK", $data);
        }
        
        break;
        case 'POST':
            // Acciones del Metodo POST
            
            /* Analiza si existe la variable Id, ya que la URL solicita por POST solo puede ser de estilo
                http://localhost/api/usuario no habria por que existir un Id ya que se esta registrando un 
                nuevo elemento y el Id es autogenerado, si el Id no existe, entra en esta condicional */
            if(!isset($id)) {
            // Decodifica el cuerpo de la solicitud y lo guarda en un array de PHP
            $array = json_decode($bodyRequest, true);
        
            // Renderiza la informacion obtenida que luego sera guardada en la Base de datos
            $obj->data = renderizeData(array_keys($array), array_values($array));
        
            // Ejecuta la funcion post() que se encuentra en la clase generica
            $data = $obj->post();
        
            // Si la respuesta es correcta o es igual a true entra en este condicional
            if($data) {
            // Si la Id generada es diferente de 0 se creo el elemento y entra aqui
            if($obj->conn->insert_id != 0) {
                // Se consulta la Id autogenerada para hacer un callBack
                $data = $obj->get($obj->conn->insert_id);
        
                // Si la variable $data es igual a 0, significa que el elemento no ha sido creado como se suponia
                if(count($data)==0) {
                
                print_json(201, false, null);
                // Si la variable $data es diferente de 0, el elemento ha sido creado y manda la siguiente respuesta
                } else {
                array_pop($data);
                print_json(201, "Created", $data);
                }
                
            // Si el Id generada es igual a 0, el elemento no ha sido creado y manda la siguiente respuesta
            } else {
                print_json(201, false, null);
        
            }
            // Si la respuesta es false, se supone que el elemento no ha sido registrado, y entra en este condicional
            } else {
            print_json(201, false, null);
            }
            // En tal caso de que exista la variable Id, imprimira el mensaje del que el metodo solicitado no es correcto
            } else {
            print_json(405, "Method Not Allowed", null);
            }
            break;
        case 'PUT':
            // Acciones del Metodo PUT
            if(isset($id)) {
            // Consulta primeramente que en realidad exista un elemeto con el Id antes de modificar
            $info = $obj->get($id);
            array_pop($info);
        
            // Si la info recibida es diferente de 0, el elemento existe, por lo tanto procede a modificar 
            if(count($info)!=0) {
            $array = json_decode($bodyRequest, true);
        
            $obj->data = renderizeData(array_keys($array), array_values($array));
        
            $obj->Id = $id;
            $data = $obj->put();
        
            if($data) {
                $data = $obj->get($id);
        
                if(count($data)==0) {
                print_json(200, false, null);
                } else {
                array_pop($data);
                print_json(200, "OK", $data);
                }
        
            } else {
                print_json(200, false, null);
            }
            // Si la info recibida es igual a 0, el elemento no existe y no hay nada para modificar
            } else {
            print_json(404, "Not Found", null);
            }
            
            } else {
            print_json(405, "Method Not Allowed", null);
            }
        
            break;
        case 'DELETE':
            if(isset($id)) {
        
            $info = $obj->get($id);
        
            if(count($info)==0) {
            print_json(404, "Not Found", null);
            } else {
            $obj->Id = $id;
            $data = $obj->delete();
        
            if($data) {
                array_pop($info);
                if(count($info)==0) {
                print_json(404, "Not Found", null);
                } else {
                print_json(200, "OK", $info);
                }
                
            } else {
                print_json(200, false, null);
            }
            }
        
            } else {
            print_json(405, "Method Not Allowed", null);
            }
            break;
    
        default:
            // Acciones cuando el metodo no se permite
            // En caso de que el Metodo Solicitado no sea ninguno de los cuatro disponible, envia la siguiente respuesta
            print_json(405, "Method Not Allowed", null);
            break;
   }

   
    // Esta funcion imprime las respuesta en estilo JSON y establece los estatus de la cebeceras HTTP
    function print_json($status, $mensaje, $data) {
        header("HTTP/1.1 $status $mensaje");
        header("Content-Type: application/json; charset=UTF-8");
    
        $response['statusCode'] = $status;
        $response['statusMessage'] = $mensaje;
        $response['data'] = $data;
    
        echo json_encode($response, JSON_PRETTY_PRINT);
    }
?>