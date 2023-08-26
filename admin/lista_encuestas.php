<?php 
    global $wpdb;

    $tabla_encuestas = "{$wpdb->prefix}enc_encuestas";
    $tabla_detalle = "{$wpdb->prefix}enc_encuestas_detalle";

    if (isset($_POST['btnGuardar'])) {
        try {
            $wpdb->query('START TRANSACTION'); // Iniciar transacción, necesarop hacer commit.
            //obtener los datos de la encuesta en la vairable POST
            $nombre = $_POST['nombre-encuesta'];
            $query = "SELECT MAX(EncuestaID) as 'maximo' FROM $tabla_encuestas"; //obtener el ultimo id agregado
            $resQuery = $wpdb->get_results($query, ARRAY_A); 
            $proximoID = $resQuery[0]['maximo'] + 1; // sumar 1 al ultimo id para obtener id de la nueva encuesta
            $shortcode = "[ENC id='$proximoID']";
            //crear arreglo para el insert con $wpdb
            $datos = [
                'EncuestaID' => null,
                'Nombre' => $nombre,
                'Shortcode' => $shortcode
            ];
            //insert de encuesta
            $respuesta = $wpdb->insert($tabla_encuestas, $datos);
            if ($respuesta) {
                $lista_preguntas = $_POST['questions'];
                $tipos_preguntas = $_POST["types"];
                $i = 0;
                //recorrer el array de preguntas
                foreach ($lista_preguntas as $key => $value) {
                    $tipo = $tipos_preguntas[$i];
                    //Armar estrucutra para el detalle de la encuesta
                    $datos_pregunta = [
                        'DetalleID' => null,
                        'EncuestaID' => $proximoID,
                        'Pregunta' => $value,
                        'Tipo' => $tipo
                    ];
                    //insertar detalle
                    $wpdb->insert($tabla_detalle, $datos_pregunta);
                    $i++;
                }

                $wpdb->query('COMMIT'); // Confirmar la transacción si todo va bien
            } else {
                throw new Exception('Error al insertar en tabla_encuestas'); // Lanzar una excepción si hay un error en el primer insert
            }
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK'); // Realizar rollback si ocurre una excepción
            echo "Ha ocurrido un error: " . $e->getMessage();
        }
    }
    //Obtener las encuentas de la base de datos
    $query = "SELECT * FROM {$wpdb->prefix}enc_encuestas";
    $lista_encuestas = $wpdb->get_results($query,ARRAY_A);
    if(empty($lista_encuestas)){
        $lista_encuestas = array();
    }
?>
<div class="wrap">
    <?php
    echo '<h1>'. get_admin_page_title().'</h1>'; //Obtener el titulo de la Página.
    ?>
    <a id="btnNuevaEncuesta" href="" class="page-title-action" data-toggle="modal" data-target="#nuevaEncuestaModal">Añadir Nueva</a>
    <br><br><br>

    <table class="wp-list-table widefat fixed striped pages">
        <thead>
            <th>Nombre de la Encuesta</th>
            <th>Shortcode</th>
            <th>Acciones</th>
        </thead>
        <tbody class="the-list">
            <?php
            //Listar las encuestas en la tabla de la página principal del plugin.
                foreach ($lista_encuestas as $key => $value){
                    $id = $value['EncuestaID'];
                    $nombre = $value['Nombre'];
                    $shortcode = $value['Shortcode'];
                    echo "<tr>
                        <td>$nombre</td>
                        <td>$shortcode</td>
                        <td>
                            <a class='button-primary'>Ver Estadísticas</a>
                            <a data-id='$id' class='button-primary del-encuesta'>Borrar</a>
                        </td>
                    </tr>";

                } 
            ?>

        </tbody>
    </table>

</div>



<!-- Modal -->
<div class="modal fade" id="nuevaEncuestaModal" tabindex="-1" role="dialog" aria-labelledby="nuevaEncuestaModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="nuevaEncuestaModalLongTitle">Añadir Nueva Encuesta</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      </div>
      <form id="encuesta-form" method="post">
            <div class="form-group mx-3 d-flex">
                <label class="mr-3" for="nombre-encuesta">Titulo</label>
                <input type="text" class="form-control" id="nombre-encuesta" name="nombre-encuesta">
            </div>
            <table class="table" id="preguntas-table">
                <tbody>
                    <tr>
                        <td>
                            <label for="pregunta-1">Pregunta 1</label>
                            <div class="d-flex">
                                <input type="text" class="form-control pregunta-input mr-2" id="question" name="questions[]">
                                <select name="types[]" id="opciones-select">
                                    <option value="1">SI/NO</option>
                                    <option value="2">Rango 1-5</option>
                                </select>   
                             </div>                         
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-agregar-otra">Agregar Pregunta</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" name="btnGuardar" id="btnGuardar">Guardar</button>
            </div>
        </form>
    </div>
  </div>
</div>