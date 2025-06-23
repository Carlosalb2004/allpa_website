function validarInput(input) {
    input.value = input.value.toUpperCase().replace(/[^A-Z0-9\-]/g, '');
}

function buscarCodigo() {
    // Obtener el valor del input
    var codigo = document.getElementById("codigo").value;

    // Leer el archivo CSV (puedes cambiar el nombre del archivo y la ubicación)
    fetch("archivosBase/PruebaCsv.csv")
        .then(response => response.text())
        .then(data => {
            // Dividir el contenido del CSV en filas
            var filas = data.split("\n");

            // Iterar a través de las filas para buscar el código
            //La fila 0 será la de las cabeceras, por ello iniciar desde la fila 1
            var encontrado = false;
            for (var i = 1; i < filas.length; i++) {
                var fila = filas[i].split(",");
                var codigoEnCSV = fila[0].trim(); // Código en primera columna

                // Si se encuentra el código, mostrarlo en el resultado y salir del bucle
                if (codigo === codigoEnCSV) {
                    //document.getElementById("resultado").innerText = "Código encontrado en el CSV: " + fila.join(", ");
                    document.getElementById("resultado").innerHTML = "<div class='card border-0 border-radius-0 bg-color-grey'><div class='card-body'><h4 class='card-title mb-1 text-4 font-weight-bold'>Datos</h4>"
                    + "<p class='card-text'><strong>Código: </strong>" +fila[0]
                    + "<br><strong>Nombre Completo: </strong>" +fila[1]
                    + "<br><strong>Cursos: </strong>" +fila[2]
                    + "<br><strong>Tipo: </strong>" +fila[3]
                    + "<br><strong>Fecha de Inicio: </strong>" +fila[4]
                    + "<br><strong>Fecha de Finalización: </strong>" +fila[5]
                    + "<br><strong>Horas: </strong>" +fila[6]
                    + "<br><strong>Convenio: </strong>" +fila[7]
                    + "<br><strong>Fecha de Certificación: </strong>" +fila[8]
                    + "<br><strong>Calificación: </strong>" +fila[9] + "</p></div></div>";
                    encontrado = true;
                    break;
                }
            }

            // Si no se encuentra el código, mostrar un mensaje de error
            if (!encontrado) {
                document.getElementById("resultado").innerText = "Código no encontrado, ingresar otro codigo.";
            }
        })
        .catch(error => {
            console.error("Error al leer el archivo CSV: " + error);
            document.getElementById("resultado").innerText = "Error al leer el archivo CSV.";
        });
}
