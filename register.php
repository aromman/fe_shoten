<?php
    // Initialize the session
    session_start();
 
    // Si esta loggeado fuerza el deslogeo
    if(isset($_SESSION["user.loggedin"]) && $_SESSION["user.loggedin"] === true){
        // Unset all of the session variables
        $_SESSION = array();
 
        // Destroy the session.
        session_destroy();
}


// Include config file
require_once "server.php";
 
// Define variables and initialize with empty values
$username = $password = $confirm_password = "";
$email = $email_err= "";
$username_err = $password_err = $confirm_password_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } elseif(!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))){
        $username_err = "Username can only contain letters, numbers, and underscores.";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
           // Set parameters
           $param_username = trim($_POST["username"]);
           

            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "This username is already taken.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have atleast 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
    // Validate email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter a email.";     
    } elseif(strlen(trim($_POST["email"])) < 6){
        $email_err = "email must have atleast 6 characters.";
    } else{
        $email = trim($_POST["email"]);
    }        



    // Check input errors before inserting in database
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($email_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO users (username, password, email, id_rol) VALUES (?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssi", $param_username, $param_password, $param_email, $param_rol);
            
            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_email = $email;
            $param_rol = 99; // Invitado
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect to login page
                header("location: login.php");
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($link);
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
        <title>Register - SB Admin</title>
        <link href="css/styles.css" rel="stylesheet" />
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="bg-primary">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-7">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header"><h3 class="text-center font-weight-light my-4">Create Account</h3></div>
                                    <div class="card-body">
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3 mb-md-0 pt-2">
                                                        
                                                        <input class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" 
                                                               id="username" 
                                                               name="username" 
                                                               type="text" 
                                                               placeholder="Ingrese su usuario" 
                                                               value="<?php echo $username; ?>"
                                                        />
                                                        <label for="username">Usuario</label>
                                                        <span class="invalid-feedback"><?php echo $username_err; ?></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3 mb-md-0 pt-2">
                                                        <label for="email">Email</label>
                                                        <input 
                                                             class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" 
                                                             id="email" 
                                                             name="email" 
                                                             type="email" 
                                                             placeholder="Create a email"
                                                             value="<?php echo $email; ?>"
                                                        />
                                                        <span class="invalid-feedback"><?php echo $email_err; ?></span>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                            
                                            
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3 mb-md-0 pt-2">
                                                        <label for="password">Password</label>
                                                        <input 
                                                             class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" 
                                                             id="password" 
                                                             name="password" 
                                                             type="password" 
                                                             placeholder="Create a password"
                                                             value="<?php echo $password; ?>"
                                                        />
                                                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3 mb-md-0 pt-2">
                                                        <label for="confirm_password">Confirm Password</label>
                                                        <input 
                                                            class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" 
                                                            id="confirm_password" 
                                                            name="confirm_password" 
                                                            type="password" 
                                                            placeholder="Confirm password" 
                                                            value="<?php echo $confirm_password; ?>"    
                                                        />
                                                        <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-4 mb-0">
                                                <div class="form-group">
                                                    <input type="submit" class="btn btn-primary" value="Submit">
                                                    <input type="reset" class="btn btn-secondary ml-2" value="Reset">
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="card-footer text-center py-3">
                                        <div class="small"><a href="login.php">Have an account? Go to login</a></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <div id="layoutAuthentication_footer">
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; Your Website 2022</div>
                            <div>
                                <a href="#">Privacy Policy</a>
                                &middot;
                                <a href="#">Terms &amp; Conditions</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>
