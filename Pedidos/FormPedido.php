<?php
require_once '../dbFunctions/dbConnection.php';

//print_r($_POST);
//print_r($_GET);

$form = 'new';
$tableName = "Pedido";
$tablePK = "NumeroPedido";
if ($_GET){
    
    if (isset($_GET['edit'])){
        $form = 'edit';
        $result = executeSelectByID($_GET['edit'], $tablePK, $tableName)[0];
        $resultClienteFK = executeSelectByID($result['ID_Cliente'], 'ID', 'Cliente')[0];
        $result = array_merge($result, $resultClienteFK);
        $_GET = array_merge($_GET, $result);
    }

} elseif ($_POST) {
    $form = 'edit';

    //insert
    if (isset($_POST['savenew'])){

        $result = executeInsertPedido();
        $resultClienteFK = executeSelectByID($result['ID_Cliente'], 'ID', 'Cliente')[0];
        $result = array_merge($result, $resultClienteFK);
        $_GET = array_merge($_GET, $result);

        $form = 'edit';
        //se falhar
        if (isset($result['error']) == True){
            $form = 'new';
            //return the last inputed data to be autofilled
            $result = array_merge($result, $_POST);
        } else {
            $result['success'] = 'Registro inserido!';
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
            <div class="nav-item"><a href="/Pedidos">Voltar</a></div>
        </div>


        <div class="content pedido-content">
            <div><?php echo $form == 'new' ? 'Cadastrar um Pedido' : 'Alterar um Pedido' ?></div>

            <form class="pedido-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">

                <div class="input-wrapper">
                    <label for="ID">Número do Pedido: </label>
                    <input type="text" readonly value="<?php echo isset($_GET['NumeroPedido']) ? $_GET['NumeroPedido'] : '' ?>" id="NumeroPedido" name="NumeroPedido" autocomplete="off">
                </div>

                <div class="input-wrapper">
                    <label for="ID_Cliente">ID do Cliente: </label>
                    <input type="text" readonly value="<?php echo isset($_GET['ID_Cliente']) ? $_GET['ID_Cliente'] : '' ?>" id="ID_Cliente" name="ID_Cliente" autocomplete="off">
                </div>

                <div class="input-wrapper">
                    <label for="NomeCliente">Nome do Cliente: </label>
                    <input type="text" readonly value="<?php echo isset($_GET['NomeCliente']) ? $_GET['NomeCliente'] : '' ?>" id="NomeCliente" name="NomeCliente" autocomplete="off">
                </div>

                <div class="input-wrapper">
                    <label for="Email">E-mail: </label>
                    <input type="text" readonly value="<?php echo isset($_GET['Email']) ? $_GET['Email'] : '' ?>" id="Email" name="Email" autocomplete="off">
                </div>

                <div class="input-wrapper">
                    <label for="DtPedido">Data de Cadastro: </label>
                    <input type="text" readonly value="<?php echo isset($_GET['DtPedido']) ? $_GET['DtPedido'] : '' ?>" id="DtPedido" name="DtPedido" autocomplete="off">
                </div>

                <div class="input-wrapper">
                    <label for="CPF">CPF</label>
                    <input type="text" <?php echo $form == 'new' ? '' : 'readonly' ?> value="<?php echo isset($_GET['CPF']) ? $_GET['CPF'] : '' ?>" id="CPF" name="CPF" maxlength="11" autocomplete="off">
                </div>

                <?php if($form == 'new'){ ?>
                    <button title="Cadastrar" value="1" name="savenew" formmethod="post" formaction="/Pedidos/FormPedido.php">Salvar Novo Pedido</button>
                <?php } elseif($form != 'new') { ?>
                    <button title="Cadastrar" value="1" name="save" formmethod="get" formaction="/Pedidos/PedidoItens/index.php?NumeroPedido=<?php echo $_GET['NumeroPedido']?>">Listar Produtos deste Pedido</button>
                    <button title="Cadastrar Outro Pedido" value="0" name="new" formmethod="get" formaction="/Pedidos/FormPedido.php">Cadastrar Novo Pedido</button>
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