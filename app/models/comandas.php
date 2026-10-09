<?php

require_once 'crud.php';
class Comanda extends Crud
{
  private $id;
  const TABLE='comandas';
  public  $pdo;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
  }

  public function getAllInCurse(){
    try
    {
        $sql = "select
                c.id,
                c.id_venta_header,
                c.id_estado,
                ep.estado,
                c.updateDate,
                v.unidades,
                p.titulo producto
                from comandas c, ventas v, estado_pedido ep, productos p
                where c.id_venta_header = v.id_ventas_header
                and ep.id = c.id_estado
                and c.id_estado !=5
                and v.id_producto = p.id
                order by c.updateDate desc, c.id_venta_header, p.titulo;";
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetchAll(PDO::FETCH_ASSOC);
        } else {
          return null;
        }
    }
    catch (PDOException $e){
        error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
        die($e->getMessage());
    }
  }

  /**
   * 
   */
  public function create(){
  }

  public function update(){
  }

}