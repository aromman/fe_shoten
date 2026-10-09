<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "../server.php";
require_once "../app/models/reservas.php";

$reservaPDO = new Reserva();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Calendario Reservas</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>

        <style>
            .row-striped:nth-of-type(odd){
            background-color: #efefef;
            border-left: 4px #000000 solid;
            }

            .row-striped:nth-of-type(even){
            background-color: #ffffff;
            border-left: 4px #efefef solid;
            }

            .row-striped {
                padding: 15px 0;
            } 
            
            .badge-secondary {
                color: #fff;
                background-color: #6c757d;
            }            
        </style>


    </head>
    <body class="sb-nav-fixed">

        <?php include '../topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include '../sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Calendario Reservas</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="../index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Vigentes</li>
                        </ol>

                        <div class="row">
                            <!-- calendario -->
                            <div class="col-lg-6 mb-4">
                                <div class="card shadow mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-table me-1"></i>Pendientes
                                    </div>
                                    <div class="card-body">    
                                    <?php 

                                        setlocale (LC_ALL, "es_ES");

                                        $diassemanaN = array("Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sabado");
                                        $mesesN=array(1=>"Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic");

                                        // eventos
                                        $reservas = $reservaPDO->getAllActive("fecha, hora, cliente ASC");
                                        if (!is_null($reservas)){
                                            foreach ($reservas as $rowEventos) {

                                                $fechaEvento = $rowEventos['fecha'];
                                                $horaEvento = $rowEventos['hora'];
                                                $diaEvento = $diassemanaN[date('w',strtotime($fechaEvento))];
                                                $nroDiaEvento = date('d', strtotime($fechaEvento));
                                                $mesDiaEvento = $mesesN[(int)(date('m', strtotime($fechaEvento)))];
                                                $horaEvento = date('H:i',strtotime($horaEvento));
                                                $tituloEvento = $rowEventos['cliente'] . ' - ' . $horaEvento . ' Hrs';
                                                $clave = $rowEventos['clave'];
                                                $mesas = $rowEventos['mesas'];
                                                $horas = $rowEventos['horas'];

                                                $dt = new DateTime($horaEvento);
                                                $dt->add(new DateInterval('PT'.$horas.'H'));
                                                $vence = $dt->format('H:i');
                                       
                                    ?>
                                    
                                        <div class="row row-striped">
                                            <div class="col-2 text-right">
                                                <h1 class="display-4"><span class="badge badge-secondary"><?php echo $nroDiaEvento; ?></span></h1>
                                                <h2><?php echo $mesDiaEvento;?></h2>
                                            </div>
                                            <div class="col-10">
                                                <h3 class="text-uppercase"><strong><?php echo $tituloEvento; ?></strong></h3>
                                                <ul class="list-inline">
                                                    <li class="list-inline-item"><i class="fa fa-calendar" aria-hidden="true"></i> Vence : <?php echo $vence;?> Hrs del <?php echo $diaEvento;?></li>
                                                </ul>
                                                <p><?php echo $mesas; ?> Mesa(s) durante <?php echo $horas; ?> Hora(s) </p>
                                               
                                            </div>
                                        </div>

                                    <?php
                                            }
                                        }
                                    ?>
                                    </div>
                                </div>
                            </div>


                            <!-- calendario -->
                            <div class="col-lg-6 mb-4">
                                <div class="card shadow mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-table me-1"></i>En Curso
                                    </div>
                                    <div class="card-body">
                                    <?php 

                                        setlocale (LC_ALL, "es_ES");

                                        $diassemanaN = array("Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado");
                                        $mesesN=array(1=>"Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic");

                                        // eventos
                                        $reservas = $reservaPDO->getAllInCurse("fecha desc, hora desc, cliente ASC");
                                        if (!is_null($reservas)){
                                            foreach ($reservas as $rowEventos) {

                                                $fechaEvento = $rowEventos['fecha'];
                                                $horaEvento = $rowEventos['hora'];
                                                $diaEvento = $diassemanaN[date('w',strtotime($fechaEvento))];
                                                $nroDiaEvento = date('d', strtotime($fechaEvento));
                                                $mesDiaEvento = $mesesN[(int)(date('m', strtotime($fechaEvento)))];
                                                $horaEvento = date('H:i',strtotime($horaEvento));
                                                $tituloEvento = $rowEventos['cliente'] . ' - ' . $horaEvento . ' Hrs';
                                                $clave = $rowEventos['clave'];
                                                $mesas = $rowEventos['mesas'];
                                                $horas = $rowEventos['horas'];

                                                $dt = new DateTime($horaEvento);
                                                $dt->add(new DateInterval('PT'.$horas.'H'));
                                                $vence = $dt->format('H:i');
                                        
                                    ?>
                                    
                                        <div class="row row-striped">
                                            <div class="col-2 text-right">
                                                <h1 class="display-4"><span class="badge badge-secondary"><?php echo $nroDiaEvento; ?></span></h1>
                                                <h2><?php echo $mesDiaEvento;?></h2>
                                            </div>
                                            <div class="col-10">
                                                <h3 class="text-uppercase"><strong><?php echo $tituloEvento; ?></strong></h3>
                                                <ul class="list-inline">
                                                    <li class="list-inline-item"><i class="fa fa-calendar" aria-hidden="true"></i> Vence : <?php echo $vence;?> Hrs del <?php echo $diaEvento;?></li>
                                                </ul>
                                                <p><?php echo $mesas; ?> Mesa(s) durante <?php echo $horas; ?> Hora(s) </p>
                                            </div>
                                        </div>

                                    <?php
                                            }
                                        }
                                    ?>
                                    </div>
                                </div>
                            </div>                            

                        </div>
                    </div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

    </body>
</html>