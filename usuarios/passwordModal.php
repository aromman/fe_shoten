<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="abrirModalLabel">Establecer Password</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <div class="mb-3">
                        <label>Password</label>
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
                    <br>
                    <div>
                        <input type="hidden" name="accionPassword" value="password"/>
                        <input type="hidden" name="id"/>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="usuarios.php" class="btn btn-secondary ml-2">Cancelar</a>
                    </div>                                
                </form>
            </div>
        </div>
    </div>
</div>