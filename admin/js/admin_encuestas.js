jQuery(document).ready(function($){

    let preguntaCount = 1;

    // Agregar Otra Pregunta
    $('.btn-agregar-otra').click(function() {
        preguntaCount++;
        const newRow = `
            <tr>
                <td>
                   
                        <label  for="pregunta-${preguntaCount}">Pregunta ${preguntaCount}</label>
                        <div class="d-flex">
                            <input  type="text" class="form-control pregunta-input" id="pregunta-${preguntaCount}" name="questions[]">
                            <select name="types[]" id="opciones-select-${preguntaCount}">
                                        <option value="1">SI/NO</option>
                                        <option value="2">Rango 1-5</option>
                            </select> 
                         </div>
                        <button type="button" class="btn btn-danger btn-remove-row float-right mt-1">Eliminar</button>
                   
                </td>
            </tr>
        `;
        $('#preguntas-table tbody').append(newRow);
        $('.btn-remove-row').hide(); // Oculta todos los botones "X"
        $('.btn-remove-row').last().show(); // Muestra el botón "X" en la última fila
        
    });

    // Eliminar Pregunta
    $(document).on('click', '.btn-remove-row', function() {
        $(this).closest('tr').remove();
        $('.btn-remove-row').hide();
        $('.btn-remove-row').last().show();
        preguntaCount--;
    });

    // Eliminar Encuesta
    $(document).on('click', '.del-encuesta[data-id]', function() {
        var id= this.dataset.id;
        var url = SolicitudesAjax.url; //la url enviada desde PHP con el wp_localize_script
        //Petición ajax para eliminar.
        $.ajax({
            type: "POST",
            url: url,
            data: {
                action: "peticioneliminar", // action debe coincidir con el alias del add_action('wp_ajax_...')
                nonce: SolicitudesAjax.seguridad, //enviada desde PHP con el wp_localize_script
                id: id
            },
            success:function(){
                location.reload();
            }
        })
    });
    
    
});