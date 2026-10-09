<?php


// Include config file
require_once "server.php";

session_start();

// Define variables and initialize with empty values
$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";

$username = $username_err = "";
$email = $email_err = "";


$_SESSION = array();

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate new password
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Please enter the new password.";
    } elseif (strlen(trim($_POST["new_password"])) < 6) {
        $new_password_err = "La contraseña al menos debe tener 6 caracteres.";
    } else {
        $new_password = trim($_POST["new_password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Por favor confirme la contraseña.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($new_password_err) && ($new_password != $confirm_password)) {
            $confirm_password_err = "Las contraseñas no coinciden.";
        }
    }

    //validate username 
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter your username.";
    }else{
        $username = trim($_POST["$username"]);
    }



    // Check input errors before updating the database
    if (empty($new_password_err) && empty($confirm_password_err)) {
        // Prepare an update statement
        $sql = "UPDATE users SET password = ? WHERE username = ?";


        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "si", $param_password, $param_id);

            // Set parameters
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
            $param_id = "SELECT * FROM users where id = ? ";

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Password updated successfully. Destroy the session, and redirect to login page

                session_destroy();

                header("location: login.php");
            } else {
                echo "Algo salió mal, por favor vuelva a intentarlo.";
            }
        }

        // Close statement
        mysqli_stmt_close($stmt);
    }

    // Close connection
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Cambio de contraseña - SB Admin</title>
        <link href="css/styles.css" rel="stylesheet" />
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
    </head>

<body class="bg-primary">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header">
                                    <h3 class="text-center font-weight-light my-4">Cambio de Contraseña</h3>
                                </div>
                                <div class="card-body">
                                    <p>Complete este formulario para restablecer su contraseña.</p>
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                        <div class="form-floating mb-3 pt-3  <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                                            <label>Ingrese su usuario</label>
                                            <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                                            <span class="help-block"><?php echo $username_err; ?></span>
                                        </div>    
                                        <div class="form-floating mb-3 pt-3  <?php echo (!empty($new_password_err)) ? 'has-error' : ''; ?>">
                                            <label>Nueva contraseña</label>
                                            <input type="password" name="new_password" class="form-control" value="<?php echo $new_password; ?>">
                                            <span class="help-block"><?php echo $new_password_err; ?></span>
                                        </div>
                                        <div class="form-floating mb-3  pt-3<?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                                            <label>Confirmar contraseña</label>
                                            <input type="password" name="confirm_password" class="form-control">
                                            <span class="help-block"><?php echo $confirm_password_err; ?></span>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="submit" class="btn btn-primary" value="Enviar">
                                            <a class="btn btn-link" href="index.php">Cancelar</a>
                                        </div>
                                    </form>
                                </div>

                            </div>
            </main>

        </div>

        <div id="layoutAuthentication_footer">
            <?php include 'footer.php'; ?>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>

    </div>
</body>

</html>