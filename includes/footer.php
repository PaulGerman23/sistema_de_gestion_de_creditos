</div>
                <!-- /.container-fluid -->
                
            </div>
            <!-- End of Main Content -->
            
            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Sistema de Gestión de Créditos 2025</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->
            
        </div>
        <!-- End of Content Wrapper -->
        
    </div>
    <!-- End of Page Wrapper -->
    
    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    
    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">¿Listo para salir?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Seleccione "Cerrar Sesión" a continuación si está listo para finalizar su sesión actual.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <a class="btn btn-primary" href="<?php echo $base_url; ?>logout.php">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap core JavaScript-->
    <script src="<?php echo $base_url; ?>vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo $base_url; ?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <!-- Core plugin JavaScript-->
    <script src="<?php echo $base_url; ?>vendor/jquery-easing/jquery.easing.min.js"></script>
    
    <!-- Custom scripts for all pages-->
    <script src="<?php echo $base_url; ?>js/sb-admin-2.min.js"></script>
    
    <?php if (isset($extra_js)): ?>
        <?php echo $extra_js; ?>
    <?php endif; ?>
    
    <!-- CSS para resultados de búsqueda del navbar -->
    <style>
    #search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        max-height: 500px;
        overflow-y: auto;
        z-index: 1000;
        margin-top: 0.5rem;
    }

    .search-result-item {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e3e6f0;
        cursor: pointer;
        transition: all 0.2s;
    }

    .search-result-item:hover {
        background-color: #f8f9fc;
    }

    .search-result-item:last-child {
        border-bottom: none;
    }

    .search-no-results {
        padding: 2rem;
        text-align: center;
        color: #858796;
    }

    .search-loading {
        padding: 2rem;
        text-align: center;
    }

    .navbar-search {
        position: relative;
    }
    </style>

    <!-- JavaScript para búsqueda en navbar -->
    <script>
    $(document).ready(function() {
        var searchTimeout;
        var $navbarSearch = $('.navbar-search input[type="text"]');
        var $searchResults = null;
        
        // Crear contenedor de resultados si no existe
        if ($navbarSearch.length > 0) {
            var searchWrapper = $navbarSearch.closest('.navbar-search');
            if (searchWrapper.find('#search-results').length === 0) {
                searchWrapper.append('<div id="search-results" style="display: none;"></div>');
            }
            $searchResults = $('#search-results');
        }
        
        // Función de búsqueda
        $navbarSearch.on('keyup', function() {
            var query = $(this).val().trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length >= 2) {
                // Mostrar loading
                $searchResults.html('<div class="search-loading"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2 mb-0">Buscando...</p></div>').show();
                
                searchTimeout = setTimeout(function() {
                    $.ajax({
                        url: '<?php echo $base_url; ?>buscar_cliente_navbar.php',
                        method: 'GET',
                        data: { q: query },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                displayResults(response.clientes);
                            } else {
                                displayNoResults(response.message);
                            }
                        },
                        error: function() {
                            $searchResults.html(
                                '<div class="search-no-results">' +
                                '<i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>' +
                                '<p class="mb-0">Error al buscar. Intente nuevamente.</p>' +
                                '</div>'
                            );
                        }
                    });
                }, 300);
            } else if (query.length === 0) {
                $searchResults.hide();
            } else {
                $searchResults.html(
                    '<div class="search-no-results">' +
                    '<i class="fas fa-info-circle text-info"></i> ' +
                    'Ingrese al menos 2 caracteres' +
                    '</div>'
                ).show();
            }
        });
        
        // Mostrar resultados
        function displayResults(clientes) {
            var html = '';
            var baseUrl = '<?php echo $base_url; ?>';
            
            clientes.forEach(function(cliente) {
                html += '<div class="search-result-item" onclick="window.location.href=\'' + baseUrl + 'clientes/editar_cliente.php?id=' + cliente.id + '\'">';
                html += '<div class="d-flex justify-content-between align-items-start mb-1">';
                html += '<h6 class="mb-0"><i class="fas fa-user-circle text-primary"></i> ' + cliente.nombre + '</h6>';
                html += '<span class="badge badge-' + cliente.badge_class + ' badge-sm">' + cliente.badge_text + '</span>';
                html += '</div>';
                html += '<small class="text-muted">';
                html += '<i class="fas fa-id-card"></i> ' + cliente.dni + ' | ';
                html += '<i class="fas fa-phone"></i> ' + cliente.telefono;
                if (cliente.creditos_activos > 0) {
                    html += ' | <i class="fas fa-dollar-sign text-warning"></i> Deuda: $' + cliente.deuda_total;
                }
                html += '</small>';
                html += '<div class="mt-2">';
                html += '<a href="' + baseUrl + 'clientes/editar_cliente.php?id=' + cliente.id + '" class="btn btn-sm btn-outline-primary mr-1" onclick="event.stopPropagation()"><i class="fas fa-eye"></i> Ver</a>';
                html += '<a href="' + baseUrl + 'creditos/registrar_credito.php?id_cliente=' + cliente.id + '" class="btn btn-sm btn-outline-success mr-1" onclick="event.stopPropagation()"><i class="fas fa-plus"></i> Nuevo Crédito</a>';
                if (cliente.creditos_activos > 0) {
                    html += '<a href="' + baseUrl + 'creditos/ver_creditos.php?cliente=' + cliente.id + '" class="btn btn-sm btn-outline-warning" onclick="event.stopPropagation()"><i class="fas fa-list"></i> Ver Créditos</a>';
                }
                html += '</div>';
                html += '</div>';
            });
            
            html += '<div class="p-2 bg-light text-center border-top">';
            html += '<small class="text-muted">Mostrando ' + clientes.length + ' resultado(s)</small>';
            html += '</div>';
            
            $searchResults.html(html).show();
        }
        
        // Mostrar sin resultados
        function displayNoResults(message) {
            var baseUrl = '<?php echo $base_url; ?>';
            var html = '<div class="search-no-results">';
            html += '<i class="fas fa-search fa-2x text-muted mb-2"></i>';
            html += '<p class="mb-3">' + message + '</p>';
            html += '<a href="' + baseUrl + 'clientes/registrar_cliente.php" class="btn btn-sm btn-primary">';
            html += '<i class="fas fa-user-plus"></i> Registrar Nuevo Cliente';
            html += '</a>';
            html += '</div>';
            
            $searchResults.html(html).show();
        }
        
        // Cerrar resultados al hacer clic fuera
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.navbar-search').length) {
                if ($searchResults) {
                    $searchResults.hide();
                }
            }
        });
        
        // Prevenir que se cierre al hacer clic en los resultados
        if ($searchResults) {
            $searchResults.on('click', function(e) {
                e.stopPropagation();
            });
        }
        
        // Limpiar búsqueda con ESC
        $navbarSearch.on('keydown', function(e) {
            if (e.key === 'Escape') {
                $(this).val('');
                if ($searchResults) {
                    $searchResults.hide();
                }
            }
        });
    });
    </script>
    
</body>
</html>