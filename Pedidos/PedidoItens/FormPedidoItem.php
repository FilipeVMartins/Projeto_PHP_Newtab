<?php
require_once '../../dbFunctions/dbConnection.php';

//print_r($_POST);
//print_r($_GET);
$tableName = 'PedidoItem';
$tablePK = 'ID';
$form = 'new';
if ($_GET){
    $result['ID_NumeroPedido'] = $_GET['ID_NumeroPedido'];
    
    if (isset($_GET['edit'])){
        $form = 'edit';
        //search PedidoItem row by its ID field in the get['edit']
        $result = executeSelectByID($_GET['edit'], $tablePK, $tableName)[0];
        $resultProdutoFK = executeSelectByID($result['ID_Produto'], $tablePK, 'Produto')[0];
        $result = array_merge($result, $resultProdutoFK);
    }

} elseif ($_POST) {
    $form = 'edit';

    //update
    if (isset($_POST['save'])){
        //after save, return $result if successful
        $result = executeUpdateByID($_POST[$tablePK], $tablePK, $tableName);
        $resultProdutoFK = executeSelectByID($result['ID_Produto'], $tablePK, 'Produto')[0];
        $result = array_merge($result, $resultProdutoFK);

        //if error
        if (isset($result['error']) == True){
            //return the last inputed data to be autofilled
            $result = array_merge($result, $_POST);
        } else {
            $result['success'] = 'Registro alterado!';
        }

    //insert
    } elseif (isset($_POST['savenew'])){

        //find Produto ID by codbarras
        $resultProdutoFK = executeSelectByID ($_POST['CodBarras'], 'CodBarras', 'Produto');
        //check if any produto has been found
        if ($resultProdutoFK){
            //if there is a produto with the given CodBarras, then
            $resultProdutoFK = $resultProdutoFK[0];

            //set the ID_Produto field
            $_POST['ID_Produto'] = $resultProdutoFK['ID'];

            //insert
            $result = executeInsert($tablePK, $tableName);
            $result = array_merge($result, $resultProdutoFK);

            $form = 'edit';

            //if error
            if (isset($result['error']) == True){
                $form = 'new';
                //return the last inputed data to be autofilled
                $result = array_merge($result, $_POST);
            } else {
                $result['success'] = 'Registro inserido!';
            }

        } else {
            //if produto ID doesn't exist, then error
            $form = 'new';
            //return the last inputed data to be autofilled
            $result = array_merge($result, $_POST);
            //show error
            $result['error'] = True;
            $result['errorInfo'][2] = 'Não foi encontrado um produto com este Código de Barras!';
        }

        
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
            <div class="nav-item"><a href="/Pedidos/PedidoItens/index.php?ID_NumeroPedido=<?php echo $result['ID_NumeroPedido']?>">Voltar</a></div>
        </div>


        <div class="content produto-content">
            <div><?php echo $form == 'new' ? 'Cadastrar um Produto no Pedido' : 'Alterar um Produto no Pedido' ?></div>

            <form class="produto-search-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                <div class="input-wrapper">
                    <label for="ID_NumeroPedido">Número do Pedido: </label>
                    <input type="text" readonly value="<?php echo isset($result['ID_NumeroPedido']) ? $result['ID_NumeroPedido'] : '' ?>" id="ID_NumeroPedido" name="ID_NumeroPedido" autocomplete="off">
                </div>

                <div class="input-wrapper">
                    <label for="ID">ID do Pedido Item: </label>
                    <input type="text" readonly value="<?php echo isset($result['ID']) ? $result['ID'] : '' ?>" id="ID" name="ID" autocomplete="off">
                </div>

                <div class="input-wrapper">
                    <label for="NomeProduto">Nome do Produto: </label>
                    <input type="text" readonly value="<?php echo isset($result['NomeProduto']) ? $result['NomeProduto'] : '' ?>" id="NomeProduto" name="NomeProduto" autocomplete="off">
                </div>

                <div class="input-wrapper">
                    <label for="CodBarras">Código de Barras: </label>
                    <input type="text" required <?php echo $form == 'new' ? '' : 'readonly' ?> value="<?php echo isset($result['CodBarras']) ? $result['CodBarras'] : '' ?>" id="CodBarras" name="CodBarras" maxlength="20" autocomplete="off">
                </div>

                <div class="input-wrapper">
                    <label for="Quantidade">Quantidade: </label>
                    <input type="text" required value="<?php echo isset($result['Quantidade']) ? $result['Quantidade'] : '' ?>" id="Quantidade" name="Quantidade" maxlength="20" autocomplete="off">
                </div>

                <div class="input-wrapper">
                    <label for="ValorUnitario">Valor Unitário: </label>
                    <input type="text" readonly value="<?php echo isset($result['ValorUnitario']) ? $result['ValorUnitario'] : '' ?>" id="ValorUnitario" name="ValorUnitario" autocomplete="off">
                </div>

                <button title="<?php echo $form == 'new' ? 'Cadastrar' : 'Alterar' ?>" value="1" name="<?php echo $form == 'new' ? 'savenew' : 'save' ?>" formmethod="post" formaction="/Pedidos/PedidoItens/FormPedidoItem.php"><?php echo $form == 'new' ? 'Salvar Novo Produto no Pedido' : 'Salvar Alterações' ?></button>

                <?php if($form == 'edit'){?>
                    <button title="Cadastrar Outro Produto" value="0" name="new" formmethod="get" formaction="/Pedidos/PedidoItens/FormPedidoItem.php">Cadastrar Novo Produto no Pedido</button>
                <?php } ?>
            </form>

            <div class="msgs">
                <div class="successful">
                    <?php echo isset($result['success']) ? $result['success'] : '' ?>
                </div>
                <div class="errors">
                    <?php echo isset($result['errorInfo']) ? $result['errorInfo'][2] : '' ?>
                </div>
            </div>
        </div>

        <script src="/js/scripts.js"></script>
        <!-- bootstrap 5-->
        <link href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>