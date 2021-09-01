<?php
require_once '../dbFunctions/dbConnection.php';

//default form selects
$qtd_paginacao = 20;
$offset_atual = 0;
$ordernar_campo = 'ID';
$ordernar_tipo = 'ASC';

$tableName = "Produto";
//existing table columns
$tableColumns = getTableColumns($tableName);

if ($_GET){
    //maintain previous request form data
    $qtd_paginacao = $_GET['qtd_paginacao'];
    $offset_atual = $_GET['offset_atual'];
    $ordernar_campo = $_GET['ordernar_campo'];
    $ordernar_tipo = $_GET['ordernar_tipo'];

    $result = executeSelectDbQueryUserInput($tableName);
    $searchResult = $result['result'];
    $total_registros = $result['rowCount'];
    $total_pages = ceil($total_registros/$qtd_paginacao);

} elseif ($_POST) {
    //delete by id
    if($_POST['delete']){
        $resultDelete = executeDeleteDbQueryUserInput($tableName, $_POST['delete']);
    }
}


?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Projeto PHP Newtab Academy</title>
        <meta name="description" content="Projeto PHP Newtab Academy">
        <link rel="stylesheet" href="/css/styles.css">
    </head>
    <body>
        <div class="nav-menu">
            <div class="nav-item"><a href="/ScriptDB_CreateTables_FakeData_DataMigration.php">Executar Scripts da Base de Dados</a></div>
            <div class="nav-item"><a href="/Clientes/index.php">Módulo Clientes</a></div>
            <div class="nav-item"><a href="/Produtos/index.php">Módulo Produtos</a></div>
            <div class="nav-item"><a href="">Módulo Pedidos</a></div>
            <div class="nav-item"><a href="/">Voltar</a></div>
        </div>




        <div class="content produto-content">
            <div>Pesquisar um Produto</div>
            <form class="produto-search-form" id="search-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="GET">
                <div class="input-wrapper">
                    <label for="ID">ID do Produto: </label>
                    <input type="text" value="<?php echo isset($_GET['ID']) ? $_GET['ID'] : '' ?>" id="ID" name="ID" autocomplete="off">
                </div>

                <div class="input-wrapper">
                    <label for="NomeProduto">Nome do Produto: </label>
                    <input type="text" value="<?php echo isset($_GET['NomeProduto']) ? $_GET['NomeProduto'] : '' ?>" id="NomeProduto" name="NomeProduto" autocomplete="off">
                </div>

                <div class="input-wrapper">
                    <label for="CodBarras">Código de Barras: </label>
                    <input type="text" value="<?php echo isset($_GET['CodBarras']) ? $_GET['CodBarras'] : '' ?>" id="CodBarras" name="CodBarras" maxlength="20" autocomplete="off">
                </div>

                <div class="input-wrapper">
                    <label for="ValorUnitario">Valor Unitário: </label>
                    <input type="text" value="<?php echo isset($_GET['ValorUnitario']) ? $_GET['ValorUnitario'] : '' ?>" id="ValorUnitario" name="ValorUnitario" autocomplete="off">
                </div>


                <div class="input-wrapper">
                    <label for="ordernar_campo">Ordernar por:</label>
                    <select name="ordernar_campo" id="ordernar_campo">
                        <?php
                        foreach($tableColumns as $columnName){
                            echo "<option value='$columnName'".($ordernar_campo==$columnName ? 'selected' : '').">$columnName</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="input-wrapper">
                    <label for="ordernar_tipo">Tipo:</label>
                    <select name="ordernar_tipo" id="ordernar_tipo">
                        <option value="ASC" <?php echo ("ASC"==$ordernar_tipo) ? 'selected' : ''?> >Crescente</option>
                        <option value="DESC" <?php echo ("DESC"==$ordernar_tipo) ? 'selected' : ''?> >Decrescente</option>
                        

                    </select>
                </div>

                <div class="input-wrapper">
                    <label for="qtd_paginacao">Produtos por Página:</label>
                    <select name="qtd_paginacao" id="qtd_paginacao">
                        <option value="10" <?php echo (10==$qtd_paginacao) ? 'selected' : ''?> >10</option>
                        <option value="20" <?php echo (20==$qtd_paginacao) ? 'selected' : ''?> >20</option>
                        <option value="30" <?php echo (30==$qtd_paginacao) ? 'selected' : ''?> >30</option>
                    </select>
                </div>

                <input type="hidden" name="offset_atual" value="0"></input>

                <button type="submit" title="Pesquisar Produto" >Pesquisar Produto</button>
                <button title="Cadastrar Novo Produto" value="0" name="new" formmethod="get" formaction="/Produtos/FormProduto.php">Cadastrar Novo Produto</button>
            </form>

            

            <div class="search-result">

                <?php if (isset($total_registros)){ ?>
                <div class="index-page-pagination" >
                    <div class="pagination-counting" >Exibindo <?php echo ( $offset_atual).'-'.($qtd_paginacao ? ($offset_atual + $qtd_paginacao < $total_registros ? ($offset_atual + $qtd_paginacao) : $total_registros ) : '0').' de '.$total_registros ?> Produtos, em <?php echo $total_pages?> Páginas.</div>

                    <div class="pagination-nav <?php echo ($total_registros == 0 ? 'hide' : '');?>" >
                        Ir à página: 
                        <button type="button" value="0" onclick="changeOffset(event)" class="<?php echo ($offset_atual == 0 ? 'first' : ''); ?>" ><< Primeiro</button>
                        <button type="button" value="<?php echo ($offset_atual-$qtd_paginacao); ?>" onclick="changeOffset(event)" class="<?php echo ($offset_atual == 0 ? 'first' : ''); ?>" >< Anterior</button>

                        <?php
                        for ($x = 0; $x < $total_pages; $x++) {
                            echo '<button type="button" value="'.($x*$qtd_paginacao)  .'" onclick="changeOffset(event)" class="'.($x*$qtd_paginacao == $offset_atual ? 'selected':'').'" >'.($x+1).'</button>';
                        }
                        ?>

                        <button type="button" value="<?php echo ($offset_atual+$qtd_paginacao); ?>" onclick="changeOffset(event)" class="<?php echo (ceil($offset_atual/$qtd_paginacao)+1 == $total_pages ? 'last' : ''); ?>" >Próximo ></button>
                        <button type="button" value="<?php echo (($total_pages-1)*$qtd_paginacao); ?>" onclick="changeOffset(event)" class="<?php echo (ceil($offset_atual/$qtd_paginacao)+1 == $total_pages ? 'last' : ''); ?>" >Último >></button> 
                    </div>
                </div>
                <?php } ?>
                    

                <?php
                if ($_GET){
                    if($searchResult){
                        ?>
                        <form class="search-result-form">
                        <table class="table">
                            <tr> <!-- table head -->
                                <?php
                                foreach ($searchResult[0] as $key => $value){
                                    if(!is_numeric($key)){
                                        echo "<th>$key</th>";
                                    }
                                }
                                ?>
                                <th>Ações</th>
                            </tr>
                            <?php
                            foreach ($searchResult as $keyRow => $row){
                                ?>
                                <tr> <!-- table body -->
                                    <?php
                                    foreach ($row as $keyField => $field){
                                        if(!is_numeric($keyField)){
                                            echo "<td>$field</td>";
                                        }
                                    }
                                    ?>
                                    <td><button title="Editar Produto" value="<?php echo $row['ID']?>" name="edit" formmethod="get" formaction="/Produtos/FormProduto.php">✏️</button></td>
                                    <td><button title="Excluir Produto" type="submit" alt="Excluir Produto" value="<?php echo $row['ID']?>" name="delete" formmethod="post" formaction="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" >❌</button></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                        </form>
                        <?php
                    } else {
                        echo '<p>Nenhum resultado encontrado.</p>';
                    }
                } else {
                    if (isset($resultDelete)){
                        echo "<p>$resultDelete</p>";
                    }
                }
                ?>
            </div>
        </div>
        <script src="/js/scripts.js"></script>
        <!-- bootstrap 5-->
        <link href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>