<?php
// Initialize the session
session_start();
 
// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["user.loggedin"]) && $_SESSION["user.loggedin"] === true){
    header("location: index.php");
    exit;
}

// Include config file
// require_once "server.php";
require_once "app/models/canal.php";
require_once "app/models/users.php";

$canalPDO = new Canal();

// Define variables and initialize with empty values
$username = $password = $canal = "";
$username_err = $password_err = $canal_err =  $login_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    //error_log(PHP_EOL."Login : POST", 3, "my-errors.log");
 
    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    if(empty(trim($_POST["canal"]))){
        $canal_err = "Debe seleccionar un canal.";
    } else{
        $canal = trim($_POST["canal"]);

        //error_log(PHP_EOL."Login : Nombre Canal", 3, "my-errors.log");
        $canalLog = $canalPDO->getById($canal);
        $canalNombre = $canalLog['nombre'];
        $canalTipo = $canalLog['tipo'];
    }


    // Validate credentials
    if(empty($username_err) && empty($password_err)){

        $userPDO = new User();
        $rsUsuario = $userPDO->getByUserName($username);
        
        if (!is_null($rsUsuario)){

            if(password_verify($password, $rsUsuario["password"])){

                session_start();
                            
                // Store data in session variables
                $_SESSION["user.loggedin"] = true;
                $_SESSION["user.id"] = $rsUsuario["id"];
                $_SESSION["user.username"] = $username;
                $_SESSION["user.rol"] = $rsUsuario["id_rol"];
                $_SESSION["user.id_empleado"] = $rsUsuario["id_empleado"];
                $_SESSION["user.apellido"] = $rsUsuario["apellido"];
                $_SESSION["user.nombre"] = $rsUsuario["nombre"];
                $_SESSION["user.canal"] = $canal; // local
                $_SESSION["user.canal.nombre"] = $canalNombre;
                $_SESSION["user.canal.tipo"] = $canalTipo;
                // Redirect user to welcome page
                header("location: index.php");


            } else {
                $login_err = "Invalid username or password.";
            }

        } else {
            $login_err = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Login</title>
        <link href="css/styles.css" rel="stylesheet" />
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
    </head>
    <body class="bg-info">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>

                    <div class="container">

                        <div class="row justify-content-center">
                            <div class="col-lg-5">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header">
                                        <h3 class="text-center font-weight-light my-4">Login</h3>
                                        <h4 class="text-center font-weight-light my-4">Saiko Manga & Comic</h4>
                                    </div>
                                    <div class="card-body">

                                        <?php 
                                            if(!empty($login_err)){
                                                echo '<div class="alert alert-danger">' . $login_err . '</div>';
                                            }        
                                        ?>                                    
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                                            <div class="form-floating mb-3 pt-2">
                                                <label>Username</label>
                                                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                                                <span class="invalid-feedback"><?php echo $username_err; ?></span>
                                            </div>
                                            <div class="form-floating mb-3 pt-2">
                                                <label>Password</label>
                                                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                                                <span class="invalid-feedback"><?php echo $password_err; ?></span>
                                            </div>
                                            <div class="form-floating mb-3 pt-2">
                                                <label>Canal</label>
                                                <?php 
                                                    $canales = $canalPDO->getAllActive("nombre ASC");
                                                     if (sizeof($canales) == 1){
                                                        ?>
                                                        <input type="text" name="canalName" class="form-control" value="<?php echo $canales[0]['nombre']?>" readonly>
                                                        <input type="hidden" name="canal" class="form-control" value="<?php echo $canales[0]['id']?>">
                                                <?php 
                                                     } else {
                                                ?>  
                                                <select name="canal" id="canal" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                                                    <option value="">Seleccione</option>
                                                    <?php
                                                    foreach ($canales as $val) {
                                                    ?>
                                                    <option 
                                                        value="<?php echo $val['id']?>" 
                                                        data-subtext="(<?php echo $val['id']?>)"
                                                        <?php if($val['id']==$canal) echo 'selected="selected"'; ?>
                                                        >
                                                        <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                                                    </option>
                                                    <?php 
                                                        }
                                                    }    
                                                        unset($canalPDO);
                                                    ?>
                                                </select>
                                                

                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                                <a class="small" href="password.php">Forgot Password?</a>
                                                <input type="submit" class="btn btn-primary" value="Login">
                                            </div>
                                        </form>
                                    </div>
                                    <div class="card-footer text-center py-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <div id="layoutAuthentication_footer">
                <?php include 'footer.php';?>
            </div>
        </div>
    </body>
</html>
