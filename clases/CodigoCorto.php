<?php
class CodigoCorto {
    
    public function obtenerEncuestas($encuestaId){
        global $wpdb;
        $query = "SELECT * FROM {$wpdb->prefix}enc_encuestas WHERE EncuestaID = '$encuestaId'" ;
        $lista_encuestas = $wpdb->get_results($query,ARRAY_A);
        if(empty($lista_encuestas)){
            $lista_encuestas = array();
        }
        return $lista_encuestas[0];
    }

    public function obtenerEncuestaDetalle($encuestaId){
        global $wpdb;
        $query = "SELECT * FROM {$wpdb->prefix}enc_encuestas_detalle WHERE EncuestaID = '$encuestaId'" ;
        $lista_detalle = $wpdb->get_results($query,ARRAY_A);
        if(empty($lista_detalle)){
            $lista_detalle = array();
        }
        return $lista_detalle;
    }

    public function formOpen($titulo){
        $html = "
            <div class='wrap'>
                <h4> $titulo </h4>
                <br>
                <form method='POST'>
        ";

        return $html;
    }

    public function formClose(){
        $html = "
            <br>
            <input type='submit'  id='' name='' class='page-title-action' value='enviar'>
            </form>
        </div>
        ";

        return $html;
    }

    public function formInput($detalleID, $pregunta, $tipo){
        $html = ""; 
        if($tipo == 1){
            $html = "
            <div class='form-grup'>
                <p><b>$pregunta</b></p>
                <div class='col-sm-8'>
                    <select name='$detalleID' id='$detalleID' class='form-control'>
                        <option value='SI'>SI</option>
                        <option value='NO'>NO</option>
                    </select>
                </div>
            </div>
            ";  
        }elseif($tipo == 2){
            $html = "
            <div class='form-group'>
                <p><b>$pregunta</b></p>
                <div class='col-sm-8'>
                    <label>
                        <input type='radio' name='$detalleID' value='1'> 1
                    </label>
                    <label>
                        <input type='radio' name='$detalleID' value='2'> 2
                    </label>
                    <label>
                        <input type='radio' name='$detalleID' value='3'> 3
                    </label>
                    <label>
                        <input type='radio' name='$detalleID' value='4'> 4
                    </label>
                    <label>
                        <input type='radio' name='$detalleID' value='5'> 5
                    </label>
                </div>
            </div>
            ";
        }
        
        return $html;

    }

    public function armarForm($encuestaID){
        $encuesta = $this->obtenerEncuestas($encuestaID);
        $nombre = $encuesta['Nombre'];
        
        //Obtener las preguntas en formato para el formulario
        $preguntas = "";
        $listaPreguntas = $this->obtenerEncuestaDetalle($encuestaID);
        foreach($listaPreguntas as $key => $value){
            $detalleID = $value['DetalleID'];
            $pregunta = $value['Pregunta'];
            $tipo = $value['Tipo'];
            $encid = $value['EncuestaID'];

            if($encid == $encuestaID){
                $preguntas .= $this->formInput($detalleID, $pregunta, $tipo);
            }

        }

        $html = $this->formOpen($nombre);
        $html .= $preguntas;
        $html .= $this->formClose();

        return $html;
    }
    
}
