<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Información Familiar</title>
    <link rel="stylesheet" href="styles/styles.css">
    <!-- Include CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Include jQuery  -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Include Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

</head>

<body>
    <!-- Barra superior -->
    <header class="header">
        <div class="header-left">
            <button class="menu-btn">☰</button>
        </div>
        <div class="header-right">
            <select id="search-paciente" class="search-input" style="width: 100%;" aria-label="">
            </select>
            <button class="user-btn">👤</button>
        </div>
    </header>

    <div class="container">
        <div class="sidebar">
            <div class="edit-info">
                <h2>EDITAR INFORMACION FAMILIAR</h2>
                <table>
                    <thead>
                        <tr>
                            <th>PARIENTE</th>
                            <th>PARENTESCO</th>
                            <th>ENFERMEDADES</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Los datos se llenarán dinámicamente aquí -->
                    </tbody>
                </table>
                <button class="add-btn">+</button>
            </div>
        </div>

        <div class="patient-info">
            <h2 id="patient-name"></h2>
            <p id="patient-id"></p>
            <p id="patient-birthdate"></p>
            <p id="patient-cuna"></p>
            <p id="patient-father"></p>
            <p id="patient-mother"></p>
            <div class="family-diseases">
                <p id="family-diseases"><strong>Enfermedades Familiares:</strong></p>
            </div>
            <div class="birth-info">
                <p id="birth-place"><strong>Nacido en:</strong></p>
                <p id="address"><strong>Dirección:</strong></p>
            </div>
        </div>
    </div>
    <script>
        // Función para manejar la búsqueda
        function buscarPaciente() {
            let busqueda = document.querySelector('.search-input').value;
            let resultsContainer = document.getElementById('search-results');

            // Limpiar resultados previos
            resultsContainer.innerHTML = '';

            // Si el campo no está vacío, realizar la búsqueda
            if (busqueda.trim() !== "") {
                fetch(`api/buscar_paciente_sugerencias.php?nombre=${busqueda}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Respuesta de la API:', data); // Aquí se imprime la respuesta de la API
                        if (data.success && data.pacientes.length > 0) {
                            // Mostrar las sugerencias
                            data.pacientes.forEach(paciente => {
                                let li = document.createElement('li');
                                li.textContent = paciente.nombre;
                                li.addEventListener('click', function() {
                                    // Al hacer clic en una sugerencia, llenar los campos de información
                                    llenarInformacionPaciente(paciente);
                                    resultsContainer.innerHTML = ''; // Limpiar sugerencias
                                    resultsContainer.classList.remove('show'); // Ocultar la lista
                                });
                                resultsContainer.appendChild(li);
                            });
                            resultsContainer.classList.add('show'); // Mostrar la lista
                        } else {
                            // Si no se encuentran resultados
                            let noResults = document.createElement('li');
                            noResults.classList.add('no-results');
                            noResults.textContent = 'No se encontraron pacientes';
                            resultsContainer.appendChild(noResults);
                            resultsContainer.classList.add('show'); // Mostrar la lista
                        }
                    })
                    .catch(error => console.error('Error:', error));
            } else {
                resultsContainer.classList.remove('show'); // Ocultar la lista si no hay texto
            }
        }

        $(document).ready(function() {

            // Inicializar Select2
            $('#search-paciente').select2({
                placeholder: "Buscar paciente...",
                allowClear: true,
                ajax: {
                    url: 'api/buscar_paciente_sugerencias.php',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            nombre: params.term // Enviar el término de búsqueda
                        };
                    },
                    processResults: function(data) {
                        // Procesar los resultados devueltos
                        return {
                            results: data.pacientes.map(function(paciente) {
                                return {
                                    id: paciente.id,
                                    text: paciente.nombre
                                };
                            })
                        };
                    },
                    cache: true
                }
            });

            let pacienteId = null; // Variable global para almacenar el id del paciente

            $('#search-paciente').on('select2:select', function(e) {
                var pacienteSeleccionado = e.params.data;
                pacienteId = pacienteSeleccionado.id; // Guardamos el id del paciente
                // Ahora puedes llamar a la API para obtener los detalles del paciente
                obtenerDetallesPaciente(pacienteId);
            });

            // Función para obtener los detalles completos del paciente
            function obtenerDetallesPaciente(id) {
                fetch(`api/buscar_paciente.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Llenar los campos con la información completa
                            llenarInformacionPaciente(data.paciente);
                            llenarInformacionFamiliares(data.familiares); // Llenar la información de los familiares
                        } else {
                            alert('No se encontraron detalles para este paciente.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            // Llenar la información del paciente
            function llenarInformacionPaciente(paciente) {
                document.getElementById('patient-name').textContent = paciente.nombre;
                document.getElementById('patient-id').textContent = 'ID: ' + paciente.id;
                document.getElementById('patient-birthdate').textContent = 'Fecha de Nacimiento: ' + paciente.fecha_nacimiento;
                document.getElementById('patient-cuna').textContent = 'Cuna: ' + paciente.cuna;
                document.getElementById('patient-father').textContent = 'Padre: ' + paciente.padre;
                document.getElementById('patient-mother').textContent = 'Madre: ' + paciente.madre;
                document.getElementById('family-diseases').innerHTML = '<strong>Enfermedades Familiares:</strong> ' + paciente.enfermedades_familiares;
                document.getElementById('birth-place').textContent = 'Nacido en: ' + paciente.lugar_nacimiento;
                document.getElementById('address').textContent = 'Dirección: ' + paciente.direccion;
            }

            // Llenar la información de los familiares en la tabla
            // Llenar la información de los familiares en la tabla
            function llenarInformacionFamiliares(familiares) {
                // Limpiar la tabla antes de llenarla
                $('table tbody').empty();

                if (familiares && familiares.length > 0) {
                    familiares.forEach(function(familiar) {
                        var fila = `
                <tr>
                    <td>${familiar.nombre}</td>
                    <td>${familiar.parentesco}</td>
                    <td>${familiar.enfermedades}</td>
                    <td><button class="delete-btn" data-id="${familiar.id}">Eliminar</button></td>
                </tr>
            `;
                        $('table tbody').append(fila);
                    });

                    // Agregar el evento al botón de eliminar
                    $('table').on('click', '.delete-btn', function() {
                        var familiarId = $(this).data('id'); // Obtener el id del familiar a eliminar
                        eliminarFamiliar(familiarId, $(this).closest('tr')); // Pasar la fila a eliminar después de la respuesta
                    });

                } else {
                    $('table tbody').append('<tr><td colspan="4">No se encontraron familiares.</td></tr>');
                }
            }

            // Función para eliminar un familiar
            function eliminarFamiliar(id, fila) {
                // Confirmar la eliminación
                if (confirm('¿Estás seguro de que quieres eliminar este familiar?')) {
                    fetch(`api/eliminar_familiar.php?id=${id}`, {
                            method: 'DELETE', // Método DELETE
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Si la eliminación fue exitosa, eliminar la fila de la tabla
                                fila.remove();
                            } else {
                                alert('Error al eliminar el familiar.');
                            }
                        })
                        .catch(error => console.error('Error:', error));
                }
            }
            // Función para añadir una fila con inputs y select
            $('.add-btn').click(function() {
                var newRow = `
                    <tr>
                        <td><input type="text" class="pariente-input" placeholder="Nombre del pariente"></td>
                        <td>
                            <select class="parentesco-select">
                                <option value="1">Padre</option>
                                <option value="2">Madre</option>
                                <option value="3">Abuelo paterno</option>
                                <option value="4">Abuela paterna</option>
                                <option value="5">Abuelo materno</option>
                                <option value="6">Abuela materna</option>
                                <option value="7">Tío paterno</option>
                                <option value="8">Tía paterna</option>
                                <option value="9">Tío materno</option>
                                <option value="10">Tía materna</option>
                                <option value="11">Primo paterno</option>
                                <option value="12">Prima paterna</option>
                                <option value="13">Primo materno</option>
                                <option value="14">Prima materna</option>
                                <option value="15">Hermano</option>
                                <option value="16">Hermana</option>
                            </select>
                        </td>
                        <td><input type="text" class="enfermedad-input" placeholder="Enfermedades del pariente"></td>
                        <td><button class="save-btn">Guardar</button></td>
                    </tr>
                `;
                $('table tbody').append(newRow);
            });

            // Función para guardar un nuevo familiar
            $(document).on('click', '.save-btn', function() {
                var row = $(this).closest('tr');
                var pariente = row.find('.pariente-input').val();
                var parentesco = row.find('.parentesco-select').val();
                var enfermedades = row.find('.enfermedad-input').val();

                if (pariente && parentesco && enfermedades) {
                    fetch('api/anadir_familiar.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                pariente: pariente,
                                parentesco: parentesco,
                                enfermedades: enfermedades,
                                paciente_id: pacienteId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Familiar agregado exitosamente.');
                                // Recargar la página después de mostrar el mensaje
                                location.reload();
                            } else {
                                alert('Error al agregar el familiar.');
                            }
                        })
                        .catch(error => console.error('Error:', error));
                } else {
                    alert('Por favor, complete todos los campos.');
                }
            });

        });
    </script>
</body>

</html>