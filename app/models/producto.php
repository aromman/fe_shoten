<?php
require_once 'crud.php';
require_once "logger.php";
class Producto extends Crud
{
  public $id;
  public $precio;
  public $idEditorial;
  public $sku;
  public $titulo;
  public $stock;
  public $idSerie;
  public $idFormato;
  public $precioCosto;
  public $precioOferta;
  public $tomo;
  public $nuevo;
  public $admiteDescuento;

  public $updateDate;
  public $validado;

  const TABLE='productos';
  public $pdo;
  public $logger;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }

  public function setId ($id){
    $this->id = $id;
  }
  public function setPrecio($precio){
    $this->precio=$precio;
  }
  public function setIdEditorial($idEditorial){
    $this->idEditorial=$idEditorial;
  }
  public function setSku ($sku){
    $this->sku = $sku;
  }
  public function setTitulo ($titulo){
    $this->titulo = $titulo;
  }
  public function setStock ($stock){
    $this->stock = $stock;
  }
  public function setIdSerie ($idSerie){
    $this->idSerie = $idSerie;
  }
  public function setIdFormato ($idFormato){
    $this->idFormato = $idFormato;
  }
  public function setPrecioCosto ($precioCosto){
    $this->precioCosto = $precioCosto;
  }
  public function setAdmiteDescuento ($admiteDescuento){
    $this->admiteDescuento = $admiteDescuento;
  }

  public function getAllActive(){
    try
    {
        $stm = $this->pdo->prepare("select p.id id, p.titulo titulo, p.precio precio, e.nombre editorial, p.nuevo, p.sku, p.stock, p.precio_costo, p.precio_oferta, p.admite_descuento from productos p, editoriales e where p.id_editorial = e.id and p.stock > 0 order by 3,4");
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function getAll($orderBy = null){
    try
    {
        $stm = $this->pdo->prepare("select p.id id, p.titulo titulo, p.precio precio, e.nombre editorial, p.nuevo, p.sku, p.stock, ps.nombre serie, p.precio_costo, p.precio_oferta from productos p, editoriales e, productos_serie ps where p.id_editorial = e.id and p.id_serie = ps.id order by p.titulo");
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function getAllByCriteria($findName, $findValue){
    try
    {
        $allowedCriteria = array('id', 'sku', 'titulo', 'id_editorial', 'id_serie', 'id_formato', 'nuevo', 'validado');
        if (!in_array($findName, $allowedCriteria, true)) {
          throw new InvalidArgumentException("Criterio de busqueda invalido: ".$findName);
        }

        $stm = $this->pdo->prepare("select * from productos where ".$findName." = :findValue order by titulo");
        $stm->bindParam(':findValue', $findValue);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }



  public function getAllWithOutUbicacion(){
    try
    {
        $stm = $this->pdo->prepare("select p.id id, p.titulo titulo, p.precio precio, p.id_editorial, e.nombre editorial, p.nuevo, p.sku, p.stock, p.precio_costo, p.precio_oferta from productos p, editoriales e where p.id_editorial = e.id and p.stock > 0 
                                    and p.id not in (select ps.id_producto from productos_stock ps where ps.id_producto = p.id)
                                    order by 2");
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function getStockAvailableGroupByFormato(){
    try
    {
        $stm = $this->pdo->prepare("select 
                                  f.nombre formato,
                                  sum(p.stock) stock,
                                  sum(p.stock * p.precio) valorizado
                                  from productos p, productos_formato f
                                  where p.stock > 0
                                  and p.id_formato = f.id
                                  group by f.id");
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function getStockAvailableByFormato($idFormato){
    try
    {
        $stm = $this->pdo->prepare("select 
                                  sum(p.stock) stock,
                                  sum(p.stock * p.precio) valorizado
                                  from productos p
                                  where p.stock > 0
                                  and p.id_formato = :idFormato
                                  ");
        $stm->execute(array(':idFormato' => $idFormato));
        return $stm->fetch(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function getAllActiveByFormato($idFormato){
    try
    {
        $stm = $this->pdo->prepare("select p.id id, p.titulo titulo, p.precio precio, e.nombre editorial, p.nuevo, p.sku, p.stock, p.precio_costo, p.precio_oferta, ps.nombre serie
                                    from productos p
                                    join editoriales e on p.id_editorial = e.id
                                    join productos_serie ps on p.id_serie = ps.id
                                    where p.stock > 0
                                    and p.id_formato = :idFormato
                                    order by 3,4");
        $stm->execute(array(':idFormato' => $idFormato));
        if ($stm->rowCount() > 0) {
          return $stm->fetchAll(PDO::FETCH_ASSOC);
        } else {
          return null;
        }        
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }



  public function create(){
    try{
      $sql = "INSERT INTO ".self::TABLE." (sku, titulo, id_editorial, stock, precio, id_serie, id_formato, precio_costo, tomo, admite_descuento, nuevo) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
        $this->sku,
        $this->titulo,
        $this->idEditorial,
        $this->stock,
        $this->precio,
        $this->idSerie,
        $this->idFormato,
        $this->precioCosto,
        $this->tomo,
        isset($this->admiteDescuento) ? $this->admiteDescuento : 1,
        $this->nuevo
        ));
        return $this->pdo->lastInsertId();
  }catch(PDOException $e){
    $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
  }
}

  public function update(){
    try{

        $params = array();
        $sql = "";

        if (isset($this->sku)){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'sku = :sku';
          $params[':sku'] = $this->sku;
        }
        if (isset($this->titulo)){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'titulo = :titulo';
          $params[':titulo'] = $this->titulo;
        }
        if (isset($this->precio)){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'precio = :precio';
          $params[':precio'] = $this->precio;
        }
        if (isset($this->precioCosto)){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'precio_costo = :precioCosto';
          $params[':precioCosto'] = $this->precioCosto;
        }
        if (isset($this->precioOferta)){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'precio_oferta = :precioOferta';
          $params[':precioOferta'] = $this->precioOferta;
        }
        if (isset($this->idEditorial)){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'id_editorial = :idEditorial';
          $params[':idEditorial'] = $this->idEditorial;
        }
        if (isset($this->idSerie)){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'id_serie = :idSerie';
          $params[':idSerie'] = $this->idSerie;
        }
        if (isset($this->idFormato)){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'id_formato = :idFormato';
          $params[':idFormato'] = $this->idFormato;
        }
        if (isset($this->tomo)){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'tomo = :tomo';
          $params[':tomo'] = $this->tomo;
        }
        if (isset($this->updateDate)){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'updateDate = :updateDate';
          $params[':updateDate'] = $this->updateDate;
        }
        if (isset($this->validado)){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'validado = :validado';
          $params[':validado'] = $this->validado;
        }
        if (isset($this->stock)){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'stock = :stock';
          $params[':stock'] = $this->stock ;
        }
        if (isset($this->nuevo)){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'nuevo = :nuevo';
          $params[':nuevo'] = $this->nuevo;
        }
        if (isset($this->admiteDescuento)){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'admite_descuento = :admiteDescuento';
          $params[':admiteDescuento'] = $this->admiteDescuento;
        }

        if (!empty($sql)){

          $params[':id'] = $this->id;

          $sqlUpdate = "UPDATE productos SET ".$sql." WHERE id=:id";
          $stm=$this->pdo->prepare($sqlUpdate);
          $stm->execute($params);
        }

    }catch(PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
    
  }

  public function updateStock($lista){

    try {
      
      $this->pdo->beginTransaction();

      $stm = $this->pdo->prepare("
        UPDATE productos
        SET stock = CASE WHEN stock > ? THEN stock - ? ELSE 0 END
        WHERE (id = ?);
        ");

      foreach ($lista as $item){
        $id_producto =  $item["id"];
        $cantidadItem = $item["cantidad"];

        $stm->execute(array($cantidadItem,$cantidadItem,$id_producto));
      }
      $this->pdo->commit();

    }catch(PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
      $this->pdo->rollback();
    }
  }

  private function normalizarIds($ids){
    $normalizados = array();
    foreach ($ids as $id) {
      $id = (int) $id;
      if ($id > 0) {
        $normalizados[$id] = $id;
      }
    }

    return array_values($normalizados);
  }

  public function validarMasivo($ids, $updateDate){
    $ids = $this->normalizarIds($ids);
    if (count($ids) === 0) {
      return 0;
    }

    try {
      $placeholders = implode(',', array_fill(0, count($ids), '?'));
      $params = array_merge(array($updateDate, true), $ids);
      $stm = $this->pdo->prepare("UPDATE productos SET updateDate = ?, validado = ? WHERE id IN (".$placeholders.")");
      $stm->execute($params);

      return $stm->rowCount();
    } catch(PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
      return 0;
    }
  }

  public function marcarSinStockMasivo($ids, $updateDate){
    $ids = $this->normalizarIds($ids);
    if (count($ids) === 0) {
      return 0;
    }

    try {
      $placeholders = implode(',', array_fill(0, count($ids), '?'));
      $params = array_merge(array(0, $updateDate, true), $ids);
      $stm = $this->pdo->prepare("UPDATE productos SET stock = ?, updateDate = ?, validado = ? WHERE id IN (".$placeholders.")");
      $stm->execute($params);

      return $stm->rowCount();
    } catch(PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
      return 0;
    }
  }

  public function getById($id){
    try
    {
        $stm = $this->pdo->prepare("select p.*, e.nombre editorial
                                    from productos p, editoriales e
                                    where p.id_editorial = e.id
                                    and p.id = :id");
        $stm->execute(array(':id' => $id));
        return $stm->fetch(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        die($e->getMessage());
    }
}


  public function getAllActiveStockNotValidate($orderBy = null){
    try
    {
        $sql = "
        select 
        p.id id, 
        p.titulo titulo, 
        p.precio precio, 
        e.nombre editorial, 
        p.nuevo, 
        p.sku, 
        p.stock, 
        ps.nombre serie, 
        ps.id serieId,
        p.precio_costo, 
        p.precio_oferta, 
        pf.nombre formato 
        from productos p, editoriales e, productos_serie ps, productos_formato pf 
        where 
        p.id_editorial = e.id 
        and p.id_serie = ps.id 
        and p.id_formato = pf.id
        and p.stock > 0
        and ( 
        p.validado = 0 
        or ( p.validado = 1 and p.updateDate < CURRENT_DATE - INTERVAL 30 DAY)
        ) order by ";
        if (!is_null($orderBy)){
          $sql = $sql . $orderBy;
        } else {
          $sql = $sql . "p.updateDate, p.titulo";
        }
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function getAllStockControl($orderBy = null){
    try
    {
        $sql = "
        select
        p.id id,
        p.titulo titulo,
        p.precio precio,
        e.id editorialId,
        e.nombre editorial,
        e.id_proveedor proveedorId,
        pr.nombre proveedor,
        p.nuevo,
        p.validado,
        p.habilitado,
        p.updateDate,
        p.sku,
        p.stock,
        ps.nombre serie,
        ps.id serieId,
        p.precio_costo,
        p.precio_oferta,
        pf.id formatoId,
        pf.nombre formato
        from productos p
        join editoriales e on p.id_editorial = e.id
        left join proveedores pr on e.id_proveedor = pr.id
        join productos_serie ps on p.id_serie = ps.id
        join productos_formato pf on p.id_formato = pf.id
        order by ";
        if (!is_null($orderBy)){
          $sql = $sql . $orderBy;
        } else {
          $sql = $sql . "p.stock <= 0, p.updateDate, p.titulo";
        }
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function addStockMasive($lista){

    try {
      $isUpdated = false;
      $this->pdo->beginTransaction();
      $stm = $this->pdo->prepare("UPDATE productos SET stock = stock + ?, precio = ?, precio_costo = ? where id=?");

      foreach ($lista as $item){
        $id_producto =  $item["id"];
        $cantidadItem = $item["cantidad"];
        $precioLista = $item["precioLista"];
        $precioCosto = $item["precioCosto"];

        $stm->execute(array($cantidadItem,$precioLista,$precioCosto,$id_producto));
        $isUpdated = true;
      }
      $this->pdo->commit();
      if (!$isUpdated){
        $this->logger->log(__FILE__,"Lista vacia : No se encontraron registros a actualizar",$this->logger::CRITICAL);  
      }
    }catch(PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
      if ($this->pdo->inTransaction()) {
        $this->pdo->rollback();
      }
    }
  }

  public function getTotalCountStockNotValidate(){
    try{
        $sql = "select sum(stock) stock from productos where stock > 0 and (validado=0 or ( validado = 1 and updateDate < CURRENT_DATE - INTERVAL 30 DAY));";
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          $rs =  $stm->fetch(PDO::FETCH_ASSOC);
          return $rs['stock'];
        } else {
          return 0;
        }
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function getStockNotValidateGroupByEditorial(){
    try{
        $sql = "select e.nombre editorial, sum(p.stock) stock 
                from productos p, editoriales e 
                where p.stock > 0 
                and (p.validado=0 or ( p.validado = 1 and p.updateDate < CURRENT_DATE - INTERVAL 30 DAY))
                and p.id_editorial = e.id
                group by e.nombre;";
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetchAll(PDO::FETCH_ASSOC);
        } else {
          return null;
        }
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function getStockNotValidateGroupBySerie(){
    try{
        $sql = "select ps.nombre serie, sum(p.stock) stock 
                from productos p, productos_serie ps
                where p.stock > 0 
                and (p.validado=0 or ( p.validado = 1 and p.updateDate < CURRENT_DATE - INTERVAL 30 DAY))
                and p.id_serie = ps.id
                group by ps.nombre;";
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetchAll(PDO::FETCH_ASSOC);
        } else {
          return null;
        }
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function getStockNotValidateGroupByFormato(){
    try{
        $sql = "select pf.nombre formato, sum(p.stock) stock 
                from productos p, productos_formato pf
                where p.stock > 0 and p.validado=0 and p.id_formato = pf.id
                group by pf.nombre;";
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetchAll(PDO::FETCH_ASSOC);
        } else {
          return null;
        }
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function getAllSaleByDays($months){

    try{
        $months = max(0, (int) $months);
        $sql = "SELECT
                p.id,
                p.titulo,
                e.nombre editorial,
                MONTHNAME(v.fecha) mes,
                MONTH(v.fecha) numero,
                sum(v.unidades) cantidad
                from ventas v, productos p, editoriales e
                where v.id_producto = p.id
                and v.fecha >= (CURRENT_DATE - INTERVAL $months MONTH)
                and p.id_editorial = e.id
                and v.id_canal != 43
                GROUP BY 1,2,3,4,5
                having sum(v.unidades) > 1
                ORDER BY 1,2,3,4,5;";
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetchAll(PDO::FETCH_ASSOC);
        } else {
          return null;
        }
    }
    catch (PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }


  public function getAllOutOfStock(){
    try
    {
        $stm = $this->pdo->prepare("select p.id id, p.titulo titulo, p.precio precio, e.nombre editorial, p.nuevo, p.sku, p.stock, p.precio_costo, p.precio_oferta from productos p, editoriales e where p.id_editorial = e.id and p.stock <= 0 order by 3,4");
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function getLastStockValidateGroupByDate(){
    try{
        $sql = 'select DATE_FORMAT(p.updateDate, "%Y-%m-%d") fecha, count(p.id) cantidad
                from productos p
                where p.validado=1 and p.updateDate > CURRENT_DATE - INTERVAL 5 DAY
                group by DATE_FORMAT(p.updateDate, "%Y-%m-%d")
                order by 1 desc
                limit 1;';
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetch(PDO::FETCH_ASSOC);
        } else {
          return null;
        }
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function getKardek($id){
    try{
        $sql = "select 
                v.fecha fecha,
                'v' tipo_movimiento,
                v.unidades unidades,
                v.precio_unitario precio
                from ventas v, canal c, productos p, clientes cli, equipos e, medio_pago mp
                where v.id_canal = c.id
                and p.id = v.id_producto
                and cli.id = v.id_cliente 
                and v.id_equipo = e.id
                and mp.id = v.id_medio_pago
                and p.id = :idVenta
                union
                select 
                c.fecha,
                'c' tipo_movimiento,
                c.cantidad unidades,
                c.precio_costo precio
                from compras c, productos p, estado_pedido ep
                where c.id_producto = p.id
                and c.id_estado = ep.id
                and p.id = :idCompra
            order by 1,2;
        ";
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':idVenta' => $id, ':idCompra' => $id));
        if ($stm->rowCount() > 0) {
          return $stm->fetchAll(PDO::FETCH_ASSOC);
        } else {
          return null;
        }
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function getTotalSaleByDays($days){

    try{
        $days = max(0, (int) $days);
        $sql = "SELECT
                p.id,
                p.titulo,
                e.nombre editorial,
                p.stock,
                p.precio,
                p.precio_costo,
                sum(v.unidades) cantidad
                from ventas v, productos p, editoriales e
                where v.id_producto = p.id
                and v.fecha >= (CURRENT_DATE - INTERVAL ".$days." DAY)
                and p.id_editorial = e.id
                GROUP BY 1,2,3,4,5,6
                having sum(v.unidades) > 1
                ORDER BY 7 desc,1,2,3,4,5,6";
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetchAll(PDO::FETCH_ASSOC);
        } else {
          return null;
        }
    }
    catch (PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function getResumenValorInventarioCosto(){
    try
    {
        $sql = "select
                  count(*) cantidadProductos,
                  sum(p.stock) stock,
                  sum(p.stock * COALESCE(p.precio_costo, 0)) valorCosto
                  from productos p
                  where p.stock > 0";
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        return $stm->fetch(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function getValorInventarioCostoPorEditorial(){
    try
    {
        $sql = "select
                  e.nombre editorial,
                  sum(p.stock) stock,
                  sum(p.stock * COALESCE(p.precio_costo, 0)) valorCosto
                  from productos p, editoriales e
                  where p.stock > 0
                  and p.id_editorial = e.id
                  group by e.id
                  order by valorCosto desc";
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

}
?>
